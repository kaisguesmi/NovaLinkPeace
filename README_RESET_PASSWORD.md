# 🔒 Système de Réinitialisation de Mot de Passe - PeaceLink

## 📋 Vue d'ensemble

Système complet et professionnel de réinitialisation de mot de passe avec email automatique et interface utilisateur moderne.

---

## ✨ Fonctionnalités

### 1. **Email Professionnel** 📧
- Design moderne avec dégradé bleu/vert PeaceLink
- Icône 🔐 et bouton call-to-action attractif
- Informations de sécurité claires :
  - Lien valide 1 heure
  - Usage unique
  - Désactivation automatique
- Footer professionnel avec copyright
- Compatible tous clients email (Gmail, Outlook, etc.)

### 2. **Page "Mot de passe oublié"** 🔑
**Fichier :** `NovaLinkPeace/test/View/FrontOffice/forgot_password.php`

#### Caractéristiques :
- Design moderne avec animations
- Icône animée (effet pulse)
- Validation email en temps réel
- Messages de succès/erreur élégants
- Responsive (mobile-friendly)

#### Améliorations visuelles :
- Dégradé de fond bleu → vert
- Card avec ombre et border-radius
- Animation slide-in au chargement
- Effet shake sur erreur

### 3. **Page de réinitialisation** 🔐
**Fichier :** `NovaLinkPeace/test/View/FrontOffice/reset_password.php`

#### Caractéristiques :
- **Indicateur de force du mot de passe** (nouveau !)
  - Faible (rouge) : < 6 caractères
  - Moyen (orange) : 6-9 caractères
  - Fort (vert) : 10+ caractères
- Validation en temps réel
- Confirmation du mot de passe
- Messages d'erreur contextuels
- Animation sur les erreurs

#### Sécurité :
- Token unique dans l'URL
- Vérification de validité du token
- Hash sécurisé du mot de passe
- Protection contre CSRF

---

## 🛠️ Architecture Technique

### EmailService.php
**Fichier :** `model/EmailService.php`

```php
EmailService::sendPasswordResetEmail($email, $userName, $resetLink)
```

**Paramètres :**
- `$email` : Email du destinataire
- `$userName` : Nom de l'utilisateur
- `$resetLink` : URL complète avec token

**Retour :** `bool` (true si envoyé)

### UtilisateurController.php
**Fichier :** `NovaLinkPeace/test/Controller/UtilisateurController.php`

#### Fonction : `handleForgotPasswordRequest()`
1. Vérifie si l'email existe
2. Génère un token sécurisé (64 caractères hex)
3. Enregistre le token en base
4. Envoie l'email professionnel
5. Affiche un message de confirmation

#### Fonction : `handleResetPasswordSubmit()`
1. Vérifie la validité du token
2. Compare les mots de passe
3. Hash le nouveau mot de passe
4. Met à jour en base
5. Redirige vers login avec succès

---

## 📊 Base de données

### Table : `Utilisateur`
Colonnes utilisées :
- `reset_token` : Token de réinitialisation (VARCHAR 255)
- `reset_expires` : Date d'expiration du token (DATETIME)
- `mot_de_passe` : Hash du mot de passe (VARCHAR 255)

---

## 🎨 Design & UX

### Palette de couleurs
```css
--bleu-pastel: #5dade2
--vert-doux: #7bd389
--blanc-pur: #ffffff
--gris-fonce: #2c3e50
--rouge-erreur: #e74c3c
--vert-succes: #27ae60
```

### Animations
- **slideIn** : Apparition de la card (0.5s)
- **pulse** : Animation de l'icône (2s loop)
- **shake** : Secousse sur erreur (0.5s)
- **fadeIn** : Apparition des messages (0.5s)

### Responsive
- Breakpoint : 480px
- Adaptation automatique sur mobile
- Touch-friendly (boutons larges)

---

## 🔐 Sécurité

### Mesures implémentées
1. **Token cryptographique** : `bin2hex(random_bytes(32))` = 64 caractères
2. **Expiration** : 1 heure maximum
3. **Usage unique** : Token supprimé après utilisation
4. **Hash mot de passe** : `password_hash()` avec BCRYPT
5. **Validation** : 
   - Email : Regex + vérification existence
   - Mot de passe : Minimum 6 caractères
   - Confirmation : Comparaison stricte
6. **Protection CSRF** : Token dans formulaire hidden
7. **Sanitization** : `htmlspecialchars()` sur toutes les sorties

### Pas de fuite d'information
- Message générique si email inexistant
- Pas de différence entre "email existe" ou "email inexistant"

---

## 🚀 Utilisation

### 1. Tester l'aperçu de l'email
```
http://localhost/integration/test_reset_email.php
```

### 2. Demander une réinitialisation
```
http://localhost/integration/NovaLinkPeace/test/View/FrontOffice/forgot_password.php
```

### 3. Flux complet
1. Utilisateur clique "Mot de passe oublié ?" sur login.php
2. Entre son email
3. Reçoit un email professionnel
4. Clique sur le lien dans l'email
5. Entre un nouveau mot de passe
6. Voit l'indicateur de force
7. Confirme le mot de passe
8. Redirigé vers login avec message de succès

---

## 📝 Configuration SMTP (Production)

Pour envoyer de vrais emails, configurez XAMPP :

### 1. Fichier `php.ini`
```ini
[mail function]
SMTP = smtp.gmail.com
smtp_port = 587
sendmail_from = votre-email@gmail.com
sendmail_path = "\"C:\xampp\sendmail\sendmail.exe\" -t"
```

### 2. Fichier `sendmail.ini`
```ini
[sendmail]
smtp_server=smtp.gmail.com
smtp_port=587
auth_username=votre-email@gmail.com
auth_password=votre-mot-de-passe-application
force_sender=votre-email@gmail.com
```

### 3. Gmail : Mot de passe d'application
1. Activer la validation en 2 étapes
2. Générer un mot de passe d'application
3. Utiliser ce mot de passe dans `sendmail.ini`

---

## 🎯 Avantages du système

### Pour l'utilisateur
- ✅ Processus simple et rapide
- ✅ Email professionnel et rassurant
- ✅ Indicateur de force du mot de passe
- ✅ Messages d'erreur clairs
- ✅ Design moderne et agréable

### Pour l'administrateur
- ✅ Code maintenable et modulaire
- ✅ Sécurité renforcée
- ✅ Logs et traçabilité
- ✅ Réutilisable (EmailService)
- ✅ Conforme aux bonnes pratiques

---

## 📦 Fichiers modifiés

1. **model/EmailService.php**
   - Ajout : `sendPasswordResetEmail()`

2. **NovaLinkPeace/test/Controller/UtilisateurController.php**
   - Modifié : `handleForgotPasswordRequest()`
   - Utilise maintenant EmailService

3. **NovaLinkPeace/test/View/FrontOffice/forgot_password.php**
   - Design moderne amélioré
   - Animations et effets visuels
   - Validation améliorée

4. **NovaLinkPeace/test/View/FrontOffice/reset_password.php**
   - Design moderne amélioré
   - Indicateur de force du mot de passe
   - Animations et effets visuels

5. **test_reset_email.php** (nouveau)
   - Aperçu de l'email sans envoi

---

## 🐛 Dépannage

### L'email ne s'envoie pas
- Vérifier la configuration SMTP dans `php.ini`
- Vérifier `sendmail.ini`
- Tester avec `test_reset_email.php`
- Vérifier les logs : `C:\xampp\sendmail\sendmail.log`

### Le lien ne fonctionne pas
- Vérifier que le token est bien dans l'URL
- Vérifier l'expiration (1 heure max)
- Vérifier que le token n'a pas déjà été utilisé

### Erreur "Token invalide"
- Le token a expiré (> 1 heure)
- Le token a déjà été utilisé
- Le token n'existe pas en base

---

## 📚 Documentation API

### EmailService::sendPasswordResetEmail()

```php
/**
 * Envoie un email professionnel de réinitialisation
 * 
 * @param string $to        Email destinataire
 * @param string $userName  Nom de l'utilisateur
 * @param string $resetLink URL complète avec token
 * @return bool             True si envoyé, false sinon
 */
public static function sendPasswordResetEmail($to, $userName, $resetLink)
```

**Exemple :**
```php
$resetLink = "http://localhost/integration/NovaLinkPeace/test/View/FrontOffice/reset_password.php?token=abc123";
$sent = EmailService::sendPasswordResetEmail(
    'user@example.com',
    'Jean Dupont',
    $resetLink
);

if ($sent) {
    echo "Email envoyé !";
}
```

---

## ✅ Tests effectués

- [x] Affichage de la page "Mot de passe oublié"
- [x] Validation email (format incorrect)
- [x] Email avec compte existant
- [x] Email avec compte inexistant (message générique)
- [x] Aperçu de l'email (test_reset_email.php)
- [x] Lien de réinitialisation fonctionnel
- [x] Indicateur de force du mot de passe
- [x] Validation mot de passe (< 6 caractères)
- [x] Confirmation mot de passe (non correspondant)
- [x] Réinitialisation réussie
- [x] Redirection vers login avec message de succès

---

## 🔄 Améliorations futures possibles

1. **Envoi SMS** en plus de l'email
2. **Authentification à 2 facteurs** (2FA)
3. **Historique des réinitialisations** (logs)
4. **Limitation de tentatives** (rate limiting)
5. **Questions de sécurité** additionnelles
6. **Notification** si réinitialisation non demandée

---

## 👨‍💻 Développeurs

Système développé pour **PeaceLink** - Plateforme d'engagement citoyen

**Date :** 14 décembre 2025

---

## 📄 Licence

© 2025 PeaceLink. Tous droits réservés.
