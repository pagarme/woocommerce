FROM wordpress:php8.4-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        zip \
        unzip \
        less \
        vim \
        default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY php-dev.ini /usr/local/etc/php/conf.d/zz-dev.ini
COPY xdebug.ini /usr/local/etc/php/conf.d/zz-xdebug.ini

RUN a2enmod rewrite

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_MEMORY_LIMIT=-1
