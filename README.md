# Swoole Chatter

> Swoole based realtime chat.

[![Actions Status](https://github.com/Lamperg/swoole-chatter/workflows/Integration/badge.svg?branch=dev)](https://github.com/Lamperg/swoole-chatter/actions) 
[![CodeFactor](https://www.codefactor.io/repository/github/lamperg/swoole-chatter/badge)](https://www.codefactor.io/repository/github/lamperg/swoole-chatter)

## Requirements
Docker, Docker-compose

## Local installation

1. Add and populate .env file
```bash
cp .env.example .env
```
2.  Run install script

```bash
cd docker/scripts/ && ./install.sh
```
3.  In browser go to configured container ip host
```
http://${CONTAINER_IP}/
```

### License

MIT © 2020
