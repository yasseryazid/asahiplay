#!/bin/sh
# Dijalankan DI SERVER dari folder proyek:  ./deploy.sh
# File compose dibaca dari COMPOSE_FILE di .env (default: docker-compose.prod.yml).
set -e
F=$(grep -s '^COMPOSE_FILE=' .env | cut -d= -f2); F=${F:-docker-compose.prod.yml}
if docker compose version 2>&1 | grep -q "Docker Compose"; then DC="docker compose -f $F"
else DC="docker-compose -f $F"; fi   # compose v1: hindari bug 'ContainerConfig' saat recreate
git pull --ff-only
if [ "${DC%% *}" = "docker-compose" ]; then $DC rm -sf app >/dev/null 2>&1 || true; fi
$DC up -d --build
$DC ps
