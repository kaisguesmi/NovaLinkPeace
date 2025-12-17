# PeaceLink – Overview

PeaceLink est une plateforme PHP/MySQL de partage d’histoires et d’initiatives avec modération intégrée (réclamations). Le code principal se trouve dans le dossier Final-UA-Task.

## Architecture rapide
- Entrées web :
	- Front : Final-UA-Task/public/index.php
	- Admin : Final-UA-Task/public/admin.php
- MVC artisanal : contrôleurs et modèles dans Final-UA-Task/app, vues dans Final-UA-Task/app/views.
- Assets : Final-UA-Task/public/assets (CSS/JS/images).
- SQL d’amorçage : Final-UA-Task/database/peacelink.sql et db/peacelink_merged.sql.

## Prérequis
- PHP 8.x avec extensions pdo_mysql, curl.
- MySQL/MariaDB.
- Un serveur web pointant vers Final-UA-Task/public (Apache, nginx, ou `php -S localhost:8000 -t public`).

## Configuration
1) Créez un fichier Final-UA-Task/.env (optionnel mais conseillé) :
```
DB_HOST=127.0.0.1
DB_NAME=peacelink
DB_USER=root
DB_PASS=
DB_CHARSET=utf8mb4

# IA : off | heuristic | gemini
AI_PROVIDER=heuristic
GEMINI_API_KEY=your-key
GEMINI_MODEL=gemini-1.5-flash

# JWT (placeholder, non appliqué côté front)
JWT_SECRET=change-me-secret
JWT_ISS=peacelink
JWT_AUD=peacelink-users
JWT_TTL=7200
```
2) Importez la base : `mysql -u root -p peacelink < Final-UA-Task/database/peacelink.sql` (ou le dump fusionné db/peacelink_merged.sql si besoin).
3) Placez le virtualhost ou le serveur PHP sur Final-UA-Task/public.

## Fonctionnalités clés
- Histoires/Posts : création, réactions, commentaires, statuts (pending/approved/rejected), notifications basiques.
- Réclamations (modération) :
	- Signalement d’une histoire/commentaire avec causes multiples et description.
	- Anti-bot (honeypot + anti-spam léger) et anti-doublon par utilisateur/cible.
	- Scoring IA : provider `off`/`heuristic`/`gemini` avec repli heuristique si clé manquante.
	- Exports CSV/TSV, statistiques (Chart.js), logs de décisions admin.
	- Pages utilisateur : “Mes réclamations envoyées” et “Histoires signalées” (réclamations reçues sur vos histoires).
- UI : toasts/alerts unifiés, tableaux responsives, navigation front avec liens vers les vues de réclamation.

## Navigation utile
- Front stories : `/?controller=histoire&action=index`
- Mes réclamations : `/?controller=reclamation&action=my`
- Histoires signalées (réclamations reçues) : `/?controller=reclamation&action=received`
- Admin réclamations : `/admin.php?controller=reclamation&action=index`

## Structure répertoire (résumé)
- Final-UA-Task/app/core : base MVC, router simple, helper baseUrl/asset.
- Final-UA-Task/app/controllers : logique métier (histoires, posts, réclamations, admin...).
- Final-UA-Task/app/models : accès DB (Histoire, Reclamation, Commentaire, etc.).
- Final-UA-Task/app/views : layouts front/admin, pages et partiels.
- public/assets : CSS/JS (front.css, backoffice.css, alerts.js, etc.).
- config/config.php : charge .env, DB, AI, JWT placeholders.

## Lancer en local (exemple PHP built-in)
```bash
cd Final-UA-Task/public
php -S localhost:8000
# Ouvrir http://localhost:8000
```

## Points d’attention
- Authentification : mode “utilisateur anonyme” simplifié, pas de rôles réels (placeholder JWT).
- Sécurité : CSRF désactivé côté front ; à activer/renforcer pour prod.
- Export Excel : généré en TSV pour compatibilité rapide (pas de .xlsx natif).
- IA Gemini : nécessite une clé valide ; sinon le scoring retombe sur l’heuristique.

## Support
Pour tout problème, notez l’URL appelée et le message PHP/console, puis ouvrez une issue interne ou contactez l’équipe.*** End Patch