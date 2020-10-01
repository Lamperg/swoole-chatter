#!/usr/bin/env bash

source ./_config.sh
cd "${DOCKER_ROOT_DIR}" || exit

confirm

bash ./_stop-all.sh
docker system prune
