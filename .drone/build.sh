#!/bin/bash

set -e

# check syntax and pass the tests
make syntax.checker
# make test


case $DRONE_BRANCH in
    release)
        # install zip package
        sudo apt-get install zip        

        # create wild-style package
        make zip s3.path=wild-style/

        # psr-2 to wordpress style guide
        make style.req style

        # create dirty package
        make zip
        ;;

    *)
        echo *$DRONE_BRANCH* pull request, all checks have passed
        ;;
esac
