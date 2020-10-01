#!/usr/bin/env bash

source ./_config.sh
cd "${DOCKER_ROOT_DIR}" || exit

confirm
docker-compose stop
docker-compose down -v
