#!/usr/bin/env bash

source ../_config.sh
cd "${DOCKER_ROOT_DIR}" || exit

docker-compose exec --user www app bash $@
