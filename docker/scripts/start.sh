#!/usr/bin/env bash

source ./_config.sh || echo 'cannot include _config'
cd "${DOCKER_ROOT_DIR}" || exit

docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d $@
