# A tag correta para Alpine exige o FPM
ARG WORDPRESS_IMAGE_VERSION=php8.4-fpm-alpine
FROM wordpress:$WORDPRESS_IMAGE_VERSION


# 1. Instala o wget (para o Datadog) e o nginx (para a Azure)
RUN apk add --no-cache wget nginx

# 2. Configura o Nginx para rodar o WordPress e enviar o PHP para o FPM
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

# 3. Baixa e instala o Datadog
RUN wget https://github.com/DataDog/dd-trace-php/releases/latest/download/datadog-setup.php -O datadog-setup.php
RUN php datadog-setup.php --php-bin=all --enable-appsec

# 4. Copia seus arquivos
COPY . ./wp-content
COPY plugins/pagarme-payments-for-woocommerce/.github/data/custom.ini $PHP_INI_DIR/conf.d/

# 5. Cria um script para iniciar o PHP e o Nginx juntos
RUN printf "#!/bin/sh\nphp-fpm -D\nnginx -g 'daemon off;'\n" > /start.sh \
    && chmod +x /start.sh

# Libera a porta 80 para a Azure
EXPOSE 80

# Inicia o container executando o script
CMD ["/start.sh"]