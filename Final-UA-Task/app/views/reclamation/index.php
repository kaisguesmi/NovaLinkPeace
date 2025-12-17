<section class="table-card">
    <h3>Signalements reçus</h3>
    <div class="stats-grid" id="reclamation-stats" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:12px;"></div>
    <form method="get" action="" class="filters">
        <input type="hidden" name="controller" value="reclamation">
        <input type="hidden" name="action" value="index">
        <label>Statut
            <select name="statut">
                <option value="">Tous</option>
                <?php $statuses = ['nouvelle','en_cours','resolue','acceptee','refusee']; ?>
                <?php foreach ($statuses as $s): ?>
                    <option value="<?= $s ?>" <?= ($statut ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Recherche
            <input type="text" name="q" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="texte ou titre">
        </label>
        <label>Tri
            <select name="sort">
                <option value="recent" <?= ($sort ?? '') === 'recent' ? 'selected' : '' ?>>Plus récents</option>
                <option value="oldest" <?= ($sort ?? '') === 'oldest' ? 'selected' : '' ?>>Plus anciens</option>
                <option value="score" <?= ($sort ?? '') === 'score' ? 'selected' : '' ?>>Score AI</option>
            </select>
        </label>
        <button class="btn-hero-secondary" type="submit">Filtrer</button>
        <a class="btn-hero-primary" href="?controller=reclamation&action=export">Exporter CSV</a>
        <a class="btn-hero-secondary" href="?controller=reclamation&action=exportExcel">Exporter Excel</a>
    </form>
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Auteur</th>
                <th>Statut</th>
                <th>Objet</th>
                <th>AI score</th>
                <th>Analyse AI</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reclamations as $reclamation): ?>
                <tr>
                    <td>#<?= $reclamation['id_reclamation'] ?></td>
                    <td><?= htmlspecialchars($reclamation['auteur_email']) ?></td>
                    <td><?= htmlspecialchars($reclamation['statut']) ?></td>
                    <td><?= htmlspecialchars($reclamation['histoire_titre'] ?? $reclamation['commentaire_contenu'] ?? 'N/A') ?></td>
                    <td><?= $reclamation['ai_score'] !== null ? htmlspecialchars(number_format((float)$reclamation['ai_score'], 1)) . ' / 100' : 'N/A' ?></td>
                    <td><?= htmlspecialchars($reclamation['ai_analysis'] ?? 'N/A') ?></td>
                    <td>
                        <a class="btn-hero-secondary" href="?controller=reclamation&action=edit&id=<?= $reclamation['id_reclamation'] ?>">Traiter</a>
                        <a class="btn-danger" href="?controller=reclamation&action=delete&id=<?= $reclamation['id_reclamation'] ?>">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (($pages ?? 1) > 1): ?>
        <div class="pagination">
            <?php for ($p = 1; $p <= $pages; $p++): ?>
                <a class="<?= $p === ($page ?? 1) ? 'active' : '' ?>" href="?controller=reclamation&action=index&page=<?= $p ?>&statut=<?= urlencode($statut ?? '') ?>">Page <?= $p ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</section>

<script>
(function(){
    const statsEl = document.getElementById('reclamation-stats');
    if (!statsEl) return;

    // Inject Chart.js
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
    script.onload = fetchStats;
    document.head.appendChild(script);

    fetch('?controller=reclamation&action=stats')
        .then(r => r.json())
        .then(data => renderStats(data))
        .catch(() => statsEl.innerHTML = '<div class="alert">Stats indisponibles.</div>');

    function renderStats(data){
        const cards = [];
        if (data.by_status) {
            const id = 'chart-status';
            cards.push(card('Par statut', `<canvas id="${id}" height="140"></canvas>`));
            setTimeout(() => drawPie(id, data.by_status, 'statut'), 0);
        }
        if (data.by_category) {
            const id = 'chart-category';
            cards.push(card('Par catégorie', `<canvas id="${id}" height="140"></canvas>`));
            setTimeout(() => drawBar(id, data.by_category, 'libelle'), 0);
        }
        if (data.by_day) {
            const id = 'chart-day';
            cards.push(card('14 derniers jours', `<canvas id="${id}" height="160"></canvas>`));
            setTimeout(() => drawLine(id, data.by_day, 'day'), 0);
        }
        statsEl.innerHTML = cards.join('');
    }

    function card(title, body){
        return `<div class="stat-card" style="border:1px solid #e0e0e0;border-radius:8px;padding:12px;background:#fff;">
            <div style="font-weight:600;margin-bottom:6px;">${title}</div>
            ${body}
        </div>`;
    }

    function list(items, labelKey){
        if (!items || items.length === 0) return '<div>Aucune donnée</div>';
        return '<ul style="margin:0;padding-left:16px;">' + items.map(it => {
            const label = it[labelKey] ?? 'N/A';
            const cnt = it.cnt ?? 0;
            return `<li>${label}: ${cnt}</li>`;
        }).join('') + '</ul>';
    }

    function drawPie(id, items, labelKey){
        const ctx = document.getElementById(id);
        if (!ctx || !window.Chart) return;
        const labels = items.map(it => it[labelKey] ?? 'N/A');
        const data = items.map(it => it.cnt ?? 0);
        new Chart(ctx, {
            type: 'doughnut',
            data: { labels, datasets: [{ data, backgroundColor: ['#1e88e5','#27ae60','#f39c12','#e74c3c','#9b59b6','#2d3436'] }] },
            options: { plugins: { legend: { position: 'bottom' } } }
        });
    }

    function drawBar(id, items, labelKey){
        const ctx = document.getElementById(id);
        if (!ctx || !window.Chart) return;
        const labels = items.map(it => it[labelKey] ?? 'N/A');
        const data = items.map(it => it.cnt ?? 0);
        new Chart(ctx, {
            type: 'bar',
            data: { labels, datasets: [{ data, backgroundColor: '#5dade2' }] },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    }

    function drawLine(id, items, labelKey){
        const ctx = document.getElementById(id);
        if (!ctx || !window.Chart) return;
        const labels = items.map(it => it[labelKey] ?? '');
        const data = items.map(it => it.cnt ?? 0);
        new Chart(ctx, {
            type: 'line',
            data: { labels, datasets: [{ data, borderColor: '#1e88e5', fill: false, tension: 0.2 }] },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    }

    function fetchStats(){
        // Chart.js loaded via onload above
    }
})();
</script>

