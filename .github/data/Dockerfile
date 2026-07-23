# A tag correta para Alpine exige o FPM
ARG WORDPRESS_IMAGE_VERSION=php8.4-fpm-alpine
FROM wordpress:$WORDPRESS_IMAGE_VERSION



WORKDIR /var/www/html/

# Alpine usa apk em vez de apt-get
RUN apk add --no-cache wget

RUN wget https://github.com/DataDog/dd-trace-php/releases/latest/download/datadog-setup.php -O datadog-setup.php

RUN php datadog-setup.php --php-bin=all --enable-appsec

COPY . ./wp-content

COPY plugins/pagarme-payments-for-woocommerce/.github/data/custom.ini $PHP_INI_DIR/conf.d/