FROM php:5.6-apache

# PHP extension
RUN apt-get update && apt-get install -y zlib1g-dev libpq-dev git
RUN docker-php-ext-install pdo pdo_pgsql zip mbstring bcmath

# Apache configuration
RUN a2enmod rewrite
ADD docker/apache-vhost.conf /etc/apache2/sites-enabled/default.conf

# PHP confiugration
ADD docker/php.ini /usr/local/etc/php/php.ini

# Install composer
RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/bin/composer

# Add the application
ADD . /app
WORKDIR /app
RUN rm -rf app/cache/* && rm -rf app/logs/*

# Set image endpoint
RUN chmod +x /app/docker/run.sh
CMD ["/app/docker/run.sh"]

# Copy installer
ADD docker/install-dependencies.sh /install-dependencies.sh
RUN chmod +x /install-dependencies.sh

# Run installation with private credentials
#~continuous-pipe:inject-environment~#
RUN /install-dependencies.sh
