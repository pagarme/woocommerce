FROM thiagobarradas/woocommerce:3.2.6-wp4.9.1-php7.1
MAINTAINER Open Source Team

COPY . /app/wp-content/plugins/woo-pagarme-payments
#COPY ../wordpress/wp-content/languages /app/wp-content/languages

#RUN mv /app/wp-content/plugins/woo-pagarme-payments/.htaccess /app/.htaccess

WORKDIR /app

#RUN sed -i "s/define ('WPLANG', '');/define ('WPLANG', 'pt_BR');/g" wp-config.php
