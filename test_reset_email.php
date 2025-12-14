<?php
// Test de l'email de réinitialisation de mot de passe
require_once 'model/EmailService.php';

// Simuler un lien de réinitialisation
$resetLink = "http://localhost/integration/NovaLinkPeace/test/View/FrontOffice/reset_password.php?token=abc123def456";
$userName = "Jean Dupont";
$email = "test@example.com";

// Afficher l'email au lieu de l'envoyer
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

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Email - Réinitialisation de mot de passe</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f2f5;
            padding: 20px;
        }
        .test-info {
            max-width: 800px;
            margin: 0 auto 20px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .test-info h2 {
            color: #5dade2;
            margin-top: 0;
        }
        .preview-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="test-info">
        <h2>📧 Aperçu de l'email de réinitialisation</h2>
        <p><strong>Destinataire :</strong> <?= htmlspecialchars($email) ?></p>
        <p><strong>Sujet :</strong> <?= htmlspecialchars($subject) ?></p>
        <p><strong>Nom d'utilisateur :</strong> <?= htmlspecialchars($userName) ?></p>
        <p style="color: #7bd389; font-weight: bold;">✅ Email professionnel et design moderne</p>
    </div>
    
    <div class="preview-container">
        <h3 style="text-align: center; color: #555;">Aperçu de l'email</h3>
        <hr style="margin: 20px 0;">
        <?= $message ?>
    </div>
</body>
</html>
