# Swoole Chatter

> Swoole based realtime chat.

## Requirements
Docker, Docker-compose

## How to install locally

1. Add and populate .env file
--------------------------------------------------------------------------------
```bash
cp .env.example .env
```
2.  Run install script
--------------------------------------------------------------------------------
```bash
cd docker/scripts/ && ./install.sh
```
3.  In browser go to configured container ip host
--------------------------------------------------------------------------------
```
http://${CONTAINER_IP}/
```

### License

MIT © 2020
