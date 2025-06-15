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
  - Flowbite
- **Cartographie**: Leaflet
- **Carrousel**: Swiper
- **Icônes**: Font Awesome
- **Service Worker**: Pour le support PWA
- **Autres bibliothèques notables**:
  - RxJS pour la gestion des observables

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
├── proxy.conf.json         # Proxy pour le developpement en local en lien avec le backend
├── styles.css              # Styles globaux
└── tailwind.config.js      # Configuration Tailwind CSS
```
### Routing
L'application utilise le Angular Router pour la navigation entre les différentes sections du site. 
Voici la structure des routes principales :

```ts
export const routes: Routes = [
  { path: 'accueil', component: HomeComponent },
  { path: 'partenaires', component: PartnerComponent },
  { path: 'carte', component: MapComponent },
  {
    path: 'informations',
    component: NavBarInformationsComponent,
    children: [
      { path: 'actualites', component: NewsSummaryComponent },
      { path: 'actualite/:id', component: NewsDetailComponent },
      { path: 'infos', component: InformationComponent },
      { path: 'faq', component: FaqComponent },
      { path: '', redirectTo: 'infos', pathMatch: 'full' }
    ]
  },
  { path: 'programmation', component: ProgrammationComponent },
  { path: 'artist/:id', component: ArtistComponent },
  { path: '', redirectTo: 'accueil', pathMatch: 'full' }
];
```
Les routes enfant sous informations permettent une navigation imbriquée avec affichage dans une zone dédiée (comme un <router-outlet> secondaire).

### Services
Les services Angular sont utilisés pour :
- appeler les API backend via HttpClient,
- partager les données entre composants via des BehaviorSubject ou ReplaySubject,
- centraliser la logique métier et la gestion de l'état local.

### Structure de donnéesd
Les interfaces TypeScript dans src/app/services/class.ts définissent les structures de données (Artiste, News, etc.).

### Pipes
Des pipes personnalisés sont utilisés pour :
- Trier des données dans l'ordre alpahbetique
- Trier des données par type

### Tests
Le projet inclut des tests unitaires de base, qui vérifient :
- L’instanciation correcte des composants,
- La présence et le contenu des balises <title> et <meta> (important pour le SEO).

```bash
ng test
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

3. Modifier le fichier `proxy.conf.json` pour définir l'url de base pour se connecter aux API du backend en developpement
```json
{
  "/api": {
    "target": "http://127.0.0.1:8000",
    "secure": false,
    "changeOrigin": true,
    "logLevel": "debug"
  },
  "/uploads": {
    "target": "http://127.0.0.1:8000",
    "secure": false,
    "changeOrigin": true,
    "logLevel": "debug"
  }
}
````

4. Créer un fichier `environnement.production.ts` dans le dossier `environnements` pour définir l'url de base pour se connecter aux API du backend
```ts
export const environment = {
    production : true,
    apiUrl: 'http://127.0.0.1:8000'   #indiquer l'url de votre backend (dans la sutructure de ce projet, le backend est dans le dossier admin sur votre hébérgement)
}
````

5. Démarrer le serveur de développement :
```bash
ng serve --proxy-config proxy.conf.json
```

L'application sera disponible sur http://localhost:4200

### Construction et déploiement

#### Construction pour la production
```bash
ng build --configuration=production
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
- **Sécurité**: SecurityBundle
- **Gestion des assets**: Webpack Encore
- **Doctrine ORM** - Gestion des données
- **style**: Tailwind CSS
- **Cartographie**: Leaflet
- **Mail**: Symfony Mailer
- **Traitement d'images**: LiipImagineBundle 
- **Positionnement**: StofDoctrineExtensionsBundle

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

3. Ouvrir le fichier `config\packages\webpack_encore.yaml` et modifier après `when@prod:` les paramètres de `output_path` et `json_manifest_path` pour le chemin de sortie de votre hébergement de production
```yaml
when@prod:
    webpack_encore:
        output_path: '/[USER_HOME]/[WEB_ROOT]/public_html/admin/build'
         
    framework:
        assets:
            json_manifest_path: '/hom[USER_HOME]/[WEB_ROOT]/public_html/admin/build/manifest.json'
```
Où :
- `[USER_HOME]` : représente le chemin vers votre répertoire utilisateur (exemple : /home/username)
- `[WEB_ROOT]` : représente le dossier racine web (généralement public_html, www, ou htdocs)

4. Verifier que dans le fichier `public\index.php`, c'est bien le chemin vers le fichier autoload.php en local en bien décommenter et que le chemin vers le fichier autoload.php de symfony sur le serveur est passé en comentaire
```php
## chemin vers le fichier autoload.php en local
require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

## chemin vers le fichier autoload.php de symfony sur le serveur
#require_once dirname(__DIR__).'/symfony/vendor/autoload_runtime.php';
```

5. Compiler les assets avec Webpack Encore :
```bash
npm run build
```

6. Configurer le fichier .env avec vos informations de connexion :
- Configuration de la base de données
```php
DATABASE_URL="mysql://[DB_USER]:[DB_PASSWORD]@[DB_HOST]:[DB_PORT]/[DB_NAME]"
```

- Configuration du mailer (exemple avec Gmail)
```php
MAILER_DSN=gmail+smtp://VOTRE_EMAIL@gmail.com:VOTRE_MOT_DE_PASSE_APPLICATION@default
ou 
MAILER_DSN:smtp://[VOTRE-EMAIL@DOMAINE.COM]:[PASSWORD_MAIL]@[SERVEUR_SMTP]?encryption=ssl&auth_mode=login
et 
ADMIN_EMAIL=VOTRE-EMAIL@DOMAINE.COM
```

- Configurer le dossier de base pour l'upload des documents de l'administration que le projet sera en production
 ```
UPLOAD_BASE_DIR='/[USER_HOME]/[WEB_ROOT]/public_html/admin'
# `[USER_HOME]` : représente le chemin vers votre répertoire utilisateur (exemple : /home/username)
# `[WEB_ROOT]` : représente le dossier racine web (généralement public_html, www, ou htdocs)
```


8. Créer la base de données :
```
php bin/console doctrine:database:create
```

9. Trigger SQL
Quand la base de données est crée, il faut rajouter des triggers SQL qui permettent de sauvegarder et d'avoir un historique des différentes modifications
Vous trouverez dans le dossier `trigger`

10. Charger les données de test (fixtures) :
```bash
php bin/console doctrine:fixtures:load
```
Cette commande va pré-remplir la base de données avec des données de test, incluant un compte administrateur :
- Compte administrateur
login : admin@email.com
mot de passe : 2DL8pxQ32yxM

- Compte commercial :
login : commercial@email.com
mot de passe : M2n9PZ2Rme3v

- Compte marketing :
login : marketing@email.com
mot de passe : 7ELkTs78Yxy2

- Compte redacteur :
login : redacteur@email.com
mot de passe : Hg37PX7dE6fn

11. Démarrer le serveur Symfony :
```bash
symfony serve
```

Le serveur sera accessible sur http://localhost:8000

### Construction et déploiement

#### Construction pour la production
- Passer dans le fichier `.env`
APP_ENV=prod
APP_DEBUG=0
et indiquer les parametres de connexion de votre base de données de production et  l'email de réceprtion si nécessaire.

- Verifier que dans le fichier `public\index.php`, c'est bien le chemin vers le fichier autoload.php en local en bien en commentaire et que le chemin vers le fichier autoload.php de symfony sur le serveur n'est pas commenté
```php
## chemin vers le fichier autoload.php en local
#require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

## chemin vers le fichier autoload.php de symfony sur le serveur
require_once dirname(__DIR__).'/symfony/vendor/autoload_runtime.php';
```

- Verifier que le fichier `config\packages\webpack_encore.yaml` possède bien les bons paramètres après `when@prod:` pour `output_path` et `json_manifest_path` comme le chemin de sortie de votre hébergement de production

- Installer/mettre a jour les vendors
```bash
composer install --no-dev --optimize-autoloader
```

- Compilez les assets pour la production :
```bash
npm run build
```

#### Déploiement
Par rapport au frontend fourni, il faut créer un dossier `admin` à la racine du site dans lequel vousn copierez le contenu du dossier `public`

Ensuite vous créez à la racine du site un dossier `symfony` dans lequel vous copiez les dossiers et fichiers suivants :
- assets
- bin
- config
- src
- templates
- translations
- var (a créer vide directement sur le serveur avec droit d’écriture)
- vendor

- .env (n'oubliez pas de renseigner les paramètres de connexion de votre base de données et un email pour la réception des messages d'inscription)
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