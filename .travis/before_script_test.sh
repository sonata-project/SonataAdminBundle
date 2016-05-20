#!/usr/bin/env sh
set -ev

if [ "$TRAVIS_PHP_VERSION" != "hhvm" ]; then
    mv /tmp/xdebug.ini "$HOME/.phpenv/versions/$(phpenv version-name)/etc/conf.d"
fi
