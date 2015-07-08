FROM php:5.6-apache

# PHP extension
RUN apt-get update && apt-get install -y zlib1g-dev
RUN docker-php-ext-install pdo pdo_mysql zip

# Apache configuration
RUN a2enmod rewrite
ADD docker/apache-vhost.conf /etc/apache2/sites-enabled/default.conf

# PHP confiugration
ADD docker/php.ini /usr/local/etc/php/php.ini

ADD . /app/apps/river
WORKDIR /app/apps/river
