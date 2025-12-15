<!-- Notification badge pour les messages non lus (À inclure dans votre navbar) -->
<!-- Ce snippet vérifie automatiquement les messages non lus pour les clients -->

<style>
.messages-badge {
    position: relative;
    display: inline-block;
}

.messages-badge .badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #ff4444;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75em;
    font-weight: bold;
    border: 2px solid white;
}

.messages-badge .badge.hidden {
    display: none;
}

.nav-messages-link {
    color: inherit;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 15px;
    border-radius: 8px;
    transition: all 0.3s;
}

.nav-messages-link:hover {
    background: rgba(102, 126, 234, 0.1);
}
</style>

<?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'client'): ?>
    <!-- Bouton Messages avec badge (visible seulement pour les clients) -->
    <div class="messages-badge">
        <a href="test/Controller/MessageController.php?action=client_conversations" class="nav-messages-link">
            <i class="fas fa-envelope"></i>
            <span>Messages</span>
        </a>
        <span class="badge hidden" id="unreadBadge">0</span>
    </div>

    <script>
        // Vérifier les messages non lus toutes les 30 secondes
        function checkUnreadMessages() {
            fetch('test/Controller/MessageController.php?action=get_unread_count')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('unreadBadge');
                    if (data.count > 0) {
                        badge.textContent = data.count > 9 ? '9+' : data.count;
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                })
                .catch(error => console.error('Error checking messages:', error));
        }

        // Vérifier au chargement de la page
        checkUnreadMessages();

        // Vérifier périodiquement
        setInterval(checkUnreadMessages, 30000); // Toutes les 30 secondes
    </script>
<?php endif; ?>

<?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'expert'): ?>
    <!-- Liens pour les experts -->
    <a href="test/Controller/MessageController.php?action=expert_stories" class="nav-messages-link">
        <i class="fas fa-book-open"></i>
        <span>Histoires</span>
    </a>
    <a href="test/Controller/MessageController.php?action=expert_conversations" class="nav-messages-link">
        <i class="fas fa-comments"></i>
        <span>Conversations</span>
    </a>
<?php endif; ?>
