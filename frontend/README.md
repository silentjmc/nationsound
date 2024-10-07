# NationSound

NationSound est une application web développée avec Angular 17.
Il s'agit d'un projet Front-end pour la formation EPSI : Titre Développeur web ou web mobile [37674]

## Prérequis

- Node.js (version recommandée : 18.x ou supérieure)
- Angular CLI (version 17.3.6)

## Installation

1. Clonez le dépôt et installer les dépendances
   ```
   git clone [URL_DU_REPO]
   cd nationsound
   ```

2. Installez les dépendances :
   ```
   npm install
   ```

Certaines pages sont reliés à une base de données Wordpress, accessibles depûis l'API wordpress

## Scripts disponibles

- `npm start` : Démarre le serveur de développement
- `npm run build` : Compile l'application pour la production
- `npm test` : Exécute les tests unitaires
- `npm run serve:ssr:nationsound` : Démarre le serveur SSR (Server-Side Rendering)

## Fonctionnalités

- Utilisation de Leaflet pour l'intégration de cartes
- Styles avec Tailwind CSS et Flowbite
- Support du Server-Side Rendering (SSR)

## Dépendances principales

- Angular 17.3.0
- Express 4.18.2
- Leaflet 1.9.4
- RxJS 7.8.0

## Dépendances de développement notables

- TypeScript 5.4.2
- Jasmine et Karma pour les tests
- Tailwind CSS 3.4.3

## Licence

Ce projet est privé et n'est pas sous licence open-source.
