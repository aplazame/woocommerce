#!/bin/bash

set -e

# Tests and check syntax (make test)
make syntax.checker


case $DRONE_BRANCH in
    release)
        # Coding Standards dependencies
        composer create-project wp-coding-standards/wpcs:dev-master --no-dev .wpcs --no-interaction

        # psr-2 to wordpress style guide
        make style

        # Install zip package
        sudo apt-get install zip

        # Create zip package
        make zip
        ;;

    *)
        echo *$DRONE_BRANCH* pull request, all checks have passed
        ;;
esac
