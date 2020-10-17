#!/usr/bin/env bash

# cleans logs for provided container (by name/id)
truncate -s 0 "$(docker inspect --format='{{.LogPath}}' "$@")"
