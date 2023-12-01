ARG WORDPRESS_IMAGE_VERSION=latest
FROM wordpress:$WORDPRESS_IMAGE_VERSION
MAINTAINER Open Source Team

WORKDIR /var/www/html/

RUN apt-get update \
    && apt-get install wget -y \
    && apt-get clean

RUN wget https://github.com/DataDog/dd-trace-php/releases/latest/download/datadog-setup.php -O datadog-setup.php

RUN php datadog-setup.php --php-bin=all --enable-appsec

COPY . ./wp-content

COPY plugins/pagarme-payments-for-woocommerce/.github/data/custom.ini $PHP_INI_DIR/conf.d/
