# Backend Laravel

## Introduction

Ceci est le backend de l'application WIzia.
Il est containerisé avec Docker.

## Prérequis

- Docker
- Docker Compose

## Lancer le projet en local

```
git clone https://github.com/DimiBeziau/backend_wizia.git
mkdir docker_build
cd ./backend_wizia
cp ./.env.example ../docker_build/.env
cp ./docker-compose.prod.yml ../docker_build/docker-compose.yml
cd ../docker_build
docker compose up -d
```