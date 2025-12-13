# Intégration Gestion des Offres - PeaceLink

## 📋 Vue d'ensemble

Ce module intègre un système complet de gestion des offres d'emploi/missions dans la plateforme PeaceLink. Il permet aux organisations de publier des offres et aux clients de postuler.

## 🗄️ Configuration de la Base de Données

### 1. Exécuter le script SQL principal
```bash
# Importer d'abord la base principale
mysql -u root < NovaLinkPeace/db/peacelink_merged.sql
```

### 2. Ajouter les tables d'offres
```bash
# Ajouter les tables offers et applications
mysql -u root peacelink < sql/add_offres_tables.sql
```

### Structure des tables ajoutées
- **offers** : Stocke les offres créées par les organisations
- **applications** : Stocke les candidatures des clients

## 👥 Fonctionnalités par Rôle

### Pour les Organisations (role = 'organisation')

#### Navbar
- **Gestion des Offres** : Accès à la liste des offres
- **Candidatures** : Voir toutes les candidatures reçues

#### Page Gestion des Offres (`index.php?action=list`)
- ✅ Publier une nouvelle offre
- ✅ Voir toutes les offres (toggle "Mes offres" / "Toutes les offres")
- ✅ Modifier uniquement ses propres offres
- ✅ Supprimer uniquement ses propres offres
- ✅ Voir le nombre de candidats par offre
- ✅ Voir le nombre de places restantes

#### Page Candidatures (`index.php?action=list_applications`)
- ✅ Voir toutes les candidatures pour ses offres
- ✅ Afficher le nom complet et username du client
- ✅ Voir le score IA et le sentiment
- ✅ Accepter une candidature
  - Envoi automatique d'un email de félicitations au client
- ✅ Refuser une candidature
  - Envoi automatique d'un email de notification
- ✅ Filtrer par offre spécifique

### Pour les Clients (role = 'client')

#### Navbar
- **Offres** : Accès à toutes les offres disponibles

#### Page Offres (`index.php?action=list`)
- ✅ Visualiser toutes les offres publiées
- ✅ Voir le nombre de places restantes dans chaque offre
- ✅ Voir le nom de l'organisation qui propose l'offre
- ✅ Postuler aux offres (si places disponibles)
- ❌ Impossible de postuler si l'offre est complète

#### Formulaire de Candidature
- Système intelligent avec :
  - Détection de contenu IA (anti-triche)
  - Filtrage par mots-clés (ATS)
  - Scoring automatique
  - Analyse de sentiment

## 📧 Système d'Emails Automatiques

### Email d'Acceptation
Envoyé automatiquement quand une organisation accepte une candidature :
- Design professionnel HTML
- Informations sur l'offre et l'organisation
- Message personnalisé avec le nom du candidat

### Email de Refus (Optionnel)
Envoyé si l'organisation refuse une candidature :
- Message courtois et encourageant
- Suggestion de consulter d'autres offres

## 🔐 Sécurité et Contrôles d'Accès

### Contrôles implémentés
1. **Session requise** : Tous les utilisateurs doivent être connectés
2. **Rôles vérifiés** : 
   - Seules les organisations peuvent créer/modifier/supprimer des offres
   - Seuls les clients peuvent postuler
   - Seules les organisations peuvent gérer les candidatures
3. **Propriété des offres** : Une organisation ne peut modifier que ses propres offres
4. **Filtrage SQL** : Les offres affichées aux organisations peuvent être filtrées

## 🚀 Routes Disponibles

### Routes Publiques (connecté)
- `index.php?action=list` - Liste des offres

### Routes Organisations
- `index.php?action=create` - Créer une offre
- `index.php?action=edit&id={id}` - Modifier une offre
- `index.php?action=delete&id={id}` - Supprimer une offre
- `index.php?action=list_applications` - Toutes les candidatures
- `index.php?action=list_applications&offer_id={id}` - Candidatures d'une offre
- `index.php?action=update_status&id={id}&status={status}` - Accepter/Refuser

### Routes Clients
- `index.php?action=apply&id={id}` - Formulaire de candidature
- `index.php?action=submit_application` - Soumettre la candidature

## 📁 Fichiers Modifiés/Créés

### Modèles
- ✏️ `model/Database.php` - Changement de BDD vers "peacelink"
- ✏️ `model/Offer.php` - Ajout du champ id_organisation + méthodes filtrées
- ✏️ `model/Application.php` - Ajout du champ id_client + jointure avec Client
- ✨ `model/EmailService.php` - Nouveau service d'envoi d'emails

### Contrôleur
- ✏️ `controller/OfferController.php` - Gestion des sessions et rôles

### Vues
- ✏️ `view/offers_list.php` - Interface adaptée au rôle
- ✏️ `view/admin_applications_list.php` - Affichage des noms de clients

### Navigation
- ✏️ `NovaLinkPeace/test/View/FrontOffice/partials/header.php` - Navbar dynamique

### SQL
- ✨ `sql/add_offres_tables.sql` - Tables offers et applications

## 🎨 Interface Utilisateur

### Design cohérent
- Utilise les mêmes templates (header.php, footer.php)
- Style CSS unifié avec le reste du site
- Icônes Font Awesome
- Couleurs thématiques :
  - Bleu pastel : Offres
  - Violet : Candidatures
  - Vert : Actions positives
  - Rouge : Actions critiques

## 🧪 Test du Système

### 1. Se connecter en tant qu'Organisation
```
Email: greenearth@example.com
Password: (voir dans la base)
```

### 2. Se connecter en tant que Client
```
Email: sami@example.com
Password: (voir dans la base)
```

### 3. Scénario de test complet
1. Organisation crée une offre
2. Client consulte les offres disponibles
3. Client postule à une offre
4. Organisation voit la candidature
5. Organisation accepte la candidature
6. Client reçoit un email automatique

## ⚙️ Configuration Email

Le système utilise la fonction PHP `mail()`. Sur un environnement de développement local :

### Option 1 : MailHog (Recommandé pour dev)
```bash
# Installer MailHog pour capturer les emails en local
# Les emails seront visibles sur http://localhost:8025
```

### Option 2 : Fake Mail
Si l'envoi échoue, le système sauvegarde les emails dans `emails_simules/` en HTML.

### Option 3 : SMTP Réel
Modifier `model/EmailService.php` pour utiliser une bibliothèque comme PHPMailer avec un serveur SMTP.

## 🐛 Dépannage

### Erreur "Base de données inexistante"
```bash
# Vérifier que la base peacelink existe
mysql -u root -e "SHOW DATABASES;"

# Si non, importer le SQL
mysql -u root < NovaLinkPeace/db/peacelink_merged.sql
```

### Erreur "Table offers n'existe pas"
```bash
# Exécuter le script d'ajout des tables
mysql -u root peacelink < sql/add_offres_tables.sql
```

### Session non démarrée
Les sessions sont gérées automatiquement dans le contrôleur.

### Emails non reçus
Vérifier les emails simulés dans le dossier `emails_simules/` à la racine du projet.

## 📝 Notes Importantes

1. **IA Token** : Remplacer le token Hugging Face dans `OfferController.php` ligne 16 pour activer la détection IA
2. **Permissions** : S'assurer que le dossier `emails_simules/` est accessible en écriture
3. **Base de données** : Toujours utiliser "peacelink" comme nom de base

## 🔄 Prochaines Améliorations Possibles

- [ ] Historique des candidatures pour les clients
- [ ] Notifications en temps réel
- [ ] Système de messagerie intégré
- [ ] Statistiques pour les organisations
- [ ] Export des candidatures en CSV/PDF
- [ ] Système de notation des candidats
