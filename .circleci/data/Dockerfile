FROM thiagobarradas/woocommerce:3.2.6-wp4.9.1-php7.1
MAINTAINER Open Source Team

COPY . /app/wp-content/plugins/woo-pagarme-payments
RUN mv /app/wp-content/plugins/woo-pagarme-payments/.htaccess /app/.htaccess

WORKDIR /app
