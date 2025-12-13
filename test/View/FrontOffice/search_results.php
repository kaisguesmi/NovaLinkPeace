<?php
session_start();
$results = $_SESSION['search_results'] ?? [];
$keyword = $_SESSION['search_keyword'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résultats pour "<?php echo htmlspecialchars($keyword); ?>"</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .results-container { max-width: 800px; margin: 100px auto; padding: 20px; }
        .result-card {
            background: white; padding: 20px; margin-bottom: 15px; border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: space-between;
        }
        .user-info h3 { margin: 0 0 5px 0; color: var(--bleu-nuit); }
        .badge-type { background: #eee; padding: 3px 8px; border-radius: 4px; font-size: 12px; color: #666; }
        .btn-view { text-decoration: none; color: var(--bleu-pastel); font-weight: bold; border: 1px solid var(--bleu-pastel); padding: 5px 15px; border-radius: 5px; transition: 0.3s;}
        .btn-view:hover { background: var(--bleu-pastel); color: white; }
    </style>
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <div class="results-container">
        <h1>Résultats de recherche</h1>
        <p>Pour le terme : <strong><?php echo htmlspecialchars($keyword); ?></strong></p>
        <hr>

        <?php if (empty($results)): ?>
            <p>Aucun utilisateur ou organisation trouvé.</p>
        <?php else: ?>
            <?php foreach ($results as $user): ?>
                <div class="result-card">
                    <div class="user-info">
                        <h3>
                            <?php echo htmlspecialchars($user['nom']); ?> 
                            <span class="badge-type"><?php echo $user['type_compte']; ?></span>
                        </h3>
                        <!-- Affiche bio (Client) ou adresse (Orga) -->
                        <p style="color: #777; margin:0;"><?php echo htmlspecialchars(substr($user['description'], 0, 50)) . '...'; ?></p>
                    </div>
                    <a href="../../Controller/UtilisateurController.php?action=show_public_profile&id=<?php echo $user['id_utilisateur']; ?>" class="btn-view">
                        Voir le profil
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>