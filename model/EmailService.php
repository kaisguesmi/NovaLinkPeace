<?php
// model/EmailService.php
class EmailService {
    
    /**
     * Envoie un email de notification d'acceptation de candidature
     * @param string $to - Email du candidat
     * @param string $candidateName - Nom du candidat
     * @param string $offerTitle - Titre de l'offre
     * @param string $organisationName - Nom de l'organisation
     * @return bool - True si l'email est envoyé, false sinon
     */
    public static function sendAcceptanceEmail($to, $candidateName, $offerTitle, $organisationName) {
        $subject = "Félicitations ! Votre candidature a été acceptée - " . $offerTitle;
        
        // Corps de l'email en HTML
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #888; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🎉 Félicitations !</h1>
                </div>
                <div class='content'>
                    <p>Bonjour <strong>" . htmlspecialchars($candidateName) . "</strong>,</p>
                    
                    <p>Nous avons le plaisir de vous informer que votre candidature pour l'offre <strong>\"" . htmlspecialchars($offerTitle) . "\"</strong> a été <span style='color: #28a745; font-weight: bold;'>ACCEPTÉE</span> par <strong>" . htmlspecialchars($organisationName) . "</strong>.</p>
                    
                    <p>Votre profil et votre motivation ont retenu notre attention. Nous sommes impatients de collaborer avec vous sur ce projet !</p>
                    
                    <p><strong>Prochaines étapes :</strong></p>
                    <ul>
                        <li>Vous serez contacté(e) prochainement par notre équipe</li>
                        <li>Préparez vos documents si nécessaire</li>
                        <li>Restez disponible pour les échanges à venir</li>
                    </ul>
                    
                    <p>Si vous avez des questions, n'hésitez pas à nous contacter.</p>
                    
                    <p>Cordialement,<br>
                    <strong>" . htmlspecialchars($organisationName) . "</strong><br>
                    L'équipe PeaceLink</p>
                </div>
                <div class='footer'>
                    <p>Cet email a été envoyé automatiquement depuis la plateforme PeaceLink</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Headers pour l'email HTML
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: noreply@peacelink.com" . "\r\n";
        $headers .= "Reply-To: " . $to . "\r\n";
        
        // Envoi de l'email
        return mail($to, $subject, $message, $headers);
    }
    
    /**
     * Envoie un email de notification de refus (optionnel)
     */
    public static function sendRejectionEmail($to, $candidateName, $offerTitle, $organisationName) {
        $subject = "Mise à jour de votre candidature - " . $offerTitle;
        
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #6c757d; color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #888; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Mise à jour de votre candidature</h1>
                </div>
                <div class='content'>
                    <p>Bonjour <strong>" . htmlspecialchars($candidateName) . "</strong>,</p>
                    
                    <p>Nous vous remercions sincèrement pour l'intérêt que vous avez porté à l'offre <strong>\"" . htmlspecialchars($offerTitle) . "\"</strong>.</p>
                    
                    <p>Après étude attentive des candidatures, nous avons le regret de vous informer que votre profil n'a pas été retenu pour cette mission.</p>
                    
                    <p>Nous vous encourageons à continuer à consulter nos offres. D'autres opportunités pourraient correspondre davantage à votre profil.</p>
                    
                    <p>Nous vous souhaitons bonne chance dans vos recherches.</p>
                    
                    <p>Cordialement,<br>
                    <strong>" . htmlspecialchars($organisationName) . "</strong><br>
                    L'équipe PeaceLink</p>
                </div>
                <div class='footer'>
                    <p>Cet email a été envoyé automatiquement depuis la plateforme PeaceLink</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: noreply@peacelink.com" . "\r\n";
        
        return mail($to, $subject, $message, $headers);
    }
    
    /**
     * Envoie un email professionnel de réinitialisation de mot de passe
     * @param string $to - Email de l'utilisateur
     * @param string $userName - Nom de l'utilisateur
     * @param string $resetLink - Lien de réinitialisation avec token
     * @return bool - True si l'email est envoyé, false sinon
     */
    public static function sendPasswordResetEmail($to, $userName, $resetLink) {
        $subject = "🔒 Réinitialisation de votre mot de passe - PeaceLink";
        
        $message = "
        <html>
        <head>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f6f9; }
                .email-container { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #5dade2 0%, #7bd389 100%); color: white; padding: 40px 30px; text-align: center; }
                .header-icon { font-size: 60px; margin-bottom: 10px; }
                .header h1 { margin: 0; font-size: 28px; font-weight: 700; }
                .content { padding: 40px 30px; background: #ffffff; }
                .content p { margin: 15px 0; font-size: 16px; color: #555; }
                .content strong { color: #2c3e50; }
                .button-container { text-align: center; margin: 35px 0; }
                .reset-button { 
                    display: inline-block; 
                    padding: 16px 40px; 
                    background: linear-gradient(135deg, #5dade2, #7bd389);
                    color: white; 
                    text-decoration: none; 
                    border-radius: 30px; 
                    font-weight: bold; 
                    font-size: 16px;
                    box-shadow: 0 4px 15px rgba(93, 173, 226, 0.4);
                    transition: all 0.3s ease;
                }
                .reset-button:hover { 
                    transform: translateY(-2px);
                    box-shadow: 0 6px 20px rgba(93, 173, 226, 0.5);
                }
                .info-box { 
                    background: #e8f8f5; 
                    border-left: 4px solid #5dade2; 
                    padding: 15px; 
                    margin: 25px 0; 
                    border-radius: 6px;
                }
                .info-box p { margin: 5px 0; font-size: 14px; color: #34495e; }
                .footer { 
                    background: #f8f9fa; 
                    padding: 25px 30px; 
                    text-align: center; 
                    border-top: 1px solid #e9ecef;
                }
                .footer p { 
                    margin: 5px 0; 
                    font-size: 13px; 
                    color: #7f8c8d; 
                }
                .security-note {
                    background: #fff3cd;
                    border-left: 4px solid #ffc107;
                    padding: 15px;
                    margin: 25px 0;
                    border-radius: 6px;
                }
                .security-note p {
                    margin: 5px 0;
                    font-size: 14px;
                    color: #856404;
                }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='header'>
                    <div class='header-icon'>🔐</div>
                    <h1>Réinitialisation de mot de passe</h1>
                </div>
                
                <div class='content'>
                    <p>Bonjour <strong>" . htmlspecialchars($userName) . "</strong>,</p>
                    
                    <p>Nous avons reçu une demande de réinitialisation de mot de passe pour votre compte <strong>PeaceLink</strong>.</p>
                    
                    <p>Pour créer un nouveau mot de passe sécurisé, cliquez sur le bouton ci-dessous :</p>
                    
                    <div class='button-container'>
                        <a href='" . htmlspecialchars($resetLink) . "' class='reset-button'>
                            🔑 Réinitialiser mon mot de passe
                        </a>
                    </div>
                    
                    <div class='info-box'>
                        <p><strong>⏱️ Important :</strong></p>
                        <p>• Ce lien est valide pendant <strong>1 heure</strong></p>
                        <p>• Il ne peut être utilisé qu'<strong>une seule fois</strong></p>
                        <p>• Après utilisation, il sera automatiquement désactivé</p>
                    </div>
                    
                    <p style='font-size: 14px; color: #7f8c8d;'>Si le bouton ne fonctionne pas, copiez et collez ce lien dans votre navigateur :</p>
                    <p style='word-break: break-all; font-size: 13px; color: #5dade2; background: #f8f9fa; padding: 10px; border-radius: 5px;'>" . htmlspecialchars($resetLink) . "</p>
                    
                    <div class='security-note'>
                        <p><strong>⚠️ Vous n'avez pas demandé cette réinitialisation ?</strong></p>
                        <p>Si vous n'êtes pas à l'origine de cette demande, ignorez cet email. Votre compte reste sécurisé.</p>
                    </div>
                    
                    <p style='margin-top: 30px;'>Cordialement,<br>
                    <strong>L'équipe PeaceLink</strong> 🌍</p>
                </div>
                
                <div class='footer'>
                    <p><strong>PeaceLink</strong> - Plateforme d'engagement citoyen</p>
                    <p>📧 Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
                    <p style='margin-top: 10px;'>© " . date('Y') . " PeaceLink. Tous droits réservés.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: PeaceLink <noreply@peacelink.com>" . "\r\n";
        $headers .= "Reply-To: support@peacelink.com" . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        return mail($to, $subject, $message, $headers);
    }
}
?>
