FROM docker.io/inviqasession/cp-river-base:v9

# Apache & PHP configuration
RUN a2enmod rewrite
ADD docker/apache/vhost.conf /etc/apache2/sites-enabled/000-default.conf
ADD docker/php/php.ini /etc/php/7.0/apache2/php.ini
ADD docker/php/php.ini /etc/php/7.0/cli/php.ini

# Crontab configuration
COPY docker/cron/crontab /etc/crontab

# Add the application
ADD . /app
WORKDIR /app

# Install dependencies
ARG GITHUB_TOKEN=
ARG SYMFONY_ENV=prod
RUN if [ -n "$GITHUB_TOKEN" ]; then \
        composer config github-oauth.github.com $GITHUB_TOKEN && \
        composer install -o --no-interaction; \
        app/console assets:install; \
    fi;

# Remove cache and logs if some and fixes permissions
RUN ((rm -rf app/cache/* && rm -rf app/logs/*) || true) \
    && chown www-data . app/cache app/logs
