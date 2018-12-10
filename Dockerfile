FROM wordpress:5-php7.2

RUN curl -o /usr/local/bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x /usr/local/bin/wp

ENV XDEBUG_CONFIG="remote_enable=on remote_connect_back=on"

RUN pecl install xdebug-2.6.1 \
    && docker-php-ext-enable xdebug

ADD ./.ci/ /
RUN chmod +x /*.sh

ENTRYPOINT ["/new-entrypoint.sh"]
CMD ["apache2-foreground"]
