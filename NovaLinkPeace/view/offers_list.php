<?php include 'templates/header.php'; ?>

<div class="page-header">
    <div>
        <h1>Offres de Mission</h1>
        <?php if ($user_role === 'organisation'): ?>
            <p class="page-subtitle">Gérez vos offres et consultez les candidatures.</p>
        <?php else: ?>
            <p class="page-subtitle">Trouvez votre prochaine mission parmi nos opportunités.</p>
        <?php endif; ?>
    </div>
    
    <!-- Bouton Créer (Visible seulement pour Organisation) -->
    <?php if ($user_role === 'organisation'): ?>
        <div style="display: flex; gap: 10px;">
            <a href="index.php?action=create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Publier une offre
            </a>
            <?php if (isset($_GET['view']) && $_GET['view'] === 'all'): ?>
                <a href="index.php?action=list" class="btn btn-secondary">
                    <i class="fas fa-filter"></i> Mes offres uniquement
                </a>
            <?php else: ?>
                <a href="index.php?action=list&view=all" class="btn btn-secondary">
                    <i class="fas fa-globe"></i> Toutes les offres
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<div class="stories-grid">
    <?php if (empty($offers)): ?>
        <div style="grid-column: 1/-1; text-align: center; color: #888; padding: 40px;">
            <i class="fas fa-folder-open" style="font-size: 40px; margin-bottom: 10px;"></i>
            <p>Aucune offre disponible pour le moment.</p>
        </div>
    <?php else: ?>
        <?php foreach ($offers as $offer): ?>
            
            <?php 
                // Logique Places / Quota
                $max = $offer['max_candidates'];
                $current = $offer['current_count'];
                $is_full = $current >= $max;
                // Calcul pourcentage (max 100%)
                $percent = ($max > 0) ? min(100, ($current / $max) * 100) : 0;
                $color_bar = $is_full ? 'var(--rouge-alerte)' : 'var(--bleu-pastel)';
            ?>

            <div class="story-card">
                <div class="story-header">
                    <span><i class="fas fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($offer['created_at'])) ?></span>
                    
                    <?php if($is_full): ?>
                        <span style="background:#fee; color:var(--rouge-alerte); padding:2px 8px; border-radius:4px; font-size:11px; font-weight:bold;">COMPLET</span>
                    <?php else: ?>
                        <span class="status-badge active"><?= htmlspecialchars($offer['status']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="story-content">
                    <h3><?= htmlspecialchars($offer['title']) ?></h3>
                    
                    <!-- Afficher le nom de l'organisation -->
                    <?php if (isset($offer['nom_organisation'])): ?>
                        <p style="font-size: 13px; color: #888; margin-bottom: 10px;">
                            <i class="fas fa-building"></i> <?= htmlspecialchars($offer['nom_organisation']) ?>
                        </p>
                    <?php endif; ?>
                    
                    <!-- BARRE DE PROGRESSION (PLACES) -->
                    <div style="margin-bottom: 15px;">
                        <div style="display:flex; justify-content:space-between; font-size:12px; color:#666; margin-bottom:5px;">
                            <span>Candidatures : <strong><?= $current ?> / <?= $max ?></strong></span>
                            <span><?= $is_full ? 'Plus de place' : ($max - $current) . ' places restantes' ?></span>
                        </div>
                        <div style="width:100%; height:6px; background:#eee; border-radius:3px; overflow:hidden;">
                            <div style="width:<?= $percent ?>%; height:100%; background:<?= $color_bar ?>;"></div>
                        </div>
                    </div>

                    <p><?= nl2br(htmlspecialchars($offer['description'])) ?></p>
                </div>

                <div class="story-actions">
                    <?php if ($user_role === 'organisation'): ?>
                        <!-- Actions ORGANISATION -->
                        <a href="index.php?action=list_applications&offer_id=<?= $offer['id'] ?>" class="btn btn-primary" title="Voir les candidats" style="background-color: var(--violet-admin);">
                            <i class="fas fa-users"></i> Candidats (<?= $current ?>)
                        </a>
                        <a href="index.php?action=edit&id=<?= $offer['id'] ?>" class="btn btn-secondary" title="Modifier">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="index.php?action=delete&id=<?= $offer['id'] ?>" class="btn btn-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette offre ?');">
                            <i class="fas fa-trash"></i>
                        </a>
                    <?php else: ?>
                        <!-- Actions CLIENT -->
                        <?php if ($is_full): ?>
                            <button class="btn btn-secondary" disabled style="opacity: 0.6; cursor: not-allowed;">
                                <i class="fas fa-lock"></i> Complet
                            </button>
                        <?php else: ?>
                            <a href="index.php?action=apply&id=<?= $offer['id'] ?>" class="btn btn-success">
                                <i class="fas fa-paper-plane"></i> Postuler
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include 'templates/footer.php'; ?>