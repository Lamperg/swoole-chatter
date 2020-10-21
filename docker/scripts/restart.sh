#!/usr/bin/env bash

# clear app logs before restarting
sudo bash ./_logs-clean.sh swoole-chatter-app

# move to docker-root folder
source ./_config.sh || echo 'cannot include _config'
cd "${DOCKER_ROOT_DIR}" || exit

# restart app service
docker-compose restart app

debug "Start swoole server"
docker-compose exec --user www app php index.php
