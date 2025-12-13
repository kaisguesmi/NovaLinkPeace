<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - PeaceLink</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="js/face-api.min.js"></script>

    <style>
        /* --- THEME PEACELINK --- */
        :root {
            --bleu-pastel: #5dade2;
            --vert-doux: #7bd389;
            --blanc-pur: #ffffff;
            --gris-fonce: #2c3e50;
            --rouge-erreur: #e74c3c;
        }

        body {
            margin: 0; padding: 0;
            background: linear-gradient(135deg, var(--bleu-pastel), var(--vert-doux));
            display: flex; justify-content: center; align-items: center; min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
        }

        .card {
            background: var(--blanc-pur); padding: 40px; border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2); width: 100%; max-width: 400px;
            text-align: center; animation: fadeIn 0.6s ease-out;
        }

        .logo-area { margin-bottom: 10px; color: var(--vert-doux); font-size: 50px; }
        h2 { color: var(--gris-fonce); margin-bottom: 30px; font-weight: 700; }

        .input-group { position: relative; margin-bottom: 15px; text-align: left; }
        .input-group i { position: absolute; left: 15px; top: 14px; color: #aaa; }
        .input-group input {
            width: 100%; padding: 12px 15px 12px 45px; border: 1px solid #ddd;
            border-radius: 50px; font-size: 15px; box-sizing: border-box; background-color: #f9f9f9;
        }
        .input-group input:focus { outline: none; border-color: var(--bleu-pastel); background: #fff; }

        /* Style erreur JS */
        .error-text { 
            color: var(--rouge-erreur); font-size: 12px; margin-top: 5px; 
            padding-left: 15px; display: none; font-weight: bold;
        }
        .input-error { border-color: var(--rouge-erreur) !important; background-color: #fdedec !important; }

        .btn-primary {
            width: 100%; padding: 12px;
            background: linear-gradient(to right, var(--bleu-pastel), var(--vert-doux));
            color: white; border: none; border-radius: 50px; font-weight: bold; font-size: 16px;
            cursor: pointer; transition: transform 0.2s; margin-top: 10px;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(93, 173, 226, 0.4); }

        .links { display: flex; justify-content: space-between; margin-top: 15px; font-size: 13px; }
        .links a { color: #7f8c8d; text-decoration: none; }
        .links a:hover { color: var(--bleu-pastel); text-decoration: underline; }

        .separator { display: flex; align-items: center; color: #ccc; margin: 25px 0; font-size: 14px; }
        .separator::before, .separator::after { content: ''; flex: 1; border-bottom: 1px solid #eee; }
        .separator::before { margin-right: .5em; } .separator::after { margin-left: .5em; }

        .btn-face {
            width: 100%; padding: 12px; background-color: #2c3e50; color: white;
            border: none; border-radius: 50px; font-weight: 600; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .btn-face:hover { background-color: #34495e; }

        .error-msg { color: #e74c3c; background: #fdedec; padding: 10px; border-radius: 8px; font-size: 13px; margin-bottom: 20px; border-left: 4px solid #e74c3c; text-align: left; }

        #camera-container {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(44, 62, 80, 0.95); z-index: 2000;
            flex-direction: column; align-items: center; justify-content: center;
        }
        video { border-radius: 20px; border: 4px solid var(--vert-doux); max-width: 90%; }
        #status-text { color: white; margin-top: 20px; font-size: 20px; font-weight: 500; }
        .btn-cancel { margin-top: 30px; padding: 10px 30px; background: rgba(255,255,255,0.2); border: 2px solid white; color: white; border-radius: 50px; cursor: pointer; font-weight: bold; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

    <div class="card">
        <div class="logo-area"><i class="fa-solid fa-leaf"></i></div>
        <h2>Connexion</h2>

        <?php if (isset($_SESSION['error_login'])): ?>
            <div class="error-msg">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo $_SESSION['error_login']; unset($_SESSION['error_login']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success_login'])): ?>
            <div class="error-msg" style="color:#27ae60; background:#e8f8f5; border-color:#2ecc71;">
                <i class="fa-solid fa-check-circle"></i> <?php echo $_SESSION['success_login']; unset($_SESSION['success_login']); ?>
            </div>
        <?php endif; ?>

        <!-- FORMULAIRE AVEC NOVALIDATE -->
        <form id="login-form" action="../../Controller/UtilisateurController.php" method="POST" novalidate>
            <input type="hidden" name="action" value="login">
            
            <div class="input-group">
                <i class="fa-solid fa-envelope"></i>
                <input type="email" id="email" name="email" placeholder="Adresse email">
                <!-- Div erreur JS -->
                <div id="err-email" class="error-text"></div>
            </div>
            
            <div class="input-group">
                <i class="fa-solid fa-lock"></i>
                <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="Mot de passe">
                <!-- Div erreur JS -->
                <div id="err-pass" class="error-text"></div>
            </div>

            <button type="submit" class="btn-primary">Se connecter <i class="fa-solid fa-arrow-right"></i></button>
        </form>

        <div class="links">
            <a href="forgot_password.php">Mot de passe oublié ?</a>
            <a href="inscription.php">Créer un compte</a>
        </div>

        <div class="separator">OU</div>

        <button id="btn-face-id" class="btn-face">
            <i class="fa-solid fa-face-smile-beam"></i> Se connecter avec Face ID
        </button>
    </div>

    <!-- ZONE CAMERA -->
    <div id="camera-container">
        <video id="video" width="600" height="450" autoplay muted></video>
        <div id="status-text">Initialisation de l'IA...</div>
        <button onclick="closeCamera()" class="btn-cancel">Annuler</button>
    </div>

    <!-- SCRIPTS -->
    <script>
        // 1. VALIDATION DU FORMULAIRE CLASSIQUE
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('login-form');
            const emailInput = document.getElementById('email');
            const passInput = document.getElementById('mot_de_passe');
            const errEmail = document.getElementById('err-email');
            const errPass = document.getElementById('err-pass');

            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Reset affichage
                [emailInput, passInput].forEach(el => el.classList.remove('input-error'));
                [errEmail, errPass].forEach(el => { el.style.display = 'none'; el.innerText = ''; });

                // Verif Email
                if (emailInput.value.trim() === '') {
                    showError(emailInput, errEmail, "L'email est requis.");
                    isValid = false;
                }

                // Verif Mot de passe
                if (passInput.value.trim() === '') {
                    showError(passInput, errPass, "Le mot de passe est requis.");
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault(); // Bloque l'envoi si erreur
                }
            });

            function showError(input, div, msg) {
                input.classList.add('input-error');
                div.innerText = msg;
                div.style.display = 'block';
            }
        });

        // 2. GESTION FACE ID (AVEC PROTECTION)
        const video = document.getElementById('video');
        const container = document.getElementById('camera-container');
        const statusText = document.getElementById('status-text');
        const btnFaceId = document.getElementById('btn-face-id');
        let profileDescriptor = null;

        Promise.all([
            faceapi.nets.ssdMobilenetv1.loadFromUri('js/models'),
            faceapi.nets.faceLandmark68Net.loadFromUri('js/models'),
            faceapi.nets.faceRecognitionNet.loadFromUri('js/models')
        ]).then(() => console.log("IA Chargée"));

        btnFaceId.addEventListener('click', async () => {
            const email = document.getElementById('email').value;
            const errEmail = document.getElementById('err-email');
            
            // Validation spécifique pour FaceID
            if (!email) {
                document.getElementById('email').classList.add('input-error');
                errEmail.innerText = "Entrez votre email pour Face ID.";
                errEmail.style.display = 'block';
                return;
            } else {
                // Si l'email est là, on nettoie les erreurs visuelles
                document.getElementById('email').classList.remove('input-error');
                errEmail.style.display = 'none';
            }

            const originalText = btnFaceId.innerHTML;
            btnFaceId.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Vérification...';
            btnFaceId.disabled = true;

            const formData = new FormData();
            formData.append('action', 'ajax_get_photo');
            formData.append('email', email);

            try {
                const response = await fetch('../../Controller/UtilisateurController.php', { method: 'POST', body: formData });
                const data = await response.json();

                if (data.success) {
                    const img = await faceapi.fetchImage('../uploads/' + data.photo);
                    const detection = await faceapi.detectSingleFace(img).withFaceLandmarks().withFaceDescriptor();

                    if (detection) {
                        profileDescriptor = detection.descriptor;
                        // Ouverture caméra seulement si OK
                        container.style.display = 'flex'; 
                        statusText.innerText = "Regardez la caméra...";
                        startVideo();
                    } else {
                        alert("Erreur : Impossible de détecter un visage sur votre photo de profil.");
                    }
                } else {
                    alert("Erreur : " + data.message);
                }
            } catch (e) {
                console.error(e);
                alert("Erreur technique de connexion.");
            } finally {
                btnFaceId.innerHTML = originalText;
                btnFaceId.disabled = false;
            }
        });

        function startVideo() {
            navigator.mediaDevices.getUserMedia({ video: {} })
                .then(stream => video.srcObject = stream)
                .catch(err => {
                    console.error(err);
                    alert("Impossible d'accéder à la webcam.");
                    closeCamera();
                });
        }

        video.addEventListener('play', () => {
            const interval = setInterval(async () => {
                if (container.style.display === 'none') { clearInterval(interval); return; }

                const detection = await faceapi.detectSingleFace(video).withFaceLandmarks().withFaceDescriptor();

                if (detection) {
                    const distance = faceapi.euclideanDistance(profileDescriptor, detection.descriptor);
                    
                    if (distance < 0.5) { 
                        clearInterval(interval);
                        statusText.innerText = "✅ VISAGE RECONNU ! Connexion...";
                        statusText.style.color = "#2ecc71";

                        const emailUser = document.getElementById('email').value;
                        const formData = new FormData();
                        formData.append('action', 'login_with_face');
                        formData.append('email', emailUser);

                        fetch('../../Controller/UtilisateurController.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) window.location.href = data.redirect || "index.php";
                            else { alert(data.message); closeCamera(); }
                        });
                    } else {
                        statusText.innerText = "Visage non reconnu... (" + Math.round(distance*100) + "%)";
                        statusText.style.color = "#e74c3c";
                    }
                }
            }, 500);
        });

        function closeCamera() {
            container.style.display = 'none';
            if(video.srcObject) video.srcObject.getTracks().forEach(t => t.stop());
        }
    </script>
</body>
</html>