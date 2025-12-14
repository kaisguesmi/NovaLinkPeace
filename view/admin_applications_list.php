<?php include 'templates/header.php'; ?>

<div class="page-header">
    <div>
        <!-- Titre dynamique si filtre actif -->
        <?php if(isset($filter_title) && $filter_title): ?>
            <h1>Candidats pour : "<?= htmlspecialchars($filter_title) ?>"</h1>
            <a href="index.php?action=list_applications" style="font-size:14px; color:var(--bleu-pastel); text-decoration:none;">
                <i class="fas fa-arrow-left"></i> Retour à toutes les candidatures
            </a>
        <?php else: ?>
            <h1>Toutes les Candidatures</h1>
            <p class="page-subtitle">Classées par pertinence (Score IA) et date.</p>
        <?php endif; ?>
    </div>
</div>

<div class="table-card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Candidat</th>
                    <th style="width:140px;">IA Score</th>
                    <th>IA Sentiment</th>
                    <th>Offre</th>
                    <th>Statut</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($applications)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: #888;">
                            <i class="fas fa-inbox" style="font-size: 40px; margin-bottom: 15px;"></i><br>
                            Aucune candidature trouvée.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($applications as $app): ?>
                        <tr>
                            <!-- 1. CANDIDAT -->
                            <td>
                                <div class="user-cell">
                                    <div class="user-cell-avatar">
                                        <?= strtoupper(mb_substr(htmlspecialchars($app['candidate_name']), 0, 1)) ?>
                                    </div>
                                    <div style="display:flex; flex-direction:column;">
                                        <span style="font-weight:600; color:var(--bleu-nuit);"><?= htmlspecialchars($app['candidate_name']) ?></span>
                                        <span style="font-size:11px; color:#999;">@<?= htmlspecialchars($app['client_nom'] ?? 'Client') ?></span>
                                        <a href="mailto:<?= htmlspecialchars($app['candidate_email']) ?>" style="font-size:12px; color:#888;">
                                            <?= htmlspecialchars($app['candidate_email']) ?>
                                        </a>
                                    </div>
                                </div>
                            </td>
                            
                            <!-- 2. IA SCORE (Barre) -->
                            <td>
                                <?php 
                                    $score = $app['score'] ?? 0;
                                    // Vert si > 80, Jaune si > 50, Rouge sinon
                                    $color = ($score >= 80) ? '#2ecc71' : (($score >= 50) ? '#f1c40f' : '#e74c3c');
                                ?>
                                <div style="display:flex; justify-content:space-between; font-size:11px; font-weight:bold; margin-bottom:3px; color:<?= $color ?>;">
                                    <span>Match</span>
                                    <span><?= $score ?>%</span>
                                </div>
                                <div style="width:100%; height:6px; background:#eee; border-radius:10px; overflow:hidden;">
                                    <div style="width:<?= $score ?>%; height:100%; background:<?= $color ?>; border-radius:10px;"></div>
                                </div>
                            </td>

                            <!-- 3. IA SENTIMENT (Badge) -->
                            <td>
                                <?php 
                                    $sent = $app['sentiment'] ?? 'Neutre';
                                    if ($sent === 'Confiant') {
                                        $icon = '🚀'; $badgeClass = 'expert'; // Vert (Expert)
                                    } elseif ($sent === 'Hésitant') {
                                        $icon = '😟'; $badgeClass = 'organisateur'; // Violet (Organisateur)
                                    } else {
                                        $icon = '😐'; $badgeClass = 'user'; // Gris
                                    }
                                ?>
                                <span class="role-badge <?= $badgeClass ?>">
                                    <?= $icon ?> <?= $sent ?>
                                </span>
                            </td>

                            <!-- 4. OFFRE -->
                            <td>
                                <span style="font-weight:600; font-size:13px; color:#444;">
                                    <?= htmlspecialchars($app['offer_title']) ?>
                                </span>
                            </td>
                            
                            <!-- 5. STATUT -->
                            <td>
                                <?php 
                                    $st = $app['status'];
                                    $stClass = ($st === 'acceptée') ? 'expert' : (($st === 'refusée') ? 'organisateur' : 'user');
                                ?>
                                <span class="role-badge <?= $stClass ?>">
                                    <?= ucfirst($st) ?>
                                </span>
                            </td>
                            
                            <!-- 6. ACTIONS -->
                            <td class="actions-cell" style="text-align: right;">
                                <?php if ($app['status'] === 'en_attente'): ?>
                                    <!-- Boutons visibles uniquement si en attente -->
                                    <a href="index.php?action=update_status&id=<?= $app['id'] ?>&status=acceptée" 
                                       class="action-btn success" title="Accepter et envoyer Email"
                                       onclick="return confirm('Confirmer l\'acceptation de ce candidat ?');">
                                        <i class="fas fa-check"></i>
                                    </a>
                                    
                                    <a href="index.php?action=update_status&id=<?= $app['id'] ?>&status=refusée" 
                                       class="action-btn danger" title="Refuser"
                                       onclick="return confirm('Refuser cette candidature ?');">
                                        <i class="fas fa-times"></i>
                                    </a>
                                <?php else: ?>
                                    <span style="font-size:12px; color:#aaa; margin-right:5px;">Traité</span>
                                <?php endif; ?>

                                <!-- Voir Motivation -->
                                <button class="action-btn" title="Lire la motivation" 
                                        onclick="alert('Motivation de <?= addslashes($app['candidate_name']) ?> :\n\n<?= addslashes(htmlspecialchars($app['motivation'])) ?>')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'templates/footer.php'; ?>