Projet fait par Kylian LHUISSIER et Valentin BEDET

# Symfony TweetToast (Docker)

Ce projet est une démonstration minimaliste d'une application Symfony exécutée dans un environnement Docker. Il expose une application "Twitter" minimaliste 

## 🚀 Prérequis

- Docker & Docker Compose installés
- Git (optionnel mais recommandé)

## 🧱 Stack technique

- PHP 8.2 (via Apache)
- Symfony 7
- MySQL 8
- Docker + Docker Compose
- PhpMyAdmin

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

### 4. Créer des données par défaut
```bash
docker exec -it symfony-php php bin/console doctrine:fixtures:load
```

### 5. Vérifie que tout fonctionne :

Accède à l'URL : http://localhost:8000


📁 Structure des dossiers
app/ : Code source Symfony

.docker/ : Configuration Apache (vhost)

Dockerfile : Image PHP + Apache

docker-compose.yml : Définition des services

PhpMyAdmin est accessible via : http://localhost:8080

### Commandes utiles
```bash
# Composer dans le conteneur
docker exec -it symfony-php composer <commande>

# Console Symfony
docker exec -it symfony-php php bin/console

# Logs Apache
docker logs -f symfony-php
```