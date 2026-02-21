# Backend Wizia (Laravel)

## Introduction

Ceci est le backend de l'application Wizia, conteneurisé avec Docker.

## Prérequis

- Docker
- Docker Compose

## Lancer le projet en local

Pour lancer le projet, suivez ces étapes :

```bash
git clone https://github.com/DimiBeziau/backend_wizia.git
cd backend_wizia
cp .env.example .env
docker network create public || true
docker compose up -d --build
```

### Accès à l'application

- **Backend API** : [http://localhost:8000](http://localhost:8000)
- **Base de données (phpMyAdmin)** : [http://localhost:8081](http://localhost:8081)
    - **Utilisateur** : `wizia_user_prod`
    - **Mot de passe** : `wizia_password_prod` (voir `.env` pour confirmation)

> [!NOTE]
> Le déploiement Docker inclus un healthcheck pour la base de données afin de s'assurer que l'application Laravel ne démarre les migrations que lorsque la base est prête. Le dossier `vendor` est préservé via un volume anonyme.

## Architecture

En savoir plus sur l'[architecture de ce projet](./docs/architecture.md)