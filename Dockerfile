FROM thiagobarradas/woocommerce:3.5.3-wp5.0.2-php7.2
MAINTAINER Open Source Team

COPY . /app/wp-content/plugins/pagarme-payments-for-woocommerce
WORKDIR /app
