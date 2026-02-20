# Backend Wizia (Laravel)

## Introduction

Ceci est le backend de l'application WIzia.
Il est containerisé avec Docker.

## Prérequis

- Docker
- Docker Compose

## Lancer le projet en local

```bash
git clone https://github.com/DimiBeziau/backend_wizia.git
cd backend_wizia
cp .env.example .env
docker network create public || true
docker compose up -d --build
```

L'accès au backend de l'application se fait sur [http://localhost:8000](http://localhost:8000)
Le visuel sur la base de données (phpMyAdmin) est accessible depuis [http://localhost:8081](http://localhost:8081), avec les identifiants enregistrés dans le fichier `.env` (utilisateur: `wizia_user_prod`, mot de passe: `wizia_password_prod`).

En savoir plus sur l'[architecture de ce projet](./docs/architecture.md)