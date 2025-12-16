
<style>
.messages-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #ff4444;
    color: white;
    border-radius: 50%;
    min-width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75em;
    font-weight: bold;
    border: 2px solid white;
    padding: 2px 5px;
}

.messages-nav-item {
    display: flex;
    align-items: center;
}

.messages-link {
    position: relative;
    display: flex !important;
    align-items: center;
    gap: 5px;
}
</style>

<?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'client'): ?>
<script>
// Vérifier les messages non lus pour le client
function checkUnreadMessages() {
    fetch('/integration/test/Controller/MessageController.php?action=get_unread_count')
        .then(response => response.json())
        .then(data => {
            const link = document.getElementById('clientMessagesLink');
            const badge = document.getElementById('messagesBadge');
            
            if (data.count > 0) {
                // Afficher le lien et le badge
                link.style.display = 'flex';
                badge.style.display = 'flex';
                badge.textContent = data.count > 9 ? '9+' : data.count;
            } else {
                // Cacher le lien si aucun message
                link.style.display = 'none';
            }
        })
        .catch(error => console.error('Error checking messages:', error));
}

// Vérifier au chargement
checkUnreadMessages();

// Vérifier périodiquement (toutes les 30 secondes)
setInterval(checkUnreadMessages, 30000);
</script>
<?php endif; ?>
