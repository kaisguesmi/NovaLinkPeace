<?php
// Base URL and assets are now handled by the Controller class
$base = $this->baseUrl();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeaceLink - Stories & Initiatives</title>
    <link rel="stylesheet" href="/NovaLinkPeace-Jasser-Ouni-task/public/assets/css/front.css">

    <!-- Inline styles for Forum Assistant widget to ensure it always floats bottom-right -->
    <style>
    #assistant-widget-container {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1200;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }

    #assistant-toggle-btn {
        background: #1f2937;
        color: #ffffff;
        border: none;
        border-radius: 999px;
        padding: 10px 18px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.35);
        transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
    }

    #assistant-toggle-btn:hover {
        background: #111827;
        transform: translateY(-1px);
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.5);
    }

    #assistant-chat-popup {
        position: fixed;
        bottom: 80px;
        right: 20px;
        width: 340px;
        max-width: 95vw;
        height: 440px;
        max-height: 80vh;
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.45);
        display: none;
        flex-direction: column;
        overflow: hidden;
    }

    #assistant-chat-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        background: linear-gradient(135deg, #111827, #1f2937);
        color: #ffffff;
    }

    #assistant-chat-header span {
        font-size: 14px;
        font-weight: 600;
    }

    #assistant-close-btn {
        background: transparent;
        border: none;
        color: #9ca3af;
        font-size: 18px;
        line-height: 1;
        cursor: pointer;
        padding: 4px 6px;
        border-radius: 999px;
        transition: background 0.15s ease, color 0.15s ease;
    }

    #assistant-close-btn:hover {
        background: rgba(55, 65, 81, 0.7);
        color: #f9fafb;
    }

    #assistant-chat-messages {
        flex: 1;
        padding: 10px 12px;
        background: #f3f4f6;
        overflow-y: auto;
        scroll-behavior: smooth;
    }

    .assistant-message {
        max-width: 85%;
        margin-bottom: 8px;
        padding: 8px 10px;
        border-radius: 12px;
        font-size: 13px;
        line-height: 1.35;
        white-space: pre-wrap;
        word-wrap: break-word;
    }

    .assistant-message.from-user {
        margin-left: auto;
        background: #2563eb;
        color: #ffffff;
        border-bottom-right-radius: 4px;
    }

    .assistant-message.from-bot {
        margin-right: auto;
        background: #ffffff;
        color: #111827;
        border-bottom-left-radius: 4px;
        border: 1px solid #e5e7eb;
    }

    #assistant-chat-input-area {
        display: flex;
        align-items: flex-end;
        gap: 8px;
        padding: 8px 10px;
        border-top: 1px solid #e5e7eb;
        background: #ffffff;
    }

    #assistant-user-input {
        flex: 1;
        resize: none;
        border-radius: 10px;
        border: 1px solid #d1d5db;
        padding: 8px 10px;
        font-size: 13px;
        max-height: 90px;
        outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    #assistant-user-input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 1px rgba(37, 99, 235, 0.35);
    }

    #assistant-send-btn {
        background: #2563eb;
        color: #ffffff;
        border: none;
        border-radius: 10px;
        padding: 8px 12px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.15s ease, transform 0.15s ease, box-shadow 0.15s ease;
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.4);
    }

    #assistant-send-btn:hover {
        background: #1d4ed8;
        transform: translateY(-1px);
        box-shadow: 0 7px 16px rgba(37, 99, 235, 0.55);
    }

    #assistant-send-btn:disabled {
        background: #9ca3af;
        cursor: default;
        box-shadow: none;
    }

    #assistant-chat-messages::-webkit-scrollbar {
        width: 6px;
    }

    #assistant-chat-messages::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 999px;
    }
    </style>
</head>
<body>
    <header class="main-navbar">
        <div class="navbar-container">
            <div class="navbar-brand">
                <a href="<?= $this->baseUrl('?controller=home&action=index#home') ?>" id="logo-link">
                    <img src="<?= $this->asset('images/mon-logo.png') ?>" alt="Logo PeaceLink" class="logo-img">
                    <span class="site-name">PeaceLink</span>
                </a>
            </div>
            <nav class="navbar-links">
                <ul>
                    <li><a href="<?= $this->baseUrl('?controller=home&action=index#home') ?>" class="nav-link active" data-section="home">Home</a></li>
                    <li><a href="<?= $this->baseUrl('?controller=home&action=index#stories') ?>" class="nav-link" data-section="stories">Stories</a></li>
                    <li><a href="<?= $this->baseUrl('?controller=home&action=index#initiatives') ?>" class="nav-link" data-section="initiatives">Initiatives</a></li>
                    <li><a href="<?= $this->baseUrl('?controller=home&action=index#participations') ?>" class="nav-link" data-section="participations">Participations</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <?= $content ?>
    </main>

    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-column footer-about">
                <div class="footer-brand">
                    <img src="<?php echo $this->asset('images/mon-logo.png'); ?>" alt="PeaceLink Logo" class="footer-logo-img">
                    <span class="footer-site-name">PeaceLink</span>
                </div>
                <p class="footer-description">Building peace through community stories and local initiatives.</p>
            </div>
            <div class="footer-column footer-links">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="<?php echo $this->baseUrl('?controller=home&action=index#about'); ?>">About Us</a></li>
                    <li><a href="<?php echo $this->baseUrl('?controller=home&action=index#how-it-works'); ?>">How It Works</a></li>
                    <li><a href="<?php echo $this->baseUrl('?controller=home&action=guidelines'); ?>">Community Guidelines</a></li>
                    <li><a href="<?php echo $this->baseUrl('?controller=home&action=index#contact'); ?>">Contact</a></li>
                </ul>
            </div>
            <div class="footer-column footer-social">
                <h4>Connect With Us</h4>
                <div class="social-icons">
                    <a href="https://twitter.com/peacelink" target="_blank" rel="noopener noreferrer" aria-label="Twitter">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path></svg>
                    </a>
                    <a href="https://facebook.com/peacelink" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
                    </a>
                    <a href="#" aria-label="Instagram">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
                    </a>
                    <a href="#" aria-label="LinkedIn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg>
                    </a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© <?= date('Y') ?> PeaceLink. Tous droits réservés.</p>
        </div>
    </footer>

    <!-- Forum Assistant Chat Widget -->
    <div id="assistant-widget-container">
        <button id="assistant-toggle-btn" type="button">Assistant</button>
        <div id="assistant-chat-popup">
            <div id="assistant-chat-header">
                <span>Forum Assistant</span>
                <button id="assistant-close-btn" type="button">&times;</button>
            </div>
            <div id="assistant-chat-messages"></div>
            <div id="assistant-chat-input-area">
                <textarea id="assistant-user-input" rows="2" placeholder="Ask how to use the forum..."></textarea>
                <button id="assistant-send-btn" type="button">Send</button>
            </div>
        </div>
    </div>

    <script src="<?= $base ?>/assets/js/smooth-scroll.js"></script>
    <script src="<?= $base ?>/assets/js/validation.js"></script>
    <script>
    var assistantBaseUrl = '<?= $base ?>';
    document.addEventListener('DOMContentLoaded', function () {
        var toggleBtn = document.getElementById('assistant-toggle-btn');
        var chatPopup = document.getElementById('assistant-chat-popup');
        var closeBtn = document.getElementById('assistant-close-btn');
        var messagesContainer = document.getElementById('assistant-chat-messages');
        var userInput = document.getElementById('assistant-user-input');
        var sendBtn = document.getElementById('assistant-send-btn');

        function toggleChat(open) {
            if (open === true) {
                chatPopup.style.display = 'flex';
            } else if (open === false) {
                chatPopup.style.display = 'none';
            } else {
                chatPopup.style.display = (chatPopup.style.display === 'flex') ? 'none' : 'flex';
            }
            if (chatPopup.style.display === 'flex') {
                userInput.focus();
            }
        }

        function appendMessage(text, sender) {
            var msg = document.createElement('div');
            msg.classList.add('assistant-message');
            if (sender === 'user') {
                msg.classList.add('from-user');
            } else {
                msg.classList.add('from-bot');
            }
            msg.textContent = text;
            messagesContainer.appendChild(msg);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
            return msg;
        }

        function sendMessage() {
            var message = userInput.value.trim();
            if (!message) {
                return;
            }

            appendMessage(message, 'user');
            userInput.value = '';
            userInput.style.height = 'auto';

            var typingMsg = appendMessage('Assistant is typing...', 'bot');

            sendBtn.disabled = true;

            fetch(assistantBaseUrl + '/chatbot.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: 'message=' + encodeURIComponent(message)
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(function (data) {
                    messagesContainer.removeChild(typingMsg);

                    var reply = (data && data.reply) ? data.reply : 'Sorry, I could not get a response right now.';
                    appendMessage(reply, 'bot');
                })
                .catch(function () {
                    messagesContainer.removeChild(typingMsg);
                    appendMessage('Sorry, something went wrong contacting the assistant.', 'bot');
                })
                .finally(function () {
                    sendBtn.disabled = false;
                });
        }

        userInput.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 90) + 'px';
        });

        userInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        toggleBtn.addEventListener('click', function () {
            toggleChat();
        });

        closeBtn.addEventListener('click', function () {
            toggleChat(false);
        });

        sendBtn.addEventListener('click', function () {
            sendMessage();
        });
    });
    </script>
</body>
</html>

