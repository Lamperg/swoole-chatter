#!/usr/bin/env bash

source ./_config.sh
cd "${DOCKER_ROOT_DIR}" || exit

confirm

# Build container (you can use '--no-cache' and '--force-recreate' params)
#=============================================================================
debug "Build container"
docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d --build $@

# Install dependencies
#=============================================================================
debug "Install dependencies"
docker-compose exec --user www app composer install --prefer-dist --no-suggest
docker-compose exec --user www app npm install

# Start server
#=============================================================================
debug "Start swoole server"
docker-compose exec --user www app php index.php

# Build frontend
#=============================================================================
debug "Build frontend"
docker-compose exec --user www app npm run dev


# Run DB migration
#=============================================================================
debug "Run DB migrations"
sleep 5
docker-compose exec db bash install.sh
