#!/bin/bash

DIR=$(pwd)
MAIN_DIR=$1
MODE=$2

case $MODE in
1)
    composer install
    ;;
2)
    php $MAIN_DIR/artisan migrate --path=$DIR/database/migrations

    cd admin
    npm install
    bower install
    gulp

    cd ../client
    npm install
    bower install
    gulp

    cd ..
    ;;
esac

