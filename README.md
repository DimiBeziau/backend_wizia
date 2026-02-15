# Backend Wizia (Laravel)

## Introduction

Ceci est le backend de l'application WIzia.
Il est containerisé avec Docker.

## Prérequis

- Docker
- Docker Compose

## Lancer le projet en local

```
git clone https://github.com/DimiBeziau/backend_wizia.git
cd ./backend_wizia
cp ./.env.example ./.env
docker network create public
docker compose -f docker-compose.prod.yml up -d
```

L'accès au backend de l'application se fait sur [http://localhost:8000](http://localhost:8000)
Le visuel sur la base de données est accessible depuis [http://localhost:8081](http://localhost:8081), avec les identifiants enregistrés dans le fichier docker_build/.env

En savoir plus sur l'[architcture de ce projet](./docs/architecture.md)