FROM quay.io/continuouspipe/symfony-php7.1-nginx:stable

MAINTAINER Samuel ROZE <samuel.roze@gmail.com>

# Install base packages
RUN apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get -yq install \
    git \
    cron \
    rsyslog \
    mysql-client \
    wget && \
    composer global require hirak/prestissimo \

    && \


# Install tideways' extension
    echo 'deb http://s3-eu-west-1.amazonaws.com/qafoo-profiler/packages debian main' > /etc/apt/sources.list.d/tideways.list && \
    curl -sS https://s3-eu-west-1.amazonaws.com/qafoo-profiler/packages/EEB5E8F4.gpg | apt-key add - && \
    apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get -yq install tideways-php \

    && \

# Install Blackfire extension
    version=$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;") && \
    curl -A "Docker" -o /tmp/blackfire-probe.tar.gz -D - -L -s https://blackfire.io/api/v1/releases/probe/php/linux/amd64/$version && \
    tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp && \
    mv /tmp/blackfire-*.so $(php -r "echo ini_get('extension_dir');")/blackfire.so && \
    echo "extension=blackfire.so\nblackfire.agent_socket=tcp://blackfire:8707" > /etc/php/7.0/mods-available/blackfire.ini \

    && \

# Install rabbitmq-cli-consumer
    wget https://github.com/ricbra/rabbitmq-cli-consumer/releases/download/1.4.2/rabbitmq-cli-consumer-linux-amd64.tar.gz && \
    tar -xzf rabbitmq-cli-consumer-linux-amd64.tar.gz && \
    mv rabbitmq-cli-consumer /usr/bin/rabbitmq-cli-consumer && \
    chmod +x /usr/bin/rabbitmq-cli-consumer && \
    rm -rf rabbitmq-cli-consumer-linux-amd64.tar.gz \

    && \

# Clean the image
    apt-get remove -y php7.0-dev pkg-config libmagickwand-dev build-essential && \
    apt-get auto-remove -y && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
