# 🧠 Documentation Technique : Module Face ID (PeaceLink)

## 📌 Présentation
Le module **Face ID** permet une authentification biométrique sécurisée sans mot de passe.
Il ne s'agit pas d'une simple comparaison d'images pixel par pixel, mais d'une analyse biométrique basée sur l'Intelligence Artificielle (Deep Learning).

## 🛠️ Stack Technologique
*   **Langage :** JavaScript (Client-side) & PHP (Server-side).
*   **Librairie IA :** `face-api.js` (basée sur **TensorFlow.js** de Google).
*   **Architecture :** MVC (Model - View - Controller).
*   **Communication :** AJAX (Fetch API) pour les échanges asynchrones.

## ⚙️ Fonctionnement Algorithmique

Le processus d'authentification se déroule en 4 étapes clés :

### 1. Chargement des Réseaux de Neurones (Neural Networks)
Au chargement de la page `login.php`, le navigateur charge trois modèles pré-entraînés :
*   **SSD Mobilenet V1 :** Pour la détection de visages (savoir où est le visage dans l'image).
*   **Face Landmark 68 :** Pour repérer 68 points géométriques clés (yeux, nez, bouche, mâchoire).
*   **Face Recognition :** Pour transformer ces points en une empreinte numérique unique.

### 2. Récupération et Analyse de la Photo de Profil (Référence)
L'utilisateur entre son email. Le système récupère sa photo de profil via le Contrôleur.
L'IA analyse cette photo et génère un **Face Descriptor**.
> *Le Face Descriptor est un vecteur mathématique de 128 nombres (float) qui représente l'identité unique de la personne, indépendamment de la lumière ou de l'angle.*

### 3. Analyse du Flux Vidéo (Temps Réel)
La webcam s'active. L'IA analyse chaque image du flux vidéo (environ 10 fois par seconde) et calcule le **Face Descriptor** de la personne qui se trouve devant l'écran.

### 4. Calcul de la Distance Euclidienne (Le Verdict)
L'algorithme compare le vecteur de la **Photo Profil** avec le vecteur de la **Webcam** en calculant la "Distance Euclidienne".
*   La distance représente la différence entre les deux visages.
*   **Seuil de tolérance (Threshold) :** `0.5`.
*   **Logique :**
    *   `Si Distance < 0.5` : C'est la même personne ✅ -> Connexion automatique.
    *   `Si Distance > 0.5` : Ce n'est pas la même personne ❌ -> Accès refusé.

---

## 📂 Emplacement du Code (Structure MVC)

### 1. View (`View/FrontOffice/login.php`)
Contient l'interface utilisateur, la balise `<video>`, et toute la logique JavaScript (`faceapi.detectSingleFace`, `faceapi.euclideanDistance`).

### 2. Controller (`Controller/UtilisateurController.php`)
Gère deux actions spécifiques :
*   `ajax_get_photo` : Reçoit l'email et renvoie le chemin de la photo de profil (JSON).
*   `login_with_face` : Reçoit la confirmation du JS et crée la session utilisateur (`$_SESSION`).

### 3. Model (`Model/Utilisateur.php`)
Fournit les méthodes `findByEmail`, `findClientById` et `findOrganisationById` pour accéder aux données brutes en base de données.

---

## 🔒 Sécurité et Contraintes
*   **Protection :** La caméra ne s'active **que** si l'email existe et possède une photo de profil valide.
*   **Prérequis :** L'utilisateur doit avoir uploadé une photo de profil lors de son inscription ou via l'édition de profil.