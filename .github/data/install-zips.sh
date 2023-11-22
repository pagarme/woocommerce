#!/bin/bash

while getopts w:f:m: flag
do
  case "${flag}" in
    w) wcVersion=$OPTARG;;
    f) wcExtraCheckoutFieldsVersion=$OPTARG;;
    m) wpMailCatcherVersion=$OPTARG;;
  esac
done

installZip() {
    curl -L $1 -o $2
    unzip $2 -d $3
    rm $2
}

latestStable="latest-stable"
pluginsDir="plugins"
themesDir="themes"

wordpressDownloadUrl="https://downloads.wordpress.org"

if [[ -z "$wcVersion" ]]; then
    wcVersion=$latestStable
fi

if [[ -z "$wcExtraCheckoutFieldsVersion" ]]; then
    wcExtraCheckoutFieldsVersion=$latestStable
fi

if [[ -z "$wpMailCatcherVersion" ]]; then
    wpMailCatcherVersion=$latestStable
fi


installZip "$wordpressDownloadUrl/plugin/woocommerce.$wcVersion.zip" "woocommerce.zip" "$pluginsDir"
installZip "$wordpressDownloadUrl/plugin/woocommerce-extra-checkout-fields-for-brazil.$wcExtraCheckoutFieldsVersion.zip" "woocommerce-extra-checkout-fields-for-brazil.zip" "$pluginsDir"
installZip "$wordpressDownloadUrl/plugin/wp-mail-catcher.$wpMailCatcherVersion.zip" "wp-mail-catcher.zip" "$pluginsDir"
installZip "$wordpressDownloadUrl/theme/storefront.$latestStable.zip" "storefront.zip" "$themesDir"
