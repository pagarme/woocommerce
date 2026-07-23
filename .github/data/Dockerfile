ARG WORDPRESS_IMAGE_VERSION=php8.4-alpine
FROM wordpress:$WORDPRESS_IMAGE_VERSION

# A instrução MAINTAINER foi descontinuada no Docker, substituída por LABEL
LABEL maintainer="Open Source Team"

WORKDIR /var/www/html/

# Instalação do wget usando apk (o gerenciador de pacotes do Alpine)
RUN apk add --no-cache wget

RUN wget https://github.com/DataDog/dd-trace-php/releases/latest/download/datadog-setup.php -O datadog-setup.php

RUN php datadog-setup.php --php-bin=all --enable-appsec

COPY . ./wp-content

COPY plugins/pagarme-payments-for-woocommerce/.github/data/custom.ini $PHP_INI_DIR/conf.d/