#!/bin/sh
set -e

WP_URL="${WP_URL:-http://woo.localhost}"
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

CURRENT_URL=$(wp option get siteurl)
if [ "$CURRENT_URL" != "$WP_URL" ]; then
    echo "[init-wp] Atualizando siteurl/home: '$CURRENT_URL' -> '$WP_URL'"
    wp option update siteurl "$WP_URL"
    wp option update home "$WP_URL"
fi

PERMALINK_STRUCTURE="${WP_PERMALINK_STRUCTURE:-/%postname%/}"
CURRENT_PERMALINK=$(wp option get permalink_structure 2>/dev/null || echo "")
if [ "$CURRENT_PERMALINK" != "$PERMALINK_STRUCTURE" ]; then
    echo "[init-wp] Configurando permalinks para '$PERMALINK_STRUCTURE'..."
    wp rewrite structure "$PERMALINK_STRUCTURE" --hard
fi
# wp-cli sidecar roda em PHP CLI, entao got_mod_rewrite() retorna false e o
# .htaccess fica vazio entre os marcadores. Forcamos via filtro got_rewrite.
echo "[init-wp] Gravando regras de rewrite em .htaccess..."
wp eval 'add_filter("got_rewrite", "__return_true"); flush_rewrite_rules(true);'

echo "[init-wp] Instalando/atualizando WooCommerce (latest)..."
wp plugin install woocommerce --activate

if ! wp plugin is-active woocommerce 2>/dev/null; then
    wp plugin activate woocommerce
fi

TARGET_LOCALE="${WP_LOCALE:-pt_BR}"
CURRENT_LOCALE=$(wp option get WPLANG 2>/dev/null || echo "")
if [ "$CURRENT_LOCALE" != "$TARGET_LOCALE" ]; then
    echo "[init-wp] Instalando/ativando locale $TARGET_LOCALE..."
    wp language core install "$TARGET_LOCALE" --activate
fi
wp language plugin install woocommerce "$TARGET_LOCALE" 2>/dev/null || true
wp language plugin install woo-pagarme-payments "$TARGET_LOCALE" 2>/dev/null || true

CURRENT_CURRENCY=$(wp option get woocommerce_currency 2>/dev/null || echo "")
if [ "$CURRENT_CURRENCY" != "BRL" ]; then
    echo "[init-wp] Configurando moeda para BRL (Real)..."
    wp option update woocommerce_currency BRL
    wp option update woocommerce_currency_pos left_space
    wp option update woocommerce_price_thousand_sep "."
    wp option update woocommerce_price_decimal_sep ","
    wp option update woocommerce_price_num_decimals 2
fi

CURRENT_COUNTRY=$(wp option get woocommerce_default_country 2>/dev/null || echo "")
case "$CURRENT_COUNTRY" in
    BR|BR:*) ;;
    *)
        echo "[init-wp] Configurando pais padrao para BR:SP..."
        wp option update woocommerce_default_country "BR:SP"
        ;;
esac

if ! wp theme is-active storefront 2>/dev/null; then
    echo "[init-wp] Instalando/ativando tema storefront..."
    wp theme install storefront --activate
    wp language theme install storefront "$TARGET_LOCALE" 2>/dev/null || true
else
    echo "[init-wp] Tema storefront ja ativo."
fi

SHOP_PAGE_ID=$(wp option get woocommerce_shop_page_id 2>/dev/null || echo 0)
if [ -z "$SHOP_PAGE_ID" ] || [ "$SHOP_PAGE_ID" = "0" ]; then
    echo "[init-wp] Pagina Shop nao encontrada - rodando install_pages do WC..."
    wp wc tool run install_pages --user=1 2>/dev/null || true
    SHOP_PAGE_ID=$(wp option get woocommerce_shop_page_id 2>/dev/null || echo 0)
fi

if [ -n "$SHOP_PAGE_ID" ] && [ "$SHOP_PAGE_ID" != "0" ]; then
    CURRENT_FRONT=$(wp option get page_on_front 2>/dev/null || echo 0)
    if [ "$CURRENT_FRONT" != "$SHOP_PAGE_ID" ]; then
        echo "[init-wp] Definindo Shop (id=$SHOP_PAGE_ID) como pagina inicial..."
        wp option update show_on_front page
        wp option update page_on_front "$SHOP_PAGE_ID"
    else
        echo "[init-wp] Shop ja eh a pagina inicial."
    fi
else
    echo "[init-wp] AVISO: nao foi possivel localizar a pagina Shop."
fi

PRODUCT_COUNT=$(wp post list --post_type=product --format=count 2>/dev/null || echo 0)
if [ "$PRODUCT_COUNT" -eq 0 ]; then
    echo "[init-wp] Importando produtos de exemplo do WooCommerce..."
    wp plugin install wordpress-importer --activate
    SAMPLE_XML="/var/www/html/wp-content/plugins/woocommerce/sample-data/sample_products.xml"
    if [ -f "$SAMPLE_XML" ]; then
        wp import "$SAMPLE_XML" --authors=skip || echo "[init-wp] (import terminou com avisos)"
    else
        echo "[init-wp] AVISO: $SAMPLE_XML nao encontrado - sample nao importado."
    fi
else
    echo "[init-wp] Ja existem $PRODUCT_COUNT produtos - pulando import de sample."
fi

if [ -d /var/www/html/wp-content/plugins/woo-pagarme-payments ]; then
    if ! wp plugin is-active woo-pagarme-payments 2>/dev/null; then
        echo "[init-wp] Ativando woo-pagarme-payments..."
        wp plugin activate woo-pagarme-payments \
            || echo "[init-wp] (ativacao falhou - rode 'make install' para instalar deps via composer)"
    else
        echo "[init-wp] woo-pagarme-payments ja ativo."
    fi
fi

echo "[init-wp] OK. WordPress disponivel em $WP_URL"
