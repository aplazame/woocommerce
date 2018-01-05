#!/bin/bash

if $(wp --allow-root core is-installed); then
    exit;
fi

wp --allow-root core install --skip-email --title=Aplazame --admin_user=wordpress --admin_password=wordpress --admin_email=wordpress@example.com --url=$WORDPRESS_URL \
    && wp --allow-root theme install storefront --activate \
    && wp --allow-root plugin install woocommerce --activate \
    && wp --allow-root plugin activate aplazame \
    && wp --allow-root --user=1 wc product create --name="Simple" --type="simple" --regular_price="1.00" \
    && wp --allow-root --user=1 wc product create --name="Variable" --type="variable" \
       --attributes='[ { "name":"size", "variation":"true", "options":"X|XL" } ]' \
    && wp --allow-root --user=1 wc product_variation create 5 \
       --attributes='[ { "name":"size", "option":"X" } ]' --regular_price="1.00" \
    && wp --allow-root --user=1 wc product_variation create 5 \
       --attributes='[ { "name":"size", "option":"XL" } ]' --regular_price="2.00"

echo >&2 "Complete! WordPress has been configured"
