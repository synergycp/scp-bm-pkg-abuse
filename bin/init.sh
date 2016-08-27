#!/bin/bash

DIR=$(pwd)
MAIN_DIR=$1
MODE=$2
REL_DIR=${DIR:${#MAIN_DIR}+1}

case $MODE in
1)
    composer install
    ;;
2)
    cd $MAIN_DIR
    php artisan migrate -f --path=$REL_DIR

    cd $DIR/admin
    npm install
    bower install
    gulp prod build --nocache

    cd $DIR/client
    npm install
    bower install
    gulp prod build --nocache

    cd $DIR
    ;;
esac
