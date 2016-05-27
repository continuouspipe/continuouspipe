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

RUN version=$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;") \
    && curl -A "Docker" -o /tmp/blackfire-probe.tar.gz -D - -L -s https://blackfire.io/api/v1/releases/probe/php/linux/amd64/$version \
    && tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp \
    && mv /tmp/blackfire-*.so $(php -r "echo ini_get('extension_dir');")/blackfire.so \
    && echo "extension=blackfire.so\nblackfire.agent_socket=tcp://blackfire:8707" > $PHP_INI_DIR/conf.d/blackfire.ini

# Install rabbitmq-cli-consumer
RUN wget https://github.com/ricbra/rabbitmq-cli-consumer/releases/download/1.1.0/rabbitmq-cli-consumer-linux-amd64.tar.gz \
    && tar -xzf rabbitmq-cli-consumer-linux-amd64.tar.gz \
    && mv rabbitmq-cli-consumer /usr/bin/rabbitmq-cli-consumer \
    && chmod +x /usr/bin/rabbitmq-cli-consumer \
    && rm -rf rabbitmq-cli-consumer-linux-amd64.tar.gz

# Install composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/bin/composer
