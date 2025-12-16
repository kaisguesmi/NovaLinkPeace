<?php
session_start();

// Vérifier que l'utilisateur est une organisation
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organisation') {
    header("Location: /integration/NovaLinkPeace/test/View/FrontOffice/login.php");
    exit();
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/EventModel.php';
require_once __DIR__ . '/../models/ParticipationModel.php';

$event_id = $_GET['event_id'] ?? null;

if (!$event_id) {
    header("Location: initiatives.php");
    exit();
}

// Récupérer l'initiative
$event = EventModel::getById($event_id);

if (!$event) {
    die("Initiative introuvable");
}

// Vérifier que l'initiative appartient à cette organisation
if ($event['org_id'] != $_SESSION['user_id']) {
    die("Erreur : Vous ne pouvez voir que les participants de VOS initiatives.");
}

// Récupérer tous les participants
global $pdo;
$sql = "SELECT p.*, c.nom_complet, u.email as client_email, u.photo_profil
        FROM participations p
        LEFT JOIN Client c ON p.id_client = c.id_utilisateur
        LEFT JOIN Utilisateur u ON p.id_client = u.id_utilisateur
        WHERE p.event_id = :event_id
        ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([':event_id' => $event_id]);
$participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_participants = count($participants);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participants - <?= htmlspecialchars($event['title']) ?></title>
    <link rel="stylesheet" href="/integration/NovaLinkPeace/test/View/FrontOffice/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .participants-container {
            max-width: 1200px;
            margin: 100px auto 50px auto;
            padding: 30px;
        }
        
        .event-header {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }
        
        .event-header h1 {
            color: var(--bleu-nuit);
            margin-bottom: 10px;
        }
        
        .event-meta {
            display: flex;
            gap: 20px;
            color: #888;
            font-size: 14px;
            margin-top: 15px;
        }
        
        .event-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .participants-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            text-align: center;
        }
        
        .stat-card i {
            font-size: 40px;
            color: var(--bleu-pastel);
            margin-bottom: 10px;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--bleu-nuit);
        }
        
        .stat-label {
            color: #888;
            font-size: 14px;
        }
        
        .participants-table {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        
        .table-header {
            padding: 20px 30px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .table-header h2 {
            color: var(--bleu-nuit);
            margin: 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: #f9f9f9;
        }
        
        th, td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        th {
            font-weight: 600;
            color: #888;
            font-size: 14px;
            text-transform: uppercase;
        }
        
        .participant-cell {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .participant-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: var(--bleu-pastel);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 18px;
            flex-shrink: 0;
            overflow: hidden;
        }
        
        .participant-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .participant-info {
            flex-grow: 1;
        }
        
        .participant-name {
            font-weight: 600;
            color: var(--bleu-nuit);
            margin-bottom: 3px;
        }
        
        .participant-email {
            font-size: 13px;
            color: #888;
        }
        
        .message-cell {
            max-width: 300px;
            color: #555;
            font-size: 14px;
            line-height: 1.4;
        }
        
        .date-cell {
            color: #888;
            font-size: 13px;
            white-space: nowrap;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            padding: 10px 20px;
            background: var(--gris-moyen);
            color: var(--gris-fonce);
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .back-btn:hover {
            background: var(--bleu-pastel);
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #888;
        }
        
        .empty-state i {
            font-size: 60px;
            color: #ddd;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../test/View/FrontOffice/partials/header.php'; ?>
    
    <div class="participants-container">
        <a href="initiatives.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Retour aux initiatives
        </a>
        
        <div class="event-header">
            <h1><?= htmlspecialchars($event['title']) ?></h1>
            <div class="event-meta">
                <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['location']) ?></span>
                <span><i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($event['date'])) ?></span>
                <span><i class="fas fa-users"></i> Capacité: <?= $event['capacity'] ?> places</span>
            </div>
        </div>
        
        <div class="participants-stats">
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <div class="stat-value"><?= $total_participants ?></div>
                <div class="stat-label">Participants inscrits</div>
            </div>
            
            <div class="stat-card">
                <i class="fas fa-percentage"></i>
                <div class="stat-value"><?= $event['capacity'] > 0 ? round(($total_participants / $event['capacity']) * 100) : 0 ?>%</div>
                <div class="stat-label">Taux de remplissage</div>
            </div>
            
            <div class="stat-card">
                <i class="fas fa-clock"></i>
                <div class="stat-value"><?= max(0, $event['capacity'] - $total_participants) ?></div>
                <div class="stat-label">Places restantes</div>
            </div>
        </div>
        
        <div class="participants-table">
            <div class="table-header">
                <h2><i class="fas fa-list"></i> Liste des participants</h2>
            </div>
            
            <?php if (empty($participants)): ?>
                <div class="empty-state">
                    <i class="fas fa-user-slash"></i>
                    <h3>Aucun participant pour le moment</h3>
                    <p>Les inscriptions apparaîtront ici dès qu'un client participera à votre initiative.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Participant</th>
                            <th>Message de motivation</th>
                            <th>Date d'inscription</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($participants as $participant): ?>
                            <tr>
                                <td>
                                    <div class="participant-cell">
                                        <div class="participant-avatar">
                                            <?php if (!empty($participant['photo_profil'])): ?>
                                                <img src="/integration/NovaLinkPeace/test/View/FrontOffice/uploads/<?= htmlspecialchars($participant['photo_profil']) ?>" alt="Photo">
                                            <?php else: ?>
                                                <?= strtoupper(substr($participant['nom_complet'] ?? $participant['full_name'] ?? 'C', 0, 1)) ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="participant-info">
                                            <div class="participant-name">
                                                <?= htmlspecialchars($participant['nom_complet'] ?? $participant['full_name'] ?? 'Client PeaceLink') ?>
                                            </div>
                                            <div class="participant-email">
                                                <?= htmlspecialchars($participant['client_email'] ?? $participant['email'] ?? 'N/A') ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="message-cell">
                                        <?= !empty($participant['message']) ? htmlspecialchars($participant['message']) : '<em style="color:#ccc;">Aucun message</em>' ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="date-cell">
                                        <i class="fas fa-calendar-check"></i>
                                        <?= date('d/m/Y à H:i', strtotime($participant['created_at'])) ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
