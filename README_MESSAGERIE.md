# 📨 Système de Messagerie Expert-Client

## 🎯 Fonctionnalité

Système de messagerie privée permettant aux **experts** de contacter les **clients** qui publient des histoires.

---

## 📋 Installation

### 1. Exécuter le script SQL

```bash
# Importer le fichier dans votre base de données
mysql -u root peacelink < sql/add_messages_table.sql
```

Ou via phpMyAdmin :
- Ouvrir `sql/add_messages_table.sql`
- Copier le contenu
- Exécuter dans l'onglet SQL

### 2. Tables créées

- **`message_prive`** : Stocke tous les messages
- **`conversation`** : Gère les conversations uniques entre expert et client

---

## 🔧 Comment ça marche ?

### **Pour les EXPERTS** 👨‍💼

#### 1. **Voir les histoires des clients**
URL : `test/Controller/MessageController.php?action=expert_stories`

- L'expert voit toutes les histoires publiées par les clients
- Chaque histoire affiche :
  - Nom de l'auteur
  - Date de publication
  - Titre et extrait du contenu
  - **Bouton "Contacter"**

#### 2. **Envoyer un message**
- Cliquer sur "Contacter" ouvre un modal
- L'expert écrit son message
- Le message est envoyé avec référence à l'histoire (optionnel)

#### 3. **Voir ses conversations**
URL : `test/Controller/MessageController.php?action=expert_conversations`

- Liste de tous les clients contactés
- Affiche le dernier message de chaque conversation
- Cliquer pour voir la conversation complète

---

### **Pour les CLIENTS** 👤

#### 1. **Badge de notification automatique**

Le badge **n'apparaît que si l'expert a envoyé un message** :

```php
<!-- Dans votre navbar, inclure : -->
<?php include 'test/View/includes/messages_navbar.php'; ?>
```

**Comportement :**
- ✅ **Si messages reçus** → Badge rouge avec le nombre de messages non lus
- ❌ **Si aucun message** → Le bouton ne s'affiche PAS

#### 2. **Voir ses messages**
URL : `test/Controller/MessageController.php?action=client_conversations`

- Liste des experts qui ont contacté le client
- Nombre de messages non lus par conversation
- Cliquer pour ouvrir la conversation

#### 3. **Répondre à un expert**
- Interface de chat en temps réel
- Historique complet de la conversation
- Possibilité de répondre directement

---

## 📁 Structure des fichiers créés

```
integration/
├── sql/
│   └── add_messages_table.sql          # Script de création des tables
│
├── test/
│   ├── Model/
│   │   └── Message.php                 # Modèle de gestion des messages
│   │
│   ├── Controller/
│   │   └── MessageController.php       # Contrôleur principal
│   │
│   └── View/
│       ├── FrontOffice/
│       │   ├── expert_stories.php           # Expert : voir histoires
│       │   ├── expert_conversations.php     # Expert : ses conversations
│       │   ├── client_messages.php          # Client : liste conversations
│       │   └── conversation.php             # Chat entre expert et client
│       │
│       └── includes/
│           └── messages_navbar.php          # Badge notification navbar
```

---

## 🔗 Intégration dans votre navbar

### **Exemple d'intégration**

```php
<!-- Dans votre fichier navbar (ex: header.php) -->
<nav class="navbar">
    <div class="nav-left">
        <a href="index.php">Accueil</a>
        <a href="histoires.php">Histoires</a>
    </div>
    
    <div class="nav-right">
        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- INTÉGRATION DU SYSTÈME DE MESSAGES -->
            <?php include 'test/View/includes/messages_navbar.php'; ?>
            
            <a href="profile.php">Profil</a>
            <a href="logout.php">Déconnexion</a>
        <?php else: ?>
            <a href="login.php">Connexion</a>
        <?php endif; ?>
    </div>
</nav>
```

---

## 🎨 Fonctionnalités détaillées

### **Badge de notification intelligent**

```javascript
// Vérifie automatiquement les nouveaux messages toutes les 30 secondes
// Affiche un badge rouge seulement si messages non lus > 0
// Format : "1", "2", ... "9+"
```

**Comportement selon le rôle :**

| Rôle   | Badge affiché ?                                  | Fonctionnalité                                |
|--------|--------------------------------------------------|-----------------------------------------------|
| Client | ✅ OUI (si messages reçus)                      | Badge rouge + lien vers conversations         |
| Client | ❌ NON (si aucun message)                       | Bouton invisible                              |
| Expert | 🔵 Liens visibles en permanence                 | "Histoires" + "Conversations"                 |

---

## 🛡️ Sécurité

✅ **Vérifications de rôle** : Seuls les experts peuvent initier un contact  
✅ **Validation côté serveur** : Tous les formulaires sont validés  
✅ **Protection XSS** : `htmlspecialchars()` sur toutes les sorties  
✅ **Sessions sécurisées** : Vérification de l'authentification  
✅ **SQL préparé** : Protection contre les injections SQL

---

## 📊 Flux utilisateur

### **Scénario typique**

1. **Client** publie une histoire → "Mon expérience en tant que bénévole"
2. **Expert** voit l'histoire dans sa liste
3. **Expert** clique sur "Contacter" et envoie un message
4. **Client** reçoit une notification (badge rouge sur navbar)
5. **Client** clique sur "Messages" et voit la conversation
6. **Client** répond à l'expert
7. **Expert** et **Client** peuvent discuter en continu

---

## 🔄 Actions disponibles

### **Routes du MessageController**

| Action                 | Accès    | Description                                      |
|------------------------|----------|--------------------------------------------------|
| `expert_stories`       | Expert   | Liste toutes les histoires des clients          |
| `send_message`         | Expert   | Envoyer un message à un client                   |
| `expert_conversations` | Expert   | Liste des conversations de l'expert              |
| `client_conversations` | Client   | Liste des conversations du client                |
| `view_conversation`    | Les deux | Afficher une conversation complète               |
| `get_unread_count`     | Client   | (AJAX) Nombre de messages non lus                |

---

## 🎯 Points clés

✅ **Le client ne voit le bouton Messages QUE si un expert l'a contacté**  
✅ **L'expert peut parcourir toutes les histoires librement**  
✅ **Badge de notification en temps réel (AJAX)**  
✅ **Interface de chat moderne et responsive**  
✅ **Référence à l'histoire qui a motivé le contact**

---

## 🐛 Dépannage

### Le badge ne s'affiche pas pour le client

**Solution :**
1. Vérifier que `messages_navbar.php` est bien inclus dans votre navbar
2. Vérifier que la session contient `$_SESSION['role'] === 'client'`
3. Vérifier qu'un expert a effectivement envoyé un message

### Les messages ne s'envoient pas

**Solution :**
1. Vérifier que les tables SQL sont bien créées
2. Vérifier les permissions de la base de données
3. Consulter les logs d'erreur PHP (`error_log`)

---

## 🚀 Prochaines améliorations possibles

- 🔔 Notifications push en temps réel (WebSocket)
- 📎 Pièces jointes dans les messages
- 🔍 Recherche dans les conversations
- 📊 Statistiques pour les experts (taux de réponse, etc.)
- ⭐ Système de notation expert-client

---

## 📞 Support

Pour toute question, vérifiez :
- Les logs PHP : `tail -f /path/to/php_error.log`
- Les requêtes SQL : Activer les logs MySQL
- La console navigateur : F12 → Console

---

**Créé le :** 15 décembre 2025  
**Version :** 1.0  
**Auteur :** PeaceLink Integration Team
