<?php
$config = require __DIR__ . '/../../../config/config.php';
$base = rtrim($config['app']['base_url'], '/');
?>

<div class="stories-page-container">
    <div class="page-header">
        <div>
            <h1>Stories</h1>
            <p class="page-subtitle">Partagez vos expériences et découvrez celles des autres</p>
        </div>
        <button class="btn-primary" id="toggle-post-form-btn" onclick="togglePostForm()">
            <i class="fa-solid fa-plus"></i> Add Story
        </button>
    </div>

<?php if (!empty($toastNotification)): ?>
    <?php
        $toastColor = ($toastNotification['type'] ?? 'success') === 'error'
            ? '#e74c3c'
            : 'var(--vert-doux)';
    ?>
    <div class="flash-banner" data-auto-dismiss="true" style="background-color: <?= $toastColor ?>; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
        <?= htmlspecialchars($toastNotification['message'] ?? '') ?>
    </div>
<?php endif; ?>

<!-- Create Post Form (Hidden by default) -->
<div class="post-create-card" id="post-create-form" style="display: none; margin-bottom: 30px;">
    <h3 style="margin-bottom: 20px; font-family: var(--font-titre); color: var(--bleu-nuit);">Créer un nouveau post</h3>
    <form action="<?= $base ?>/?controller=post&action=store" method="POST" class="post-form" id="post-inline-form">
        <input type="hidden" name="csrf_token" value="<?= $this->generateCsrfToken() ?>">
        <div class="form-group">
            <label for="title">Titre (optionnel)</label>
            <input type="text" id="title" name="title" class="form-control" placeholder="Ajoutez un titre...">
        </div>
        <div class="form-group">
            <label for="content">Contenu *</label>
            <textarea id="content" name="content" class="form-control" rows="4" placeholder="Quoi de neuf ?"></textarea>
        </div>
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-paper-plane"></i> Publier
            </button>
            <button type="button" class="btn-secondary" onclick="togglePostForm()">
                Annuler
            </button>
        </div>
    </form>
</div>

    <!-- Removed duplicate notification display -->

    <!-- Create Post Form (Hidden by default) -->
    <div class="post-create-card" id="post-create-form" style="display: none;">
        <h3>Créer un nouveau post</h3>
        <form action="<?= $base ?>/?controller=post&action=store" method="POST" class="post-form" id="post-inline-form">
            <input type="hidden" name="csrf_token" value="<?= $this->generateCsrfToken() ?>">
            <div class="form-group">
                <label for="title">Titre (optionnel)</label>
                <input type="text" id="title" name="title" class="form-control" placeholder="Ajoutez un titre...">
            </div>
            <div class="form-group">
                <label for="content">Contenu *</label>
                <textarea id="content" name="content" class="form-control" rows="4" placeholder="Quoi de neuf ?"></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-paper-plane"></i> Publier
                </button>
                <button type="button" class="btn-secondary" onclick="togglePostForm()">
                    Annuler
                </button>
            </div>
        </form>
    </div>

    <!-- Posts Feed -->
    <div class="posts-feed">
        <?php if (empty($posts)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-inbox"></i>
                <p>Aucun post pour le moment. Cliquez sur "Add Story" pour créer le premier !</p>
            </div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <?php include __DIR__ . '/../partials/post-card.php'; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
var baseUrl = '<?= $base ?>';

function togglePostForm() {
    const form = document.getElementById('post-create-form');
    const btn = document.getElementById('toggle-post-form-btn');
    
    if (form.style.display === 'none') {
        form.style.display = 'block';
        form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        btn.innerHTML = '<i class="fa-solid fa-times"></i> Annuler';
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-secondary');
    } else {
        form.style.display = 'none';
        btn.innerHTML = '<i class="fa-solid fa-plus"></i> Add Story';
        btn.classList.remove('btn-secondary');
        btn.classList.add('btn-primary');
        // Clear form
        document.querySelector('#post-create-form form').reset();
    }
}

function attachAutoDismiss(banner) {
    // Keep notification visible a bit longer for better readability (9s)
    setTimeout(function () {
        banner.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
        banner.style.opacity = '0';
        banner.style.transform = 'translateY(-8px)';
        setTimeout(function () {
            if (banner.parentNode) {
                banner.parentNode.removeChild(banner);
            }
        }, 450);
    }, 9000);
}

function showToast(message, type) {
    var color = type === 'error' ? '#e74c3c' : 'var(--vert-doux)';
    var banner = document.createElement('div');
    banner.className = 'flash-banner';
    banner.setAttribute('data-auto-dismiss', 'true');
    banner.style.backgroundColor = color;
    banner.style.color = 'white';
    banner.style.padding = '15px';
    banner.style.borderRadius = '8px';
    banner.style.marginBottom = '20px';
    banner.style.fontWeight = '500';
    banner.style.boxShadow = '0 4px 12px rgba(0,0,0,0.08)';
    banner.textContent = message;

    var header = document.querySelector('.page-header');
    if (header && header.parentNode) {
        header.parentNode.insertBefore(banner, header.nextSibling);
    } else if (document.body.firstChild) {
        document.body.insertBefore(banner, document.body.firstChild);
    } else {
        document.body.appendChild(banner);
    }

    attachAutoDismiss(banner);
}

function pollNotifications() {
    if (!window.fetch) {
        return;
    }

    fetch(baseUrl + '/?controller=histoire&action=pollNotifications', {
        credentials: 'same-origin'
    })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            if (data && data.toast && data.toast.message) {
                showToast(data.toast.message, data.toast.type || 'success');
            }
        })
        .catch(function () {});
}

document.addEventListener('DOMContentLoaded', function () {
    var banners = document.querySelectorAll('.flash-banner[data-auto-dismiss="true"]');
    banners.forEach(function (banner) {
        attachAutoDismiss(banner);
    });

    setInterval(pollNotifications, 6000);
});

// Post interaction functions
function toggleReaction(postId, type) {
    const formData = new FormData();
    formData.append('post_id', postId);
    formData.append('type', type);
    
    // Get CSRF token from meta tag or form
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || 
                      document.querySelector('input[name="csrf_token"]')?.value || '';
    
    if (csrfToken) {
        formData.append('csrf_token', csrfToken);
    }

    fetch(baseUrl + '/?controller=post&action=react', {
        method: 'POST',
        headers: {
            'X-CSRF-Token': csrfToken
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Erreur lors de la réaction');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erreur lors de la réaction');
    });
}

function toggleComments(postId) {
    const commentsSection = document.getElementById('comments-' + postId);
    if (commentsSection) {
        commentsSection.style.display = commentsSection.style.display === 'none' ? 'block' : 'none';
    }
}

function submitComment(event, postId) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    formData.append('post_id', postId);
    
    // Get CSRF token
    const csrfToken = document.querySelector('input[name="csrf_token"]')?.value || '';
    if (csrfToken) {
        formData.append('csrf_token', csrfToken);
    }

    fetch(baseUrl + '/?controller=comment&action=store', {
        method: 'POST',
        headers: {
            'X-CSRF-Token': csrfToken
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Erreur lors de l\'ajout du commentaire');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erreur lors de l\'ajout du commentaire');
    });
}

function deletePost(postId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce post ?')) {
        window.location.href = baseUrl + '/?controller=post&action=delete&id=' + postId;
    }
}
</script>
