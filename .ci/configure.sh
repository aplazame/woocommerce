#!/bin/bash

if $(wp --allow-root core is-installed); then
    exit;
fi

wp --allow-root core install --skip-email --title=Aplazame --admin_user=wordpress --admin_password=wordpress --admin_email=wordpress@example.com --url=$WORDPRESS_URL \
    && wp --allow-root theme install storefront --activate \
    && wp --allow-root plugin install woocommerce --activate \
    && wp --allow-root plugin activate aplazame

echo >&2 "Complete! WordPress has been configured"
