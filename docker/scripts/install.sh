#!/usr/bin/env bash

source ./_config.sh
cd "${DOCKER_ROOT_DIR}" || exit

confirm

# Build container (you can use '--no-cache' and '--force-recreate' params)
#=============================================================================
docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d --build $@

# Install dependencies
#=============================================================================
docker-compose exec --user www app composer install
docker-compose exec --user www app npm install


# Build frontend
#=============================================================================
docker-compose exec --user www app npm run dev
