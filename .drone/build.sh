#!/bin/bash

set -e

# Tests and check syntax (make test)
make syntax.checker


case $DRONE_BRANCH in
    release)
        # s3 package folder
        mkdir -p .s3/wild-style

        # Create wild-style package
        make zip s3.path=wild-style/

        # Coding Standards dependencies
        composer create-project wp-coding-standards/wpcs:dev-master --no-dev .wpcs --no-interaction

        # psr-2 to wordpress style guide
        make style

        # Install zip package
        sudo apt-get install zip

        # Create dirty package
        make zip
        ;;

    *)
        echo *$DRONE_BRANCH* pull request, all checks have passed
        ;;
esac
