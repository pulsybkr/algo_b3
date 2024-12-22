# Système de Gestion de Bibliothèque
Une application moderne de gestion de bibliothèque avec une architecture microservices, comprenant une API REST, une interface CLI et une interface utilisateur web.
## Fonctionnalités Principales
- ✨ Interface web moderne avec Next.js
 🚀 API REST avec PHP
 💻 Interface en ligne de commande
 📚 Gestion complète des livres (CRUD)
 🔍 Recherche avancée avec tri intelligent
 📝 Historique des actions
 💾 Sauvegarde automatique en JSON
## Prérequis
- Docker
 Docker Compose
## Architecture du Projet


``` plaintext
.
├── api/ # Service API REST
├── cli/ # Interface en ligne de commande
├── next/ # Interface utilisateur web (Next.js)
├── services/ # Logique métier partagée
├── database/ # Données persistantes
└── docker-compose.yml

```	


## Installation

1. Clonez le dépôt : 
```bash
git clone git@github.com:pulsybkr/algo_b3.git
```
2. Naviguez vers le répertoire du projet :
```bash
cd algo_b3
```
3. Construisez et démarrez les conteneurs Docker :
```bash
docker-compose up -d
```


## Services Disponibles

### 1. Interface Web (Frontend)
- URL: `http://localhost:3000`
- Framework: Next.js
- Fonctionnalités:
  - Interface utilisateur moderne
  - Gestion intuitive des livres
  - Recherche en temps réel

### 2. API REST
- URL: `http://localhost:8000`
- Endpoints principaux:
  - `GET /livres` - Liste tous les livres
  - `POST /ajouter` - Crée un nouveau livre
  - `PUT /modifier` - Modifie un livre
  - `DELETE /supprimer` - Supprime un livre
  - `GET /trier` - Trie les livres par colonne
  - `GET /rechercher` - Recherche un livre par colonne et valeur
  - `GET /historique` - Affiche l'historique des actions

### 3. Interface CLI
- Accès: `docker-compose run cli`
- Commandes disponibles:
  - 📖 Afficher les livres
  - 👁️ Afficher un livre
  - ➕ Ajouter un livre
  - ✏️ Modifier un livre
  - 🗑️ Supprimer un livre
  - 🔄 Trier les livres
  - 🔍 Rechercher des livres
  - 📜 Historique des actions
  - ❌ Quitter

## Fonctionnalités Avancées

### Système de Tri
- Tri fusion optimisé
- Tri possible par différentes colonnes
- Performance O(n log n)

### Système de Recherche
- Recherche binaire efficace
- Recherche partielle supportée
- Performance O(log n)

### Persistance des Données
- Base de données SQLite
- Sauvegarde JSON automatique
- Historique des actions

## Développement

Pour accéder aux différents services en développement :

#Interface CLI
```bash
docker-compose run cli
```

#logs
```bash
docker-compose logs -f [service]
```


## Arrêt des Services
```bash
docker-compose down
```

