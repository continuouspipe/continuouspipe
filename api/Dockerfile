FROM quay.io/continuouspipe/symfony-pack:latest

ARG APP_ENV=prod

COPY ./docker/etc/ /etc/
COPY ./docker/usr/ /usr/

ADD . /app
WORKDIR /app

RUN container build

# Remove cache and logs if some and fixes permissions
RUN ((rm -rf var/cache/* && rm -rf var/logs/*) || true) \
    && chown www-data . var/ var/cache var/logs
