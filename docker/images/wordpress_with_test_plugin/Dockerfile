ARG WP_VERSION=6.4.1
ARG PHP_VERSION=8.2
ARG WEBSERVER_VERSION=apache

FROM wordpress:${WP_VERSION}-php${PHP_VERSION}-${WEBSERVER_VERSION}

COPY ./plugin_structure /var/www/html/docker_temp

#intalling wp-cli
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
RUN chmod +x wp-cli.phar
RUN mv wp-cli.phar /usr/local/bin/wp
RUN docker-php-ext-install mysqli

COPY ./spin8_init.bash /usr/local/bin/spin8_init
RUN chmod +x /usr/local/bin/spin8_init

RUN apt-get update -y && apt-get upgrade -y
RUN apt-get install unzip -y
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
