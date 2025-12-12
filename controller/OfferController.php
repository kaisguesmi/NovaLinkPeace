<?php
// controller/OfferController.php

require_once 'model/Offer.php';
require_once 'model/Application.php';

class OfferController {
    private $offerModel;
    private $applicationModel;
    
    public function __construct() {
        $this->offerModel = new Offer();
        $this->applicationModel = new Application();
    }
    
    // =========================================================
    // 🏢 GESTION DES OFFRES (Côté Organisateur)
    // =========================================================

    public function listOffers() {
        // Vérification du rôle : Organisateur ou Client
        $user_role = isset($_GET['role']) && $_GET['role'] === 'organisateur' ? 'organisateur' : 'client';
        $offers = $this->offerModel->getAll()->fetchAll(PDO::FETCH_ASSOC);
        require 'view/offers_list.php';
    }

    public function createOffer() { require 'view/offer_form.php'; }

    public function storeOffer() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $max = intval($_POST['max_candidates']);
            $keywords = trim($_POST['keywords']);
            
            $errors = [];
            if (empty($title) || strlen($title) < 5) $errors[] = "Le titre est trop court.";
            if ($max < 1) $errors[] = "Il faut au moins 1 place disponible.";

            if (empty($errors)) {
                $this->offerModel->title = $title;
                $this->offerModel->description = $description;
                $this->offerModel->max_candidates = $max;
                $this->offerModel->keywords = $keywords;
                
                if ($this->offerModel->create()) {
                    header("Location: index.php?role=organisateur&status=created");
                    exit();
                }
            }
        }
    }

    public function editOffer() {
        $id = $_GET['id'] ?? die('ID manquant.');
        if ($this->offerModel->getById($id)) {
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
            $this->offerModel->id = $_GET['id'];
            $this->offerModel->title = trim($_POST['title']);
            $this->offerModel->description = trim($_POST['description']);
            $this->offerModel->max_candidates = intval($_POST['max_candidates']);
            $this->offerModel->keywords = trim($_POST['keywords']);
            
            if ($this->offerModel->update()) {
                header("Location: index.php?role=organisateur&status=updated");
                exit();
            }
        }
    }

    public function deleteOffer() {
        $this->offerModel->id = $_GET['id'];
        if ($this->offerModel->delete()) {
            header("Location: index.php?role=organisateur&status=deleted");
            exit();
        }
    }

    // =========================================================
    // 👤 GESTION DES CANDIDATURES (Côté Client & Traitement)
    // =========================================================

    public function showApplicationForm() {
        $id = $_GET['id'];
        
        // Vérification Quota (sauf refusés)
        if ($this->offerModel->isFull($id)) {
            die("<div style='font-family:sans-serif; text-align:center; margin-top:50px; color:#333;'>
                    <h1 style='color:#E74C3C;'>Offre Complète</h1>
                    <p>Désolé, le nombre maximum de candidatures pour cette offre a été atteint.</p>
                    <a href='index.php' style='text-decoration:none; color:#5DADE2;'>&larr; Retour aux offres</a>
                 </div>");
        }

        if ($this->offerModel->getById($id)) {
            $offer = [
                'id' => $this->offerModel->id, 
                'title' => $this->offerModel->title, 
                'keywords' => $this->offerModel->keywords
            ];
            require 'view/application_form.php';
        }
    }

    // --- CŒUR DU SYSTÈME INTELLIGENT ---
    public function submitApplication() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $offer_id = $_POST['offer_id'];
            
            // 1. Sécurité : Vérification Quota de dernière minute
            if ($this->offerModel->isFull($offer_id)) {
                die("Erreur : L'offre vient d'atteindre son quota maximum.");
            }
            
            $motivation = trim($_POST['motivation']);
            
            // Récupération des infos de l'offre pour l'ATS
            $this->offerModel->getById($offer_id);
            $required_keywords = $this->offerModel->keywords;
            
            // 2. FILTRAGE ATS (Mots-clés obligatoires)
            $status = 'en attente';
            if (!empty($required_keywords)) {
                $keywords_array = array_map('trim', explode(',', $required_keywords));
                foreach ($keywords_array as $word) {
                    if (!empty($word) && stripos($motivation, $word) === false) {
                        $status = 'refusée'; // Sanction immédiate
                        break;
                    }
                }
            }

            // 3. IA : Calcul du Score de Compatibilité
            $score = $this->calculateAiScore($motivation, $required_keywords);

            // 4. IA : Analyse de Sentiment
            $sentiment = $this->analyzeAiSentiment($motivation);

            // Préparation de l'objet
            $this->applicationModel->offer_id = $offer_id;
            $this->applicationModel->candidate_name = trim($_POST['candidate_name']);
            $this->applicationModel->candidate_email = trim($_POST['candidate_email']);
            $this->applicationModel->motivation = $motivation;
            $this->applicationModel->status = $status;
            
            // Données IA
            $this->applicationModel->score = $score;
            $this->applicationModel->sentiment = $sentiment;
            
            // Enregistrement
            if ($this->applicationModel->create()) {
                // Message adapté si refusé automatiquement
                $msg = ($status === 'refusée') ? 'applied_refused' : 'applied';
                header("Location: index.php?status=" . $msg);
                exit();
            }
        }
    }

    // =========================================================
    // 🛡️ GESTION ORGANISATEUR (Dashboard)
    // =========================================================

    public function listApplications() {
        // Sécurité Rôle
        if (!isset($_GET['role']) || $_GET['role'] !== 'organisateur') die("Accès interdit : Réservé aux organisateurs.");
        
        $offer_id = isset($_GET['offer_id']) ? $_GET['offer_id'] : null;
        
        // Récupère les candidatures (filtrées ou non)
        $applications = $this->applicationModel->getAllWithOfferDetails($offer_id)->fetchAll(PDO::FETCH_ASSOC);
        
        // Titre dynamique pour le filtre
        $filter_title = null;
        if ($offer_id) {
            $this->offerModel->getById($offer_id);
            $filter_title = $this->offerModel->title;
        }

        require 'view/admin_applications_list.php';
    }

    // Action : Accepter/Refuser + Envoi Email
    public function updateApplicationStatus() {
        if (!isset($_GET['role']) || $_GET['role'] !== 'organisateur') die("Accès interdit.");
        
        $id = $_GET['id'] ?? null;
        $status = $_GET['status'] ?? null;
        
        if ($id && in_array($status, ['acceptée', 'refusée'])) {
            
            // Si on accepte, on déclenche l'envoi d'email
            if ($status === 'acceptée') {
                $appInfo = $this->applicationModel->getById($id);
                if ($appInfo) {
                    $this->sendAcceptanceEmail(
                        $appInfo['candidate_email'], 
                        $appInfo['candidate_name'], 
                        $appInfo['offer_title']
                    );
                }
            }

            $this->applicationModel->updateStatus($id, $status);
            header("Location: index.php?action=list_applications&role=organisateur&status=app_updated");
            exit();
        }
    }

    // =========================================================
    // 🧠 MOTEURS IA & OUTILS (Privé & Ajax)
    // =========================================================

    // 1. GÉNÉRATEUR DE DESCRIPTION (Route AJAX)
    public function generateAiDescription() {
        header('Content-Type: application/json');
        
        $title = $_GET['title'] ?? '';
        $keywords = $_GET['keywords'] ?? 'compétences variées';
        
        if (empty($title)) {
            echo json_encode(['success' => false, 'message' => 'Titre manquant']);
            exit;
        }

        $title = trim(htmlspecialchars($title));
        
        // Détection de contexte simple
        $context = 'generic';
        $t_lower = mb_strtolower($title);
        if (preg_match('/(dev|web|data|tech|ingénieur)/', $t_lower)) $context = 'tech';
        elseif (preg_match('/(commer|vente|manager|chef)/', $t_lower)) $context = 'biz';
        elseif (preg_match('/(design|graph|ux|ui)/', $t_lower)) $context = 'design';

        // Génération du texte
        $body = "";
        switch ($context) {
            case 'tech':
                $body = "Passionné(e) de tech ? Rejoignez-nous en tant que **$title**.\n\nVos missions :\n- Concevoir des solutions performantes.\n- Assurer la qualité du code.\n\nProfil :\n- Maîtrise de : **$keywords**.\n- Rigueur et esprit d'équipe.";
                break;
            case 'biz':
                $body = "Nous recrutons un(e) **$title** pour booster notre croissance.\n\nMissions :\n- Développer le portefeuille clients.\n- Négocier et convaincre.\n\nProfil :\n- Expert en : **$keywords**.\n- Excellent relationnel.";
                break;
            default:
                $body = "Nous recherchons un(e) **$title** motivé(e).\n\nMissions :\n- Gérer les projets quotidiens.\n- Collaborer avec les équipes.\n\nProfil :\n- Compétences : **$keywords**.\n- Autonomie et proactivité.";
                break;
        }
        
        $text = "Opportunité : $title\n\n$body\n\nRejoignez une équipe dynamique !";
        echo json_encode(['success' => true, 'text' => $text]);
        exit;
    }

    // 2. CALCUL SCORE IA (0-100)
    private function calculateAiScore($text, $keys) {
        if (empty($keys)) return 100;
        $arr = array_map('trim', explode(',', $keys));
        $found = 0;
        foreach ($arr as $k) { 
            if (stripos($text, $k) !== false) $found++; 
        }
        $score = ($found / count($arr)) * 80;
        if (strlen($text) > 100) $score += 10;
        if (strlen($text) > 300) $score += 10;
        return min(100, round($score));
    }

    // 3. ANALYSE SENTIMENT IA
    private function analyzeAiSentiment($text) {
        $pos = ['expert', 'maîtrise', 'passion', 'fort', 'aime', 'succès', 'autonome', 'sérieux', 'motivé'];
        $neg = ['peut-être', 'essayer', 'peur', 'doute', 'sais pas', 'moyen', 'bof', 'incertain'];
        
        $score = 0;
        $tl = mb_strtolower($text);
        
        foreach ($pos as $w) $score += substr_count($tl, $w);
        foreach ($neg as $w) $score -= substr_count($tl, $w);
        
        if ($score >= 1) return 'Confiant';
        if ($score <= -1) return 'Hésitant';
        return 'Neutre';
    }

    // 4. ENVOI EMAIL PROFESSIONNEL (DESIGN HTML)
    private function sendAcceptanceEmail($toEmail, $candidateName, $offerTitle) {
        
        $subject = "Félicitations ! Votre candidature a été retenue 🚀";
        
        // --- TEMPLATE EMAIL PRO (CSS INLINE) ---
        $message = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Candidature Acceptée</title>
        </head>
        <body style="margin: 0; padding: 0; background-color: #f4f6f9; font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;">
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="center" style="padding: 40px 0;">
                        <!-- Conteneur Principal -->
                        <table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                            
                            <!-- En-tête (Violet Organisateur) -->
                            <tr>
                                <td align="center" style="background-color: #8E44AD; padding: 30px;">
                                    <h1 style="color: #ffffff; margin: 0; font-size: 24px; letter-spacing: 1px; text-transform: uppercase;">Candidature Acceptée</h1>
                                </td>
                            </tr>
                            
                            <!-- Contenu -->
                            <tr>
                                <td style="padding: 40px 30px; color: #333333; line-height: 1.6;">
                                    <p style="font-size: 16px; margin-bottom: 20px;">Bonjour <strong>' . htmlspecialchars($candidateName) . '</strong>,</p>
                                    
                                    <p style="margin-bottom: 15px;">Nous avons le plaisir de vous annoncer que votre profil a retenu toute notre attention pour la mission :</p>
                                    
                                    <!-- Titre Offre mis en avant -->
                                    <div style="background-color: #f8f9fa; border-left: 4px solid #8E44AD; padding: 15px; margin: 20px 0;">
                                        <p style="margin: 0; font-size: 18px; font-weight: bold; color: #2c3e50;">' . htmlspecialchars($offerTitle) . '</p>
                                    </div>
                                    
                                    <p style="margin-bottom: 30px;">L\'organisateur a validé votre candidature. Vous pouvez dès à présent vous préparer pour les prochaines étapes.</p>
                                    
                                    <!-- Bouton d\'action -->
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td align="center">
                                                <a href="http://localhost/Module4_Gestion_Offres" style="display: inline-block; background-color: #7BD389; color: #ffffff; text-decoration: none; padding: 14px 30px; border-radius: 50px; font-weight: bold; font-size: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                                                    Accéder à mon espace
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            
                            <!-- Pied de page -->
                            <tr>
                                <td style="background-color: #e9ecef; padding: 20px; text-align: center; font-size: 12px; color: #888;">
                                    <p style="margin: 0;">&copy; ' . date('Y') . ' Gestion des Offres. Tous droits réservés.</p>
                                    <p style="margin: 5px 0 0 0;">Ceci est un message automatique, merci de ne pas répondre.</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ';

        // En-têtes techniques
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: Recrutement <no-reply@mon-site-offres.com>" . "\r\n";
        $headers .= "Reply-To: contact@mon-site-offres.com" . "\r\n";

        // ENVOI (Avec gestion d'erreur localhost)
        // Le @ masque les erreurs PHP si le SMTP n'est pas configuré
        if (@mail($toEmail, $subject, $message, $headers)) {
            return true;
        } else {
            // Simulation pour localhost (Si mail() échoue)
            if (!is_dir('emails_simules')) { mkdir('emails_simules'); }
            $filename = 'emails_simules/mail_' . time() . '_' . preg_replace('/[^a-zA-Z0-9]/', '', $candidateName) . '.html';
            file_put_contents($filename, $message);
            return false;
        }
    }
}
?>