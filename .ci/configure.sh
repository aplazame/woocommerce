#!/bin/bash

if $(wp --allow-root core is-installed); then
    exit;
fi

wp --allow-root core install --skip-email --title=Aplazame --admin_user=wordpress --admin_password=wordpress --admin_email=wordpress@example.com --url=$WORDPRESS_URL \
    && wp --allow-root theme install storefront --activate \
    && wp --allow-root plugin install woocommerce --activate \
    && wp --allow-root plugin activate aplazame \
    && wp --allow-root wc product create --title="Simple" --regular_price=1 \
    && wp --allow-root wc product create --title="Variable" --type="variable" \
       --attributes.0.name="size" --attributes.0.variation="true" --attributes.0.options="X|XL" \
       --variations.0.attributes.size="X" --variations.0.regular_price=1 \
       --variations.1.attributes.size="XL" --variations.1.regular_price=2

echo >&2 "Complete! WordPress has been configured"
