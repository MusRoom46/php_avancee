# TweetToast - Application de microblogging

## 👥 Équipe
- [Kylian LHUISSIER]
- [Valentin BEDET]

## 🔗 Dépôt GitHub
URL du dépôt : https://github.com/MusRoom46/php_avancee.git

## 📝 Description du projet
TweetToast est une application de microblogging inspirée de Twitter, développée avec Symfony et Docker. Elle permet aux utilisateurs de créer un compte, publier des tweets, suivre d'autres utilisateurs et interagir avec leur contenu.

### État actuel du projet
- ✅ API REST fonctionnelle avec authentification JWT
- ✅ Gestion des utilisateurs (inscription, connexion, profil)
- ✅ Publication de tweets
- ✅ Système de followers
- ✅ Recherche de tweets et d'utilisateurs
- ✅ Liker et commenteer un tweet

## 🚀 Prérequis

- Docker & Docker Compose installés
- Git (optionnel mais recommandé)

## 🧱 Stack technique

- PHP 8.2 (via Apache)
- Symfony 7
- MySQL 8
- Docker + Docker Compose
- PhpMyAdmin
- JWT pour l'authentification API

## 📦 Installation

### 1. Clone le projet :

```bash
git clone https://github.com/MusRoom46/php_avancee.git
cd php_avancee
```

### 2. Lance les conteneurs :
```bash
docker-compose up --build -d
```

### 3. Installe les dépendances Symfony :
```bash
docker exec -it symfony-php composer install
```

### 4. Crée la base de données et charge les fixtures (données de test) :
```bash
docker exec -it symfony-php php bin/console doctrine:database:create --if-not-exists
docker exec -it symfony-php php bin/console doctrine:migration:migrate
docker exec -it symfony-php php bin/console doctrine:schema:update --force

# Générer des données par défaut :
docker exec -it symfony-php php bin/console doctrine:fixtures:load
```

### 5. Vérifie que tout fonctionne :

Accède à l'URL : http://localhost:8000

Tu devrais voir : la page d'acceuil avec la Timeline si tu es connecté sinon tu seras redirigé vers la page de connexion.

## 📁 Structure des dossiers
- `app/` : Code source Symfony
- `.docker/` : Configuration Apache (vhost)
- `Dockerfile` : Image PHP + Apache
- `docker-compose.yml` : Définition des services

## 🔧 Outils et accès

- **Application Symfony** : http://localhost:8000
- **PhpMyAdmin** : http://localhost:8080
  - Serveur : db
  - Utilisateur : root
  - Mot de passe : root
  - Base de données : tweettoast

## 🔑 Utilisation de l'API

### Authentification
L'API utilise JWT (JSON Web Tokens) pour l'authentification. Pour accéder aux endpoints protégés, tu dois :

1. Créer un compte utilisateur ou utiliser un compte existant
2. Obtenir un token JWT via l'endpoint de login
3. Inclure ce token dans l'en-tête Authorization de vos requêtes (`Bearer {token}`)

### Endpoints principaux
Lien vers la documentation des API (Swagger) :
http://localhost:8000/api/doc

## 💻 Commandes utiles
```bash
# Composer dans le conteneur
docker exec -it symfony-php composer <commande>

# Console Symfony
docker exec -it symfony-php php bin/console <commande>

# Logs Apache
docker logs -f symfony-php
```
