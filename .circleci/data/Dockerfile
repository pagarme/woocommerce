FROM thiagobarradas/woocommerce:3.2.6-wp4.9.1-php7.0
MAINTAINER Embeddables Team <embeddables@mundipagg.com>

COPY . /app/wp-content/plugins/woo-mundipagg-payments
RUN mv /app/wp-content/plugins/woo-mundipagg-payments/.htaccess /app/.htaccess 

WORKDIR /app