#!/bin/sh
set -e

WP_URL="${WP_URL:-http://localhost:8080}"
WP_TITLE="${WP_TITLE:-WooCommerce Pagar.me Dev}"
WP_ADMIN_USER="${WP_ADMIN_USER:-admin}"
WP_ADMIN_PASSWORD="${WP_ADMIN_PASSWORD:-admin}"
WP_ADMIN_EMAIL="${WP_ADMIN_EMAIL:-admin@example.com}"

echo "[init-wp] Aguardando arquivos do WordPress..."
i=0
until [ -f /var/www/html/wp-load.php ]; do
    i=$((i+1))
    if [ $i -gt 60 ]; then
        echo "[init-wp] ERRO: wp-load.php nao apareceu em 60s" >&2
        exit 1
    fi
    sleep 1
done

echo "[init-wp] Aguardando banco de dados..."
i=0
until wp db check --quiet 2>/dev/null; do
    i=$((i+1))
    if [ $i -gt 60 ]; then
        echo "[init-wp] ERRO: banco nao respondeu em 60s" >&2
        exit 1
    fi
    sleep 1
done

if wp core is-installed --quiet 2>/dev/null; then
    echo "[init-wp] WordPress ja instalado."
else
    echo "[init-wp] Instalando WordPress em $WP_URL ..."
    wp core install \
        --url="$WP_URL" \
        --title="$WP_TITLE" \
        --admin_user="$WP_ADMIN_USER" \
        --admin_password="$WP_ADMIN_PASSWORD" \
        --admin_email="$WP_ADMIN_EMAIL" \
        --skip-email
fi

echo "[init-wp] Instalando/atualizando WooCommerce (latest)..."
wp plugin install woocommerce --activate

if [ -d /var/www/html/wp-content/plugins/woo-pagarme-payments ]; then
    if ! wp plugin is-active woo-pagarme-payments 2>/dev/null; then
        echo "[init-wp] Ativando woo-pagarme-payments..."
        wp plugin activate woo-pagarme-payments || echo "[init-wp] (ativacao falhou - rode 'make install' para instalar deps via composer)"
    else
        echo "[init-wp] woo-pagarme-payments ja ativo."
    fi
fi

echo "[init-wp] OK."
