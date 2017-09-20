#!/usr/bin/env bash
set -ev

if [ "$TRAVIS_PHP_VERSION" != "hhvm" ] && [ "$TRAVIS_PHP_VERSION" '<' '5.4' ]; then
    PHP_INI_DIR="$HOME/.phpenv/versions/$(phpenv version-name)/etc/conf.d/"
    XDEBUG_INI_FILE="/tmp/xdebug.ini"
    if [ -f  "$XDEBUG_INI_FILE" ]; then
        mv "$XDEBUG_INI_FILE" "$PHP_INI_DIR"
    fi
fi
