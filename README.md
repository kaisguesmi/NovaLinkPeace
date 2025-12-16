# PeaceLink Integration

Plateforme web de mise en relation organisations / clients / experts avec offres, initiatives, histoires, réclamations, et outils d’administration.

## Sommaire
- Démo locale & URLs
- Fonctionnalités clés
- Parcours utilisateurs
- Back-office (admin)
- Offres & candidatures
- Initiatives (events)
- Histoires, réactions, réclamations
- Authentification & réinit. mot de passe
- Vérification organisations & experts
- Pré-requis & setup
- Structure du projet

## Démo locale & URLs
- Front Office : http://localhost/integration/index.php
- Back Office PHP (test) : http://localhost/integration/test/View/BackOffice/backoffice.php
- Back Office statique (demo) : http://localhost/integration/views/backoffice.html
- PeaceLink Expert Dashboard (demo) : http://localhost/integration/PeaceLink_Expert_Dashboard/index.html
- Reset password (flux complet) : http://localhost/integration/NovaLinkPeace/test/View/FrontOffice/forgot_password.php
- Stories réclamations (admin) : http://localhost/integration/NovaLinkPeace/test/View/BackOffice/reclamations.php

## Fonctionnalités clés
- Offres : création/modif/suppression par organisations vérifiées, candidatures clients, email d’acceptation/refus.
- Initiatives (events) : création par organisations vérifiées et experts associés à une organisation vérifiée.
- Histoires : publication, commentaires, réactions (like/love/dislike/laugh/angry), réclamations, modération admin.
- Authentification : login, logout, FaceID (tests), réinitialisation mot de passe par email (token 1h, usage unique).
- Vérification : seules les organisations vérifiées publient offres/initiatives ; les experts sont liés à l’organisation qui les a acceptés.
- Bad-words filter sur contenus histoires/réclamations.
- Tableau de bord admin avec badge de réclamations en attente.

## Parcours utilisateurs
- Client : s’inscrit, postule à des offres, publie des histoires, commente/réagit, peut devenir expert si sa candidature est acceptée.
- Organisation : crée des offres, voit ses candidatures, accepte/refuse (promotion candidat → expert) ; crée des initiatives si vérifiée.
- Expert : lié à une organisation, peut créer des initiatives au nom de l’organisation.
- Admin : valide organisations, gère utilisateurs/bannis, modère réclamations d’histoires.

## Back-office (admin)
- Accès : http://localhost/integration/test/View/BackOffice/backoffice.php
- Sections : Dashboard, Organisations (validation), Clients, Banned Users, Réclamations (histoires) avec badge de nouvelles réclamations.

## Offres & candidatures
- Controller : controller/OfferController.php
- Modèles : model/Offer.php, model/Application.php
- Promotion client→expert sur acceptation (Expert.organisation_id renseigné) avec email d’acceptation.

## Initiatives (events)
- API : controllers/EventController.php (et NovaLinkPeace/controllers/EventController.php)
- Modèle : models/EventModel.php
- org_id stocke l’organisation propriétaire (y compris quand créé par un expert lié).

## Histoires / Réclamations
- Modèle : test/Model/Histoire.php (et NovaLinkPeace/test/Model/Histoire.php)
- Contrôleur : test/Controller/HistoireController.php (et NovaLinkPeace/test/Controller/HistoireController.php)
- Backoffice : test/View/BackOffice/reclamations.php (et NovaLinkPeace/test/View/BackOffice/reclamations.php)

## Authentification & Réinitialisation
- Contrôleur : test/Controller/UtilisateurController.php (et NovaLinkPeace/test/Controller/UtilisateurController.php)
- Vues : login, forgot_password, reset_password sous test/View/FrontOffice/… (et NovaLinkPeace/test/View/FrontOffice/...)
- Email : model/EmailService.php
- Sécurité reset : token 1h, hash BCRYPT, vérifications et logs sur update.

## Vérification organisations & experts
- Organisations doivent être "Verifié" pour publier offres/initiatives.
- Experts : créés lors d’acceptation d’une candidature ; champ organisation_id pour rattacher l’expert à l’org qui l’a accepté.

## Pré-requis & Setup
- PHP 8+, MySQL, XAMPP.
- Base : importer db/peacelink_merged.sql ou sql/peacelink_complete.sql ; appliquer sql/add_expert_table.sql si besoin.
- Config DB : model/Database.php (et test/Model/Database.php).
- Accès admin seed : admin_seed.php (email/par défaut défini dans le fichier).

## Structure (extrait)
- controller/ : OfferController.php
- controllers/ : EventController.php
- model/ : Database.php, Offer.php, Application.php, EmailService.php, Utilisateur.php
- models/ : EventModel.php, ParticipationModel.php
- test/ : Controller/, Model/, View/ (FrontOffice, BackOffice), helpers/
- NovaLinkPeace/ : miroirs des contrôleurs/vues pour l’intégration
- assets/, view/, views/, PeaceLink_Expert_Dashboard/ : front

## Tests rapides
- Créer offre (organisation vérifiée) → postuler en client → accepter → vérifier Expert.organisation_id + email.
- Création initiative en expert lié → org affichée sur l’initiative.
- Réinit mot de passe → login avec nouveau mot de passe.
- Réclamations : soumettre une histoire signalée → badge dans backoffice admin.

## Notes
- Les colonnes/DDL peuvent nécessiter : `ALTER TABLE Expert ADD COLUMN organisation_id INT NULL;` (FK optionnelle vers Organisation).
- Pour l’email en dev, test_reset_email.php donne un aperçu du template.