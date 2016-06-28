#!/bin/bash

/entrypoint.sh php-fpm || /configure.sh

exec "$@"
