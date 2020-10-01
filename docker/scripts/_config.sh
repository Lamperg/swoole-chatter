#!/usr/bin/env bash

#=============================================================================
# Variables
#=============================================================================
cd ../../
DOCKER_ROOT_DIR=$(pwd)
DOCKER_ASSETS_DIR="${DOCKER_ROOT_DIR}/docker"
DOCKER_SCRIPTS_DIR="${DOCKER_ROOT_DIR}/docker/scripts"

COLOR_RED="\033[31m"
COLOR_BLUE="\033[34m"
COLOR_DEFAULT="\033[0m"
#=============================================================================
# OS checking functions
#=============================================================================
is_linux() {
    if uname -a | grep -i "linux" > /dev/null;
    then
        debug "Linux system detected"
        true
    else
        false
    fi
}
#=============================================================================
# Print functions
#=============================================================================
error() {
    echo -e "${COLOR_RED}$1${COLOR_DEFAULT}"
}
debug () {
    echo -e "${COLOR_BLUE}$1${COLOR_DEFAULT}"
}
#=============================================================================
# Helper functions
#=============================================================================
confirm() {
while true; do
    read -p "$(echo -e "${COLOR_RED}""Are you sure? [y/N] ""${COLOR_DEFAULT}")" yn
    case $yn in
        [Yy]* ) break;;
        [Nn]* ) exit;;
        * ) echo "Please answer yes or no.";;
    esac
done
}
