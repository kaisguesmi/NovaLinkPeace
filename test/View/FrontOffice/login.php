<?php 
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - PeaceLink</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <script src="js/face-api.min.js"></script>
    <style>
        body { 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            background: linear-gradient(135deg, #5dade2, #7bd389); 
            padding: 20px;
        }
        
        .login-container { 
            background-color: white; 
            padding: 40px; 
            border-radius: 20px; 
            box-shadow: 0 15px 40px rgba(0,0,0,0.2); 
            width: 100%; 
            max-width: 450px;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from { transform: translateY(-30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .login-container h1 { 
            text-align: center; 
            color: #2c3e50; 
            margin-bottom: 10px;
            font-size: 32px;
            font-weight: 700;
        }

        .subtitle {
            text-align: center;
            color: #7f8c8d;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .form-group { 
            margin-bottom: 20px; 
        }
        
        .form-group label { 
            display: block; 
            font-weight: 600; 
            margin-bottom: 8px;
            color: #555;
            font-size: 14px;
        }
        
        .form-group input { 
            width: 100%; 
            padding: 14px 15px; 
            border: 2px solid #e0e0e0; 
            border-radius: 10px; 
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            border-color: #5dade2;
            outline: none;
            box-shadow: 0 0 0 3px rgba(93, 173, 226, 0.1);
        }

        .input-error {
            border-color: #e74c3c !important;
            animation: shake 0.5s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .error-text {
            color: #e74c3c;
            font-size: 12px;
            margin-top: 5px;
            display: none;
            font-weight: 500;
        }
        
        .error-message { 
            background: #fee;
            color: #e74c3c; 
            font-size: 14px; 
            text-align: center; 
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #e74c3c;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            font-size: 14px;
            text-align: center;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }

        .btn-primary { 
            width: 100%; 
            padding: 15px; 
            background: linear-gradient(135deg, #5dade2, #7bd389);
            color: white; 
            border: none; 
            border-radius: 12px; 
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(93, 173, 226, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(93, 173, 226, 0.4);
        }

        .btn-face-id {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            margin-top: 15px;
        }

        .btn-face-id:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-face-id:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .divider {
            text-align: center;
            margin: 25px 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 1px;
            background: #e0e0e0;
        }

        .divider span {
            background: white;
            padding: 0 15px;
            position: relative;
            color: #7f8c8d;
            font-size: 14px;
        }

        .links {
            text-align: center;
            margin-top: 20px;
        }

        .links a {
            color: #5dade2;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .links a:hover {
            color: #7bd389;
        }

        /* Camera Container */
        #camera-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            display: none;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            z-index: 10000;
        }

        #video {
            max-width: 640px;
            width: 90%;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
        }

        #status-text {
            color: white;
            font-size: 24px;
            margin-top: 20px;
            font-weight: bold;
            text-align: center;
        }

        .close-camera {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #e74c3c;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }

        .close-camera:hover {
            background: #c0392b;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1><i class="fa-solid fa-right-to-bracket"></i> Connexion</h1>
        <p class="subtitle">Bienvenue sur PeaceLink</p>

        <?php 
            if (isset($_SESSION['error_login'])) {
                echo "<p class='error-message'><i class='fa-solid fa-circle-exclamation'></i> " . $_SESSION['error_login'] . "</p>";
                unset($_SESSION['error_login']);
            }
            if (isset($_SESSION['success_login'])) {
                echo "<p class='success-message'><i class='fa-solid fa-circle-check'></i> " . $_SESSION['success_login'] . "</p>";
                unset($_SESSION['success_login']);
            }
        ?>

        <form id="login-form" action="../../Controller/UtilisateurController.php" method="POST" novalidate>
            <input type="hidden" name="action" value="login">
            
            <div class="form-group">
                <label for="email"><i class="fa-solid fa-envelope"></i> Email</label>
                <input type="text" id="email" name="email" placeholder="votre@email.com">
                <div id="err-email" class="error-text"></div>
            </div>

            <div class="form-group">
                <label for="mot_de_passe"><i class="fa-solid fa-lock"></i> Mot de passe</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="••••••••">
                <div id="err-password" class="error-text"></div>
            </div>

            <div style="text-align: right; margin-bottom: 15px;">
                <a href="forgot_password.php" style="font-size: 14px; color: #5dade2;">
                    <i class="fa-solid fa-key"></i> Mot de passe oublié ?
                </a>
            </div>

            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-sign-in-alt"></i> Se connecter
            </button>
        </form>

        <div class="divider">
            <span>OU</span>
        </div>

        <button id="btn-face-id" class="btn-face-id">
            <i class="fa-solid fa-user-lock"></i> Se connecter avec Face ID
        </button>

        <p style="text-align:center; color:#7f8c8d; font-size:13px; margin-top:10px;">
            Face ID disponible pour les clients, experts et administrateurs.
        </p>

        <div class="links">
            <p style="color: #7f8c8d; margin-bottom: 10px;">Pas encore de compte ?</p>
            <a href="inscription.php"><i class="fa-solid fa-user-plus"></i> Créer un compte</a>
        </div>
    </div>

    <!-- Camera Container -->
    <div id="camera-container">
        <button class="close-camera" onclick="closeCamera()">
            <i class="fa-solid fa-times"></i> Fermer
        </button>
        <video id="video" autoplay muted></video>
        <p id="status-text">Initialisation...</p>
    </div>

    <script>
        // Attendre que la page et face-api soient chargés
        window.addEventListener('load', function() {
            console.log("🚀 Page chargée");
            
            // Vérifier si face-api est chargé
            if (typeof faceapi === 'undefined') {
                console.error("❌ face-api.min.js n'est pas chargé !");
                alert("Erreur: Bibliothèque Face ID non chargée. Vérifiez la console.");
                return;
            }
            
            console.log("✅ face-api détecté");
            
            // 1. VALIDATION FORMULAIRE CLASSIQUE
            const loginForm = document.getElementById('login-form');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('mot_de_passe');
            const errEmail = document.getElementById('err-email');
            const errPassword = document.getElementById('err-password');

            loginForm.addEventListener('submit', function(e) {
                let isValid = true;

                // Reset
                [emailInput, passwordInput].forEach(el => el.classList.remove('input-error'));
                [errEmail, errPassword].forEach(el => { el.style.display = 'none'; el.innerText = ''; });

                // Validation Email
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (emailInput.value.trim() === '') {
                    showError(emailInput, errEmail, "L'email est requis.");
                    isValid = false;
                } else if (!emailRegex.test(emailInput.value)) {
                    showError(emailInput, errEmail, "Format d'email invalide.");
                    isValid = false;
                }

                // Validation Mot de passe
                if (passwordInput.value === '') {
                    showError(passwordInput, errPassword, "Le mot de passe est requis.");
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                }
            });

            function showError(input, errorDiv, message) {
                input.classList.add('input-error');
                errorDiv.innerText = message;
                errorDiv.style.display = 'block';
            }

            // 2. GESTION FACE ID (AVEC PROTECTION)
            const video = document.getElementById('video');
            const container = document.getElementById('camera-container');
            const statusText = document.getElementById('status-text');
            const btnFaceId = document.getElementById('btn-face-id');
            let profileDescriptor = null;

            console.log("📸 Chargement des modèles IA...");
            
            Promise.all([
                faceapi.nets.ssdMobilenetv1.loadFromUri('js/models'),
                faceapi.nets.faceLandmark68Net.loadFromUri('js/models'),
                faceapi.nets.faceRecognitionNet.loadFromUri('js/models')
            ]).then(() => {
                console.log("✅ IA Face Recognition chargée avec succès !");
            }).catch(err => {
                console.error("❌ Erreur chargement modèles IA:", err);
                alert("Erreur de chargement des modèles IA. Vérifiez le dossier js/models/");
            });

            btnFaceId.addEventListener('click', async () => {
                console.log("🔘 Bouton Face ID cliqué");
                
                const email = emailInput.value;
                
                // Validation spécifique pour FaceID
                if (!email) {
                    console.log("⚠️ Email vide");
                    emailInput.classList.add('input-error');
                    errEmail.innerText = "Entrez votre email pour Face ID.";
                    errEmail.style.display = 'block';
                    return;
                } else {
                    emailInput.classList.remove('input-error');
                    errEmail.style.display = 'none';
                }

                console.log("📧 Email:", email);
                
                const originalText = btnFaceId.innerHTML;
                btnFaceId.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Vérification...';
                btnFaceId.disabled = true;

                const formData = new FormData();
                formData.append('action', 'ajax_get_photo');
                formData.append('email', email);

                try {
                    console.log("🌐 Envoi requête AJAX...");
                    const response = await fetch('../../Controller/UtilisateurController.php', { method: 'POST', body: formData });
                    console.log("📡 Statut HTTP:", response.status, response.statusText);
                    
                    // Lire la réponse en texte brut d'abord pour déboguer
                    const responseText = await response.text();
                    console.log("📄 Réponse brute:", responseText);
                    
                    // Parser en JSON
                    let data;
                    try {
                        data = JSON.parse(responseText);
                    } catch(e) {
                        console.error("❌ La réponse n'est pas du JSON valide:", e);
                        throw new Error("Réponse serveur invalide (pas JSON): " + responseText.substring(0, 200));
                    }
                    
                    console.log("📥 Réponse serveur:", data);

                    if (data.success) {
                        console.log("📷 Chargement photo:", data.photo);
                        const img = await faceapi.fetchImage('../uploads/' + data.photo);
                        console.log("🔍 Détection visage sur photo...");
                        const detection = await faceapi.detectSingleFace(img).withFaceLandmarks().withFaceDescriptor();

                        if (detection) {
                            console.log("✅ Visage détecté sur photo !");
                            profileDescriptor = detection.descriptor;
                            container.style.display = 'flex'; 
                            statusText.innerText = "📸 Regardez la caméra...";
                            statusText.style.color = "#5dade2";
                            startVideo();
                        } else {
                            console.error("❌ Aucun visage détecté sur photo");
                            alert("❌ Erreur : Impossible de détecter un visage sur votre photo de profil.");
                        }
                    } else {
                        console.error("❌ Erreur serveur:", data.message);
                        alert("❌ Erreur : " + data.message);
                    }
                } catch (e) {
                    console.error("❌ Exception:", e);
                    alert("❌ Erreur technique de connexion.");
                } finally {
                    btnFaceId.innerHTML = originalText;
                    btnFaceId.disabled = false;
                }
            });

            function startVideo() {
                console.log("📹 Démarrage webcam...");
                navigator.mediaDevices.getUserMedia({ video: {} })
                    .then(stream => {
                        console.log("✅ Webcam activée");
                        video.srcObject = stream;
                    })
                    .catch(err => {
                        console.error("❌ Erreur webcam:", err);
                        alert("❌ Impossible d'accéder à la webcam.");
                        closeCamera();
                    });
            }

            video.addEventListener('play', () => {
                console.log("▶️ Vidéo en lecture, démarrage détection...");
                const interval = setInterval(async () => {
                    if (container.style.display === 'none') { 
                        clearInterval(interval); 
                        console.log("⏹️ Arrêt détection");
                        return; 
                    }

                    const detection = await faceapi.detectSingleFace(video).withFaceLandmarks().withFaceDescriptor();

                    if (detection) {
                        const distance = faceapi.euclideanDistance(profileDescriptor, detection.descriptor);
                        console.log("📏 Distance:", distance);
                        
                        if (distance < 0.5) { 
                            clearInterval(interval);
                            console.log("✅ MATCH ! Connexion...");
                            statusText.innerText = "✅ VISAGE RECONNU ! Connexion...";
                            statusText.style.color = "#2ecc71";

                            const emailUser = emailInput.value;
                            console.log("📧 Email pour connexion:", emailUser);
                            
                            const formData = new FormData();
                            formData.append('action', 'login_with_face');
                            formData.append('email', emailUser);

                            console.log("🌐 Envoi requête login_with_face...");
                            
                            fetch('../../Controller/UtilisateurController.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(res => {
                                console.log("📡 Statut réponse login:", res.status);
                                return res.text();
                            })
                            .then(responseText => {
                                console.log("📄 Réponse login brute (longueur " + responseText.length + "):", responseText);
                                console.log("📄 Premiers 500 caractères:", responseText.substring(0, 500));
                                
                                if (responseText.length === 0) {
                                    console.error("❌ RÉPONSE VIDE !");
                                    alert("Erreur: Le serveur n'a rien renvoyé. Vérifiez les logs PHP.");
                                    closeCamera();
                                    return;
                                }
                                
                                let data;
                                try {
                                    data = JSON.parse(responseText);
                                } catch(e) {
                                    console.error("❌ Réponse login pas JSON:", e);
                                    alert("Erreur serveur lors de la connexion: " + responseText.substring(0, 200));
                                    closeCamera();
                                    return;
                                }
                                
                                console.log("🔐 Réponse login:", data);
                                if (data.success) {
                                    console.log("✅ Connexion réussie ! Redirection vers:", data.redirect);
                                    window.location.href = data.redirect || "index.php";
                                } else { 
                                    console.error("❌ Échec connexion:", data.message);
                                    alert("❌ " + data.message); 
                                    closeCamera(); 
                                }
                            })
                            .catch(err => {
                                console.error("❌ Erreur fetch login:", err);
                                alert("Erreur de connexion: " + err.message);
                                closeCamera();
                            });
                        } else {
                            statusText.innerText = "❌ Visage non reconnu... (" + Math.round(distance*100) + "%)";
                            statusText.style.color = "#e74c3c";
                        }
                    }
                }, 500);
            });

            function closeCamera() {
                console.log("📷 Fermeture caméra");
                container.style.display = 'none';
                if(video.srcObject) video.srcObject.getTracks().forEach(t => t.stop());
            }
            
            // Fonction globale pour le bouton fermer
            window.closeCamera = closeCamera;
        });
    </script>
</body>
</html>