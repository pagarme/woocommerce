ARG WORDPRESS_IMAGE_VERSION=php8.4-fpm-alpine
FROM wordpress:$WORDPRESS_IMAGE_VERSION

RUN apk add --no-cache wget nginx

# 1. Ajusta o Nginx para rodar com o mesmo usuário do WordPress (www-data)
# Isso evita o 403 Forbidden por falta de permissão de leitura
RUN sed -i 's/user nginx;/user www-data;/' /etc/nginx/nginx.conf

RUN printf "server { \n\
    listen 80; \n\
    root /var/www/html; \n\
    index index.php; \n\
    location / { try_files \$uri \$uri/ /index.php?\$args; } \n\
    location ~ \.php$ { \n\
        include fastcgi_params; \n\
        fastcgi_pass 127.0.0.1:9000; \n\
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name; \n\
    } \n\
}" > /etc/nginx/http.d/default.conf

WORKDIR /var/www/html/

RUN wget https://github.com/DataDog/dd-trace-php/releases/latest/download/datadog-setup.php -O datadog-setup.php
RUN php datadog-setup.php --php-bin=all --enable-appsec

# 2. Copia os arquivos já passando a posse para o www-data
COPY --chown=www-data:www-data . ./wp-content
COPY --chown=www-data:www-data plugins/pagarme-payments-for-woocommerce/.github/data/custom.ini $PHP_INI_DIR/conf.d/

# 3. Corrige a inicialização: chama o script oficial do WP e depois o Nginx
RUN printf "#!/bin/sh\n\
# Roda o entrypoint oficial em background para copiar o index.php e iniciar o PHP\n\
docker-entrypoint.sh php-fpm &\n\
# Inicia o nginx em foreground para manter o container vivo\n\
nginx -g 'daemon off;'\n\
" > /start.sh && chmod +x /start.sh

EXPOSE 80

# Substitui o entrypoint padrão para o nosso script assumir o controle total
ENTRYPOINT ["/start.sh"]