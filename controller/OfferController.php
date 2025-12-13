<?php
// controller/OfferController.php

require_once 'model/Offer.php';
require_once 'model/Application.php';
require_once 'model/EmailService.php';

// Démarrer la session pour gérer les utilisateurs connectés
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class OfferController {
    private $offerModel;
    private $applicationModel;
    
    // 🔑 CLÉ API HUGGING FACE (Obligatoire pour la détection IA)
    private $huggingFaceToken = "hf_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX"; 
    
    public function __construct() {
        $this->offerModel = new Offer();
        $this->applicationModel = new Application();
    }
    
    // =========================================================
    // 🏢 GESTION DES OFFRES (Côté Organisateur)
    // =========================================================

    public function listOffers() {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            header("Location: NovaLinkPeace/test/View/FrontOffice/login.php");
            exit();
        }
        
        $user_role = $_SESSION['role'] ?? 'client';
        $user_id = $_SESSION['user_id'];
        
        // Si organisation, on peut filtrer ses propres offres ou voir toutes
        if ($user_role === 'organisation') {
            $show_all = isset($_GET['view']) && $_GET['view'] === 'all';
            $offers = $this->offerModel->getAll($show_all ? null : $user_id)->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // Les clients voient toutes les offres
            $offers = $this->offerModel->getAll()->fetchAll(PDO::FETCH_ASSOC);
        }
        
        require 'view/offers_list.php';
    }

    public function createOffer() { 
        // Seules les organisations peuvent créer des offres
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organisation') {
            die("Accès interdit : Vous devez être une organisation pour créer une offre.");
        }
        require 'view/offer_form.php'; 
    }

    public function storeOffer() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organisation') {
                die("Accès interdit");
            }
            
            $this->offerModel->org_id = $_SESSION['user_id'];
            $this->offerModel->title = trim($_POST['title']);
            $this->offerModel->description = trim($_POST['description']);
            $this->offerModel->max_candidates = intval($_POST['max_candidates']);
            $this->offerModel->keywords = trim($_POST['keywords']);
            
            if ($this->offerModel->create()) {
                header("Location: index.php?action=list&status=created");
                exit();
            }
        }
    }

    public function editOffer() {
        $id = $_GET['id'] ?? die('ID manquant.');
        
        if ($this->offerModel->getById($id)) {
            // Vérifier que l'organisation modifie bien sa propre offre
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organisation') {
                die("Accès interdit");
            }
            
            $offer = [
                'id' => $this->offerModel->id,
                'title' => $this->offerModel->title,
                'description' => $this->offerModel->description,
                'max_candidates' => $this->offerModel->max_candidates,
                'keywords' => $this->offerModel->keywords
            ];
            require 'view/offer_form.php';
        }
    }

    public function updateOffer() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organisation') {
                die("Accès interdit");
            }
            
            $this->offerModel->id = $_GET['id'];
            $this->offerModel->title = trim($_POST['title']);
            $this->offerModel->description = trim($_POST['description']);
            $this->offerModel->max_candidates = intval($_POST['max_candidates']);
            $this->offerModel->keywords = trim($_POST['keywords']);
            
            if ($this->offerModel->update()) {
                header("Location: index.php?action=list&status=updated");
                exit();
            }
        }
    }

    public function deleteOffer() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organisation') {
            die("Accès interdit");
        }
        
        $this->offerModel->id = $_GET['id'];
        if ($this->offerModel->delete()) {
            header("Location: index.php?action=list&status=deleted");
            exit();
        }
    }

    // =========================================================
    // 👤 TRAITEMENT CANDIDATURE (Logiciel Anti-Triche & IA)
    // =========================================================

    public function showApplicationForm() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
            die("Accès interdit : Seuls les clients peuvent postuler.");
        }
        
        $id = $_GET['id'];
        if ($this->offerModel->isFull($id)) {
            die("<div style='text-align:center;margin-top:50px;'><h1 style='color:#E74C3C'>Offre Complète</h1><p>Quota atteint.</p><a href='index.php'>Retour</a></div>");
        }
        if ($this->offerModel->getById($id)) {
            $offer = ['id' => $this->offerModel->id, 'title' => $this->offerModel->title, 'keywords' => $this->offerModel->keywords];
            require 'view/application_form.php';
        }
    }

    public function submitApplication() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
                die("Accès interdit");
            }
            
            $offer_id = $_POST['offer_id'];
            
            // 0. Vérification Quota
            if ($this->offerModel->isFull($offer_id)) die("Erreur : Offre complète.");
            
            $motivation = trim($_POST['motivation']);

            // 🛡️ 1. FILTRE LONGUEUR (Anti-Lazy)
            // Si moins de 100 caractères, on considère que c'est du spam ou un prompt IA trop court
            if (strlen($motivation) < 100) {
                $this->saveApplication($offer_id, $motivation, 'refusée', 0, 'Spam (Trop court)');
                header("Location: index.php?status=applied_refused");
                exit();
            }
            
            // 🛡️ 2. DÉTECTION IA API (Hugging Face)
            // Appel de la méthode stricte
            $is_fake = $this->detectAiContent($motivation);
            
            if ($is_fake) {
                // Refus immédiat avec motif spécial
                $this->saveApplication($offer_id, $motivation, 'refusée', 0, 'Artificiel');
                header("Location: index.php?status=detected_ai");
                exit();
            }

            // 🛡️ 3. FILTRAGE ATS (Mots-clés)
            $this->offerModel->getById($offer_id);
            $required_keywords = $this->offerModel->keywords;
            $status = 'en attente';
            
            if (!empty($required_keywords)) {
                $keywords_array = array_map('trim', explode(',', $required_keywords));
                
                // Vérification de chaque mot clé
                foreach ($keywords_array as $word) {
                    if (!empty($word) && stripos($motivation, $word) === false) {
                        $status = 'refusée'; break;
                    }
                }

                // 🛡️ 4. ANTI-BOURRAGE (Keyword Stuffing)
                // Si le texte contient trop de mots clés par rapport à sa longueur totale
                $total_len = strlen($motivation);
                $kw_len = 0;
                foreach ($keywords_array as $word) $kw_len += substr_count(strtolower($motivation), strtolower($word)) * strlen($word);
                
                // Si > 30% du texte sont juste des mots clés -> Fake
                if (($kw_len / $total_len) > 0.3) {
                    $status = 'refusée';
                }
            }

            // 4. IA Interne (Score & Sentiment)
            $score = $this->calculateAiScore($motivation, $required_keywords);
            $sentiment = $this->analyzeAiSentiment($motivation);

            // Enregistrement final
            $this->saveApplication($offer_id, $motivation, $status, $score, $sentiment);
            
            // Redirection selon le résultat
            $msg = ($status === 'refusée') ? 'applied_refused' : 'applied';
            header("Location: index.php?status=" . $msg);
            exit();
        }
    }

    // Helper pour éviter de répéter le code d'enregistrement
    private function saveApplication($offerId, $motivation, $status, $score, $sentiment) {
        $this->applicationModel->offer_id = $offerId;
        $this->applicationModel->candidate_id = $_SESSION['user_id'];
        $this->applicationModel->candidate_name = trim($_POST['candidate_name']);
        $this->applicationModel->candidate_email = trim($_POST['candidate_email']);
        $this->applicationModel->motivation = $motivation;
        $this->applicationModel->status = $status;
        $this->applicationModel->score = $score;
        $this->applicationModel->sentiment = $sentiment;
        $this->applicationModel->create();
    }

    // =========================================================
    // 🛡️ DASHBOARD ORGANISATEUR
    // =========================================================

    public function listApplications() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organisation') {
            die("Accès interdit : Seules les organisations peuvent voir les candidatures.");
        }
        
        $offer_id = isset($_GET['offer_id']) ? $_GET['offer_id'] : null;
        $applications = $this->applicationModel->getAllWithOfferDetails($offer_id)->fetchAll(PDO::FETCH_ASSOC);
        
        // Si filtré par offre, vérifier que l'offre appartient à l'organisation
        if ($offer_id) {
            $this->offerModel->getById($offer_id);
            $filter_title = $this->offerModel->title;
        } else {
            $filter_title = null;
        }
        
        require 'view/admin_applications_list.php';
    }

    public function updateApplicationStatus() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organisation') {
            die("Accès interdit");
        }
        
        $id = $_GET['id'] ?? null;
        $status = $_GET['status'] ?? null;
        
        if ($id && in_array($status, ['acceptée', 'refusée'])) {
            // Récupérer les infos de la candidature et de l'offre
            $appInfo = $this->applicationModel->getById($id);
            
            if ($appInfo) {
                // Récupérer le nom de l'organisation
                $this->offerModel->getById($appInfo['offer_id']);
                
                // Utiliser le nom de l'organisation depuis la session ou la base
                $orgName = $_SESSION['organisation_name'] ?? 'Notre Organisation';
                
                // Envoi Email selon le statut
                if ($status === 'acceptée') {
                    EmailService::sendAcceptanceEmail(
                        $appInfo['candidate_email'], 
                        $appInfo['candidate_name'], 
                        $appInfo['offer_title'],
                        $orgName
                    );
                } elseif ($status === 'refusée') {
                    EmailService::sendRejectionEmail(
                        $appInfo['candidate_email'], 
                        $appInfo['candidate_name'], 
                        $appInfo['offer_title'],
                        $orgName
                    );
                }
            }
            
            $this->applicationModel->updateStatus($id, $status);
            header("Location: index.php?action=list_applications&status=app_updated");
            exit();
        }
    }

    // =========================================================
    // 🧠 OUTILS INTELLIGENTS (IA & EMAIL PRO)
    // =========================================================

    // 1. DÉTECTION IA API (Mode Strict)
    private function detectAiContent($text) {
        $api_url = "https://api-inference.huggingface.co/models/openai-community/roberta-base-openai-detector";
        $data = json_encode(["inputs" => substr($text, 0, 500)]); 
        
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $this->huggingFaceToken,
            "Content-Type: application/json"
        ]);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode === 200) {
            $result = json_decode($response, true);
            if (isset($result[0]) && is_array($result[0])) {
                foreach ($result[0] as $prediction) {
                    // SEUIL STRICT : 0.40 (40%)
                    if ($prediction['label'] === 'Fake' && $prediction['score'] > 0.40) {
                        return true; 
                    }
                }
            }
        }
        return false;
    }

    // 2. GÉNÉRATEUR DESCRIPTION (AJAX)
    public function generateAiDescription() {
        header('Content-Type: application/json');
        $title = $_GET['title'] ?? ''; $keywords = $_GET['keywords'] ?? 'compétences';
        if (empty($title)) { echo json_encode(['success' => false, 'message' => 'Titre manquant']); exit; }
        
        $title = trim(htmlspecialchars($title));
        $t_lower = mb_strtolower($title);
        $context = 'generic';

        if (preg_match('/(dev|web|data|tech|ing)/', $t_lower)) $context = 'tech';
        elseif (preg_match('/(comm|vente|manag)/', $t_lower)) $context = 'biz';

        $body = "";
        switch($context) {
            case 'tech': $body = "Nous cherchons un(e) **$title** passionné(e).\n\nMissions:\n- Développement de solutions.\n- Veille technologique.\n\nStack:\n- **$keywords**."; break;
            case 'biz': $body = "Poste de **$title** à pourvoir.\n\nMissions:\n- Gestion portefeuille client.\n- Négociation.\n\nProfil:\n- **$keywords**."; break;
            default: $body = "Rejoignez-nous comme **$title**.\n\nVos missions seront stimulantes.\n\nCompétences : **$keywords**."; break;
        }
        echo json_encode(['success' => true, 'text' => $body]); exit;
    }

    // 3. SCORE INTERNE
    private function calculateAiScore($text, $keys) {
        if (empty($keys)) return 100;
        $arr = array_map('trim', explode(',', $keys));
        $found = 0;
        foreach ($arr as $k) { if (stripos($text, $k) !== false) $found++; }
        return min(100, round(($found / count($arr)) * 80 + (strlen($text) > 100 ? 20 : 0)));
    }

    // 4. SENTIMENT INTERNE
    private function analyzeAiSentiment($text) {
        $pos = ['expert', 'maîtrise', 'passion', 'fort', 'aime'];
        $score = 0;
        foreach ($pos as $w) $score += substr_count(mb_strtolower($text), $w);
        return ($score >= 1) ? 'Confiant' : 'Neutre';
    }
}
?>