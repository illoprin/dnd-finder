#/bin/bash

DOMAIN="dnd-finder.ru"
EMAIL="Iljya.Kunsevich@yandex.ru"

docker stop $(docker ps -a -q)

docker compose -f docker-compose.certbot.yaml up -d

sleep 3

certbot certonly -w /var/www/certbot -d $DOMAIN --email $EMAIL

sleep 3

docker compose -f docker-compose.certbot.yaml down

sleep 3

docker compose -f docker-compose.prod.yaml up -d
