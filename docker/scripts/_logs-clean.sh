#!/usr/bin/env bash

# cleans logs for provided container (by name/id)
sudo truncate -s 0 $(docker inspect --format='{{.LogPath}}' <container_name_or_id>)
