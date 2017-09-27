FROM docker.io/inviqasession/cp-river-base:v12

COPY ./docker/etc/ /etc/
COPY ./docker/usr/ /usr/

# Add the application
ADD . /app
WORKDIR /app

# Install dependencies
ARG GITHUB_TOKEN=
ARG SYMFONY_ENV=prod
RUN if [ -n "$GITHUB_TOKEN" ]; then \
        composer config github-oauth.github.com $GITHUB_TOKEN && \
        composer install -o --no-interaction; \
        bin/console assets:install; \
    fi;

# Remove cache and logs if some and fixes permissions
RUN ((rm -rf var/cache/* && rm -rf var/logs/*) || true) \
    && chown www-data . var/cache var/logs
