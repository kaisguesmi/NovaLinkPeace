<<<<<<< HEAD
<?php
session_start();
// Sécurité : Si l'utilisateur n'est pas banni, on le renvoie à l'accueil
if (!isset($_SESSION['banned_reason'])) {
    header("Location: index.php"); 
    exit();
}

$reason = $_SESSION['banned_reason'];
$dateEnd = $_SESSION['banned_until'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Compte Suspendu - PeaceLink</title>
    <!-- On inclut FontAwesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* --- THEME PEACELINK --- */
        :root {
            --bleu-pastel: #5dade2;
            --vert-doux: #7bd389;
            --rouge-alerte: #e74c3c;
            --blanc-pur: #ffffff;
            --gris-fonce: #2c3e50;
        }

        body { 
            margin: 0;
            padding: 0;
            /* Le fameux dégradé PeaceLink */
            background: linear-gradient(135deg, var(--bleu-pastel), var(--vert-doux));
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            color: var(--gris-fonce);
        }

        .ban-card { 
            background: var(--blanc-pur); 
            padding: 50px; 
            border-radius: 20px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.2); 
            max-width: 550px; 
            width: 90%; 
            text-align: center;
            animation: fadeIn 0.8s ease-out;
        }

        .icon-container {
            margin-bottom: 20px;
        }

        .icon { 
            font-size: 80px; 
            color: #f39c12; /* Orange avertissement */
            animation: bounce 2s infinite;
        }

        h1 { 
            color: var(--rouge-alerte); 
            font-size: 2.5em; 
            margin: 10px 0; 
            font-weight: 700;
        }
        
        p.subtitle {
            color: #7f8c8d;
            font-size: 1.1em;
            margin-bottom: 30px;
        }

        /* Boîte de raison stylisée */
        .reason-box { 
            background: #fdedec; /* Fond rouge très clair */
            padding: 20px; 
            border-radius: 10px; 
            margin: 25px 0; 
            border-left: 5px solid var(--rouge-alerte); 
            text-align: left; 
            font-size: 1em;
            color: #c0392b;
        }
        
        .timer-label {
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #95a5a6;
            margin-top: 30px;
        }

        .timer { 
            font-size: 2.5em; 
            font-weight: bold; 
            margin: 10px 0 30px 0; 
            color: var(--gris-fonce);
            font-family: 'Courier New', Courier, monospace; /* Pour éviter que les chiffres bougent */
        }

        /* Bouton style PeaceLink */
        .btn-home { 
            display: inline-block; 
            padding: 12px 30px; 
            background: linear-gradient(to right, var(--bleu-pastel), var(--vert-doux)); 
            color: white; 
            text-decoration: none; 
            border-radius: 50px; 
            font-weight: bold; 
            box-shadow: 0 4px 15px rgba(93, 173, 226, 0.4);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(93, 173, 226, 0.6);
        }

        /* Petites animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0);} 
            40% {transform: translateY(-10px);} 
            60% {transform: translateY(-5px);} 
        }
    </style>
</head>
<body>

    <div class="ban-card">
        <div class="icon-container">
            <i class="fa-solid fa-triangle-exclamation icon"></i>
        </div>
        
        <h1>Accès Suspendu</h1>
        <p class="subtitle">Votre compte a été temporairement mis en pause par l'administration de PeaceLink.</p>

        <div class="reason-box">
            <strong><i class="fa-solid fa-circle-info"></i> Motif :</strong><br>
            <?php echo htmlspecialchars($reason); ?>
        </div>

        <div class="timer-label">Temps restant avant réactivation</div>
        <div id="countdown" class="timer">Calcul...</div>

        <a href="index.php" class="btn-home">
            <i class="fa-solid fa-arrow-left"></i> Retour à l'accueil
        </a>
    </div>

    <script>
        // Date de fin récupérée depuis PHP
        const countDownDate = new Date("<?php echo $dateEnd; ?>").getTime();

        const x = setInterval(function() {
            const now = new Date().getTime();
            const distance = countDownDate - now;

            if (distance < 0) {
                clearInterval(x);
                const timerElement = document.getElementById("countdown");
                timerElement.innerHTML = "<span style='color:#27ae60'>Bannissement terminé !</span>";
                timerElement.style.fontSize = "1.5em";
                
                // On recharge la page pour que PHP détecte la fin du ban et redirige
                setTimeout(function(){ location.reload(); }, 3000);
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById("countdown").innerHTML = 
                days + "j " + hours + "h " + minutes + "m " + seconds + "s ";
        }, 1000);
    </script>
</body>
=======
<?php
session_start();
// Sécurité : Si l'utilisateur n'est pas banni, on le renvoie à l'accueil
if (!isset($_SESSION['banned_reason'])) {
    header("Location: index.php"); 
    exit();
}

$reason = $_SESSION['banned_reason'];
$dateEnd = $_SESSION['banned_until'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Compte Suspendu - PeaceLink</title>
    <!-- On inclut FontAwesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* --- THEME PEACELINK --- */
        :root {
            --bleu-pastel: #5dade2;
            --vert-doux: #7bd389;
            --rouge-alerte: #e74c3c;
            --blanc-pur: #ffffff;
            --gris-fonce: #2c3e50;
        }

        body { 
            margin: 0;
            padding: 0;
            /* Le fameux dégradé PeaceLink */
            background: linear-gradient(135deg, var(--bleu-pastel), var(--vert-doux));
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            color: var(--gris-fonce);
        }

        .ban-card { 
            background: var(--blanc-pur); 
            padding: 50px; 
            border-radius: 20px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.2); 
            max-width: 550px; 
            width: 90%; 
            text-align: center;
            animation: fadeIn 0.8s ease-out;
        }

        .icon-container {
            margin-bottom: 20px;
        }

        .icon { 
            font-size: 80px; 
            color: #f39c12; /* Orange avertissement */
            animation: bounce 2s infinite;
        }

        h1 { 
            color: var(--rouge-alerte); 
            font-size: 2.5em; 
            margin: 10px 0; 
            font-weight: 700;
        }
        
        p.subtitle {
            color: #7f8c8d;
            font-size: 1.1em;
            margin-bottom: 30px;
        }

        /* Boîte de raison stylisée */
        .reason-box { 
            background: #fdedec; /* Fond rouge très clair */
            padding: 20px; 
            border-radius: 10px; 
            margin: 25px 0; 
            border-left: 5px solid var(--rouge-alerte); 
            text-align: left; 
            font-size: 1em;
            color: #c0392b;
        }
        
        .timer-label {
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #95a5a6;
            margin-top: 30px;
        }

        .timer { 
            font-size: 2.5em; 
            font-weight: bold; 
            margin: 10px 0 30px 0; 
            color: var(--gris-fonce);
            font-family: 'Courier New', Courier, monospace; /* Pour éviter que les chiffres bougent */
        }

        /* Bouton style PeaceLink */
        .btn-home { 
            display: inline-block; 
            padding: 12px 30px; 
            background: linear-gradient(to right, var(--bleu-pastel), var(--vert-doux)); 
            color: white; 
            text-decoration: none; 
            border-radius: 50px; 
            font-weight: bold; 
            box-shadow: 0 4px 15px rgba(93, 173, 226, 0.4);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(93, 173, 226, 0.6);
        }

        /* Petites animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0);} 
            40% {transform: translateY(-10px);} 
            60% {transform: translateY(-5px);} 
        }
    </style>
</head>
<body>

    <div class="ban-card">
        <div class="icon-container">
            <i class="fa-solid fa-triangle-exclamation icon"></i>
        </div>
        
        <h1>Accès Suspendu</h1>
        <p class="subtitle">Votre compte a été temporairement mis en pause par l'administration de PeaceLink.</p>

        <div class="reason-box">
            <strong><i class="fa-solid fa-circle-info"></i> Motif :</strong><br>
            <?php echo htmlspecialchars($reason); ?>
        </div>

        <div class="timer-label">Temps restant avant réactivation</div>
        <div id="countdown" class="timer">Calcul...</div>

        <a href="index.php" class="btn-home">
            <i class="fa-solid fa-arrow-left"></i> Retour à l'accueil
        </a>
    </div>

    <script>
        // Date de fin récupérée depuis PHP
        const countDownDate = new Date("<?php echo $dateEnd; ?>").getTime();

        const x = setInterval(function() {
            const now = new Date().getTime();
            const distance = countDownDate - now;

            if (distance < 0) {
                clearInterval(x);
                const timerElement = document.getElementById("countdown");
                timerElement.innerHTML = "<span style='color:#27ae60'>Bannissement terminé !</span>";
                timerElement.style.fontSize = "1.5em";
                
                // On recharge la page pour que PHP détecte la fin du ban et redirige
                setTimeout(function(){ location.reload(); }, 3000);
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById("countdown").innerHTML = 
                days + "j " + hours + "h " + minutes + "m " + seconds + "s ";
        }, 1000);
    </script>
</body>
>>>>>>> origin/Taherwork
</html>