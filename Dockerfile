FROM wordpress

RUN curl -o /usr/local/bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x /usr/local/bin/wp

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_connect_back=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.default_enable=0" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.coverage_enable=0" >> /usr/local/etc/php/conf.d/xdebug.ini

ADD ./.ci/ /
RUN chmod +x /*.sh

ENTRYPOINT ["/new-entrypoint.sh"]
CMD ["apache2-foreground"]
