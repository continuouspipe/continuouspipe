FROM php:5.6-apache

# PHP extension
RUN apt-get update && apt-get install -y zlib1g-dev libpq-dev git
RUN docker-php-ext-install pdo pdo_pgsql zip mbstring

# Apache configuration
RUN a2enmod rewrite
ADD apps/river/docker/apache-vhost.conf /etc/apache2/sites-enabled/default.conf

# PHP confiugration
ADD apps/river/docker/php.ini /usr/local/etc/php/php.ini

# Install composer
RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/bin/composer

# Add the application
ADD . /app
WORKDIR /app

# Install main dependencies
RUN composer install -o --no-dev

# Install application dependencies
WORKDIR /app/apps/river

# Workarround waiting a better solution than conductor
RUN composer update -o

CMD /app/apps/river/docker/run.sh
