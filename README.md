# NationSound Project

## Description
NationSound est une application web construite avec Angular 17 pour le frontend et Symfony/EasyAdmin pour le backend.

## Structure du Projet
```
nationsound/
├── frontend/    # Application Angular
└── backend/     # Application Symfony
```

## Frontend

### Technologies Utilisées
- **Framework**: Angular 17.3.0
- **Langage**: TypeScript
- **Style**: 
  - Tailwind CSS
  - Material Tailwind
- **Cartographie**: Leaflet
- **Carrousel**: Swiper
- **Icônes**: Font Awesome
- **Service Worker**: Pour le support PWA
- **Autres bibliothèques notables**:
  - RxJS pour la gestion des observables
  - Pushy SDK pour les notifications push

### Structure du projet

```
src/
├── app/                    # Composants principaux de l'application
│   ├── alert-news/         # Composant d'alerte actualités
│   ├── artist/             # Composant fiche des artistes
│   ├── carousel/           # Composant carrousel
│   ├── faq/                # Composant Foire aux questions
│   ├── footer/             # Composant Pied de page
│   ├── header/             # Composant En-tête
│   ├── home/               # Composant Page d'accueil
│   ├── information/        # Composant Actualités/Informations
│   ├── map/                # Composant Carte interactive
│   ├── models/             # Modèles de données
│   ├── news-detail/        # Composant Détail des actualités
│   ├── news-summary/       # Composant Résumé des actualités
│   ├── partner/            # Composant Partenaires
│   ├── pipe/               # Pipes personnalisés
│   ├── programmation/      # Composant Programmation du festival
│   └── services/           # Services partagés
├── assets/                 # Ressources statiques
├── environments/           # Configurations d'environnement
├── index.html              # Point d'entrée HTML
├── styles.css              # Styles globaux
└── tailwind.config.js      # Configuration Tailwind CSS
```

### Installation et Configuration
1. Après avoir cloner le projet, naviguer vers le répertoire frontend :
```bash
cd frontend
```

2. Installer les dépendances :
```bash
npm install
```

3. Démarrer le serveur de développement :
```bash
ng serve
```

L'application sera disponible sur http://localhost:4200

### Construction et déploiement

#### Construction pour la production
```bash
ng build
```
Les fichiers de production seront générés dans le dossier `dist/`.
Il y aura deux dossiers.
Le dossier `browser` contiendra la version navigateur de l'application.
Le dossier `server` contiendra la version rendu côté server (SSR).
Selon votre hébergement web, vous pourrez choisir de mettre en place le SSR ou non.

#### Déploiement

1. **Hébergement statique** (sans SSR) :
   - Déployez uniquement le contenu du dossier `browser/`

2. **Avec SSR** :
   - Déployez à la fois les dossiers `browser/` et `server/`
   - Nécessite un environnement Node.js pour exécuter le serveur

## Backend

### Technologies Utilisées
- **Langage**: PHP 8.1+
- **Framework**: Symfony 6.4
- **Base de données**: MySQL/MariaDB
- **Interface d'administration**: EasyAdmin
- **Gestion des assets**: Webpack Encore
- **Doctrine ORM** - Gestion des données
- **style**: Tailwind CSS
- **Cartographie**: Leaflet
- **Mail**: Symfony Mailer
- **Notifications**: Pushy

### Structure du projet

```
├── assets/             # Ressources statiques
├── config/             # Configuration de l'application 
├── migrations/         # Fichiers de migration BDD 
├── public/             # Point d'entrée public 
├── src/  
│  ├── Controller/      # Contrôleurs 
│  ├── DataFixtures/    # Fixtures de données 
│  ├── Entity/          # Entités Doctrine 
│  ├── Form/            # Formulaires Symfony 
│  ├── Repository/      # Requêtes personnalisées 
│  ├── Security/        # Authentification Symfony 
│  └── Service/         # Logique métier 
├── templates/          # Templates Twig
├── composer.json       # Configuration Composer
├── importmap.php       # Configuration des assets
├── postcss.config.js   # Configuration PostCSS
├── tailwind.config.js  # Configuration Tailwind CSS
└── webpack.config.js   # Configuration Webpack Encore
```


### Installation et Configuration
1. Après avoir cloner le projet, aviguer vers le répertoire backend :
```bash
cd backend
```

2. Installer les dépendances :
```bash
# Installation des dépendances PHP via Composer (Symfony et ses composants)
composer install

# Installation des dépendances JavaScript pour Webpack Encore
npm install
```

3. Compiler les assets avec Webpack Encore :
```bash
npm run build
```

4. Configurer le fichier .env avec vos informations de connexion :
# Configuration de la base de données
```
DATABASE_URL="mysql://[DB_USER]:[DB_PASSWORD]@[DB_HOST]:[DB_PORT]/[DB_NAME]"
```

# Configuration du mailer (exemple avec Gmail)
```
MAILER_DSN=gmail+smtp://VOTRE_EMAIL@gmail.com:VOTRE_MOT_DE_PASSE_APPLICATION@default
```

Vous pouvez également configurer le mailer dans `config/packages/mailer.yaml` :
```yaml
framework:
    mailer:
        dsn: '%env(MAILER_DSN)%'
        envelope:
            sender: 'votre-email@domaine.com'
```

5. Créer la base de données et effectuer les migrations :
```
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

6. Trigger SQL
Quand la base de données est crée, il faut rajouter des triggers SQL qui permettent de sauvegarder et d'avoir un historique des différentes modifications
Vous trouverez dans le dossier `trigger`

6. Charger les données de test (fixtures) :
```bash
php bin/console doctrine:fixtures:load
```
Cette commande va pré-remplir la base de données avec des données de test, incluant un compte administrateur :
- Email : admin@admin.com
- Mot de passe : admin

7. Démarrer le serveur Symfony :
```bash
symfony serve
```

Le serveur sera accessible sur http://localhost:8000

### Construction et déploiement

#### Construction pour la production
Passer dans le fichier .env
APP_ENV=prod
APP_DEBUG=0

Installer/mettre a jour les vendors
```
composer install --no-dev --optimize-autoloader
```
Nettoyer le cache
```
php bin/console cache:clear --env=prod 
```

#### Déploiement
Copier dans le fichier racine du site
- le dossier bundle
- .htaccess
- index.php

Dans un dossier au même  niveau que la racine
- assets
- bin
- config
- migrations
- src
- templates
- translations
- var (a créer vide directement sur le serveur avec droit d’écriture)
- vendor

- .env
- composer.json
- composer.lock
- importmap.php
- symfony.lock

## Démarrage Rapide
1. Cloner le repository
2. Configurer le frontend et le backend en suivant les instructions ci-dessus
3. S'assurer que MySQL/MariaDB est installé et en cours d'exécution
4. Démarrer les serveurs frontend et backend

## Fonctionnalités
- Authentification utilisateur
- Gestion des événements
- Profils des artistes
- Planification des événements
- Carte interactive des lieux d'événements
- Interface d'administration avec EasyAdmin