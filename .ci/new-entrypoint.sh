#!/bin/bash

/usr/local/bin/docker-entrypoint.sh php-fpm || /configure.sh

exec "$@"
