#!/bin/sh
# Dijalankan DI SERVER dari folder proyek: ./deploy.sh
set -e
git pull --ff-only
docker compose -f docker-compose.prod.yml up -d --build
docker compose -f docker-compose.prod.yml ps
