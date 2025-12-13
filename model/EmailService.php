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
}
?>
