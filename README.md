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
- Angular 17
- TypeScript
- TailwindCSS
- Angular Material
- RxJS

### Installation et Configuration
1. Naviguer vers le répertoire frontend :
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

## Backend

### Technologies Utilisées
- PHP 8.x
- Symfony 6.x
- EasyAdmin
- Doctrine ORM
- MySQL/MariaDB
- Composer
- Webpack Encore

### Installation et Configuration
1. Naviguer vers le répertoire backend :
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
```
DATABASE_URL="mysql://[DB_USER]:[DB_PASSWORD]@[DB_HOST]:[DB_PORT]/[DB_NAME]"

# Configuration du mailer (exemple avec Gmail)
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