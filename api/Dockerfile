FROM php:5.6-apache

# PHP extension
RUN requirements="zlib1g-dev libicu-dev libmcrypt-dev git wget" \
    && apt-get update && apt-get install -y $requirements && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install mbstring \
    && docker-php-ext-install intl \
    && docker-php-ext-install zip \
    && docker-php-ext-install opcache \
    && docker-php-ext-install bcmath \
    && docker-php-ext-install mcrypt \
    && pecl install apcu-4.0.8 && echo extension=apcu.so > /usr/local/etc/php/conf.d/apcu.ini \
    && apt-get purge --auto-remove -y

# Install rabbitmq-cli-consumer
RUN wget https://github.com/ricbra/rabbitmq-cli-consumer/releases/download/1.1.0/rabbitmq-cli-consumer-linux-amd64.tar.gz \
    && tar -xzf rabbitmq-cli-consumer-linux-amd64.tar.gz \
    && mv rabbitmq-cli-consumer /usr/bin/rabbitmq-cli-consumer \
    && chmod +x /usr/bin/rabbitmq-cli-consumer \
    && rm -rf rabbitmq-cli-consumer-linux-amd64.tar.gz

# Apache & PHP configuration
RUN a2enmod rewrite
ADD docker/apache-vhost.conf /etc/apache2/sites-enabled/default.conf
ADD docker/php.ini /usr/local/etc/php/php.ini

# Install composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/bin/composer

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
    && app/console assets:install

# Remove cache and logs if some and fixes permissions
RUN ((rm -rf app/cache/* && rm -rf app/logs/*) || true) \
    && chown www-data . app/cache app/logs
