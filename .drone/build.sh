#!/bin/bash

set -e

# Tests and check syntax
make syntax.checker
# make test


case $DRONE_BRANCH in
    release)
        # Install zip package
        sudo apt-get install zip        

        # Create wild-style package
        make zip s3.path=wild-style/

        # Coding Standards dependencies
        composer create-project wp-coding-standards/wpcs:dev-master\
            .wpcs --no-dev --no-interaction

        # psr-2 to wordpress style guide
        make style

        # Create dirty package
        make zip
        ;;

    *)
        echo *$DRONE_BRANCH* pull request, all checks have passed
        ;;
esac
