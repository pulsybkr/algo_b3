# Système de Gestion de Bibliothèque

Ce projet est une application CLI (Command Line Interface) permettant de gérer une bibliothèque simple. Il est développé en PHP et utilise SQLite comme base de données.

## Fonctionnalités

- Affichage de la liste des livres
- Ajout de nouveaux livres
- Gestion de la disponibilité des livres

## Prérequis

- Docker
- Docker Compose

## Structure du Projet
# Système de Gestion de Bibliothèque

Ce projet est une application CLI (Command Line Interface) permettant de gérer une bibliothèque simple. Il est développé en PHP et utilise SQLite comme base de données.

## Fonctionnalités

- Affichage de la liste des livres
- Ajout de nouveaux livres
- Gestion de la disponibilité des livres

## Prérequis

- Docker
- Docker Compose

## Structure du Projet
plaintext
.
├── cli/
│ └── cli.php
├── services/
│ └── service.php
├── database/
│ └── database.db
├── docker/
│ └── Dockerfile
├── docker-compose.yml
└── README.md

## Configuration Docker

### Dockerfile

Créez un fichier `docker/Dockerfile` :
dockerfile
FROM php:8.2-cli
RUN apt-get update && apt-get install -y \
git \
unzip \
libsqlite3-dev
RUN docker-php-ext-install pdo pdo_sqlite
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /app
### docker-compose.yml

Créez un fichier `docker-compose.yml` à la racine du projet :
yaml
version: '3.8'
services:
app:
build:
context: .
dockerfile: docker/Dockerfile
volumes:
.:/app
working_dir: /app
command: php cli/cli.php

## Installation

1. Clonez le repository :

2. Installez les dépendances avec Composer :

bash
docker-compose run --rm app composer install


3. Construisez l'image Docker :
bash
docker-compose build


## Lancement de l'Application

Pour lancer l'application :

bash
docker-compose up


## Utilisation

Une fois l'application lancée, vous aurez accès à un menu interactif avec les options suivantes :

1. **Afficher les livres** : Liste tous les livres présents dans la base de données
2. **Ajouter un livre** : Permet d'ajouter un nouveau livre avec :
   - Nom du livre
   - Description
   - Statut de disponibilité
3. **Quitter** : Ferme l'application

## Tests

Pour exécuter les tests (si implémentés) :
bash
docker-compose run --rm app vendor/bin/phpunit


## Gestion de la Base de Données

La base de données SQLite est automatiquement créée au premier lancement de l'application dans le dossier `database/`. Elle contient une table `livres` avec les champs suivants :

- id (INTEGER PRIMARY KEY AUTOINCREMENT)
- nom (TEXT NOT NULL)
- description (TEXT NOT NULL)
- disponible (INTEGER NOT NULL)

## Développement

Pour accéder au conteneur en mode développement :

bash
docker-compose run --rm app bash

## Arrêt de l'Application

Pour arrêter l'application :
bash
docker-compose down