<?php

class Reclamation extends Model
{
    protected string $table = 'Reclamation';
    protected string $primaryKey = 'id_reclamation';

    public function getWithRelations(): array
    {
        $sql = "SELECT r.*, u.email AS auteur_email, h.titre AS histoire_titre, c.contenu AS commentaire_contenu
                FROM Reclamation r
                JOIN Utilisateur u ON u.id_utilisateur = r.id_auteur
                LEFT JOIN Histoire h ON h.id_histoire = r.id_histoire_cible
                LEFT JOIN Commentaire c ON c.id_commentaire = r.id_commentaire_cible
                ORDER BY r.id_reclamation DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getWithFilters(?string $statut, int $page, int $perPage, ?string $search = null, string $sort = 'recent'): array
    {
        $where = [];
        $params = [];
        if ($statut) {
            $where[] = 'r.statut = :statut';
            $params['statut'] = $statut;
        }
        if ($search) {
            $where[] = '(r.description_personnalisee LIKE :q OR h.titre LIKE :q)';
            $params['q'] = '%' . $search . '%';
        }
        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $countSql = "SELECT COUNT(*) FROM Reclamation r {$whereSql}";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $orderBy = 'r.created_at DESC';
        if ($sort === 'oldest') {
            $orderBy = 'r.created_at ASC';
        } elseif ($sort === 'score') {
            $orderBy = 'r.ai_score DESC NULLS LAST, r.created_at DESC';
        }

        $sql = "SELECT r.*, u.email AS auteur_email, h.titre AS histoire_titre, c.contenu AS commentaire_contenu
                FROM Reclamation r
                JOIN Utilisateur u ON u.id_utilisateur = r.id_auteur
                LEFT JOIN Histoire h ON h.id_histoire = r.id_histoire_cible
                LEFT JOIN Commentaire c ON c.id_commentaire = r.id_commentaire_cible
                {$whereSql}
                ORDER BY {$orderBy}
                LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return [$stmt->fetchAll(), $total];
    }

    public function getAllForExport(): array
    {
        $sql = "SELECT r.*, u.email AS auteur_email, h.titre AS histoire_titre, c.contenu AS commentaire_contenu
                FROM Reclamation r
                JOIN Utilisateur u ON u.id_utilisateur = r.id_auteur
                LEFT JOIN Histoire h ON h.id_histoire = r.id_histoire_cible
                LEFT JOIN Commentaire c ON c.id_commentaire = r.id_commentaire_cible
                ORDER BY r.id_reclamation DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getByUser(int $userId): array
    {
        $sql = "SELECT r.*, h.titre AS histoire_titre, c.contenu AS commentaire_contenu
                FROM Reclamation r
                LEFT JOIN Histoire h ON h.id_histoire = r.id_histoire_cible
                LEFT JOIN Commentaire c ON c.id_commentaire = r.id_commentaire_cible
                WHERE r.id_auteur = :uid
                ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function getByStoryAuthor(int $userId): array
    {
        $sql = "SELECT r.*, h.titre AS histoire_titre, u.email AS auteur_email
                FROM Reclamation r
                JOIN Histoire h ON h.id_histoire = r.id_histoire_cible
                JOIN Utilisateur u ON u.id_utilisateur = r.id_auteur
                WHERE h.id_client = :uid
                ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function getStats(): array
    {
        $stats = [];

        $stats['by_status'] = $this->db->query("SELECT statut, COUNT(*) AS cnt FROM Reclamation GROUP BY statut")
            ->fetchAll();

        $stats['by_category'] = $this->db->query(
            "SELECT cs.libelle, COUNT(*) AS cnt
             FROM Reclamation_Cause rc
             JOIN Cause_Signalement cs ON cs.id_cause = rc.id_cause
             GROUP BY cs.libelle"
        )->fetchAll();

        $stats['by_day'] = $this->db->query(
            "SELECT DATE(created_at) AS day, COUNT(*) AS cnt
             FROM Reclamation
             GROUP BY DATE(created_at)
             ORDER BY day DESC
             LIMIT 14"
        )->fetchAll();

        return $stats;
    }

    public function existsForUserTarget(int $userId, ?int $histoireId, ?int $commentId): bool
    {
        $sql = "SELECT 1 FROM Reclamation
                WHERE id_auteur = :uid
                  AND ( (id_histoire_cible IS NOT NULL AND id_histoire_cible = :hid)
                        OR (id_commentaire_cible IS NOT NULL AND id_commentaire_cible = :cid) )
                  AND statut IN ('nouvelle','en_cours','acceptee','resolue')
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':uid' => $userId,
            ':hid' => $histoireId,
            ':cid' => $commentId,
        ]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Retourne la dernière réclamation et le volume pour une histoire donnée.
     */
    public function getRecapForStory(int $histoireId): ?array
    {
        $sql = "SELECT r.*, u.email AS auteur_email
                FROM Reclamation r
                JOIN Utilisateur u ON u.id_utilisateur = r.id_auteur
                WHERE r.id_histoire_cible = :hid
                ORDER BY r.id_reclamation DESC
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['hid' => $histoireId]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM Reclamation WHERE id_histoire_cible = :hid");
        $countStmt->execute(['hid' => $histoireId]);
        $row['count_total'] = (int)$countStmt->fetchColumn();
        return $row;
    }

    /**
     * Simple heuristic scorer (fallback when Gemini/AI off).
     */
    public function scoreHeuristic(string $description, array $causeIds): array
    {
        $configPath = __DIR__ . '/../../config/badwords.php';
        $badwords = [];
        if (file_exists($configPath)) {
            $cfg = require $configPath;
            if (!empty($cfg['banned_words']) && is_array($cfg['banned_words'])) {
                $badwords = $cfg['banned_words'];
            }
        }

        $lower = mb_strtolower($description);
        $hits = 0;
        foreach ($badwords as $word) {
            if ($word !== '' && mb_stripos($lower, mb_strtolower($word)) !== false) {
                $hits++;
            }
        }

        $score = 20 + ($hits * 15) + (count($causeIds) * 5);
        $score = max(0, min(100, $score));

        $parts = [];
        if ($hits > 0) {
            $parts[] = $hits . ' terme(s) sensible(s)';
        }
        if (count($causeIds) > 0) {
            $parts[] = 'Causes: ' . count($causeIds);
        }
        $excerpt = mb_substr($description, 0, 120) . (mb_strlen($description) > 120 ? '...' : '');
        $parts[] = 'Extrait: "' . $excerpt . '"';

        return [(float)$score, implode(' | ', $parts), 'heuristic-v1'];
    }

    /**
     * Score using provider (gemini|heuristic|off). Gemini gracefully degrades to heuristic if clé manquante/erreur.
     */
    public function scoreWithProvider(string $description, array $causeIds, string $provider, ?string $apiKey, string $model): array
    {
        if ($provider === 'off') {
            return [null, null, 'off'];
        }

        if ($provider === 'gemini') {
            if (!$apiKey) {
                return $this->scoreHeuristic($description, $causeIds);
            }
            try {
                $response = $this->callGemini($description, $causeIds, $apiKey, $model);
                if ($response) {
                    return $response;
                }
            } catch (Exception $e) {
                error_log('Gemini error: ' . $e->getMessage());
            }
            // fallback
            return $this->scoreHeuristic($description, $causeIds);
        }

        return $this->scoreHeuristic($description, $causeIds);
    }

    private function callGemini(string $description, array $causeIds, string $apiKey, string $model): ?array
    {
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . urlencode($apiKey);
        $prompt = "Tu es un modérateur. Donne un score de gravité (0-100) et un résumé court pour cette réclamation. Réclamation: " . $description . " | Causes: " . implode(',', $causeIds);
        $payload = [
            'contents' => [[
                'parts' => [['text' => $prompt]]
            ]],
            'safetySettings' => [
                ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_NONE']
            ],
            'generationConfig' => [
                'temperature' => 0.2,
                'maxOutputTokens' => 120
            ]
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $raw = curl_exec($ch);
        if ($raw === false) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }
        $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        if ($code >= 400) {
            throw new Exception('HTTP ' . $code . ' body=' . $raw);
        }
        $json = json_decode($raw, true);
        $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
        if (!$text) {
            return null;
        }
        // Try to extract score: look for first number 0-100
        preg_match('/(\d{1,3})/', $text, $m);
        $score = isset($m[1]) ? max(0, min(100, (int)$m[1])) : 50;
        $analysis = trim($text);
        return [(float)$score, $analysis, $model];
    }
}

