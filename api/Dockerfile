FROM docker.io/inviqasession/cp-river-base:v6

# Apache & PHP configuration
RUN a2enmod rewrite
ADD docker/apache-vhost.conf /etc/apache2/sites-enabled/default.conf
ADD docker/php.ini /usr/local/etc/php/php.ini

# Add the application
ADD . /app
WORKDIR /app

# Install dependencies
ARG GITHUB_TOKEN=
ARG SYMFONY_ENV=prod
RUN if [ -n "$GITHUB_TOKEN" ]; then \
        composer config github-oauth.github.com $GITHUB_TOKEN && \
        composer install -o --no-interaction; \
    fi; \
    app/console assets:install

# Remove cache and logs if some and fixes permissions
RUN ((rm -rf app/cache/* && rm -rf app/logs/*) || true) \
    && chown www-data . app/cache app/logs
