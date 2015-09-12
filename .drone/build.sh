#!/bin/bash

set -e

# Tests and syntax checker
# make test
make syntax.checker


case $DRONE_BRANCH in
    release)
        # Install zip package
        sudo apt-get install zip

        # Create zip package
        make zip
        ;;

    *)
        echo *$DRONE_BRANCH* pull request, all checks have passed
        ;;
esac
