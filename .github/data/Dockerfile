ARG WORDPRESS_IMAGE_VERSION=php8.4-fpm-alpine
FROM wordpress:$WORDPRESS_IMAGE_VERSION

RUN apk add --no-cache wget nginx

# 1. Altera o usuário do Nginx e DÁ PERMISSÃO nas pastas temporárias para o www-data
RUN sed -i 's/user nginx;/user www-data;/' /etc/nginx/nginx.conf \
    && chown -R www-data:www-data /var/lib/nginx /var/log/nginx

# 2. Configura o Nginx com buffers maiores para suportar o WordPress
RUN printf "server { \n\
    listen 80; \n\
    root /var/www/html; \n\
    index index.php; \n\
    location / { try_files \$uri \$uri/ /index.php?\$args; } \n\
    location ~ \.php$ { \n\
        include fastcgi_params; \n\
        fastcgi_pass 127.0.0.1:9000; \n\
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name; \n\
        # Aumenta o limite de buffers para evitar o erro de chunked encoding \n\
        fastcgi_buffers 16 16k; \n\
        fastcgi_buffer_size 32k; \n\
    } \n\
}" > /etc/nginx/http.d/default.conf

WORKDIR /var/www/html/

RUN wget https://github.com/DataDog/dd-trace-php/releases/latest/download/datadog-setup.php -O datadog-setup.php
RUN php datadog-setup.php --php-bin=all --enable-appsec

COPY --chown=www-data:www-data . ./wp-content
COPY --chown=www-data:www-data plugins/pagarme-payments-for-woocommerce/.github/data/custom.ini $PHP_INI_DIR/conf.d/

# A INVERSÃO MÁGICA: Nginx em background, PHP-FPM em foreground com PID 1 (exec)
RUN printf "#!/bin/sh\n\
nginx\n\
exec docker-entrypoint.sh php-fpm\n\
" > /start.sh && chmod +x /start.sh

EXPOSE 80

ENTRYPOINT ["/start.sh"]