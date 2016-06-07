#!/usr/bin/env sh
set -ev

if [ "${TRAVIS_PHP_VERSION}" != "hhvm" ]; then
    mv "$HOME/.phpenv/versions/$(phpenv version-name)/etc/conf.d/xdebug.ini" /tmp
    echo "memory_limit=3072M" >> ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/travis.ini

    if [ ${TRAVIS_PHP_VERSION} '<' '7.0' ]; then
        echo "extension=mongo.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/travis.ini
    fi
fi

# To be removed when following PR will be merged: https://github.com/travis-ci/travis-build/pull/718
composer self-update --stable
sed --in-place "s/\"dev-master\":/\"dev-${TRAVIS_COMMIT}\":/" composer.json

if [ "$SYMFONY" != "" ]; then composer require "symfony/symfony:$SYMFONY" --no-update; fi;
if [ "$SONATA_CORE" != "" ]; then composer require "sonata-project/core-bundle:$SONATA_CORE" --no-update; fi;
if [ "$SONATA_BLOCK" != "" ]; then composer require "sonata-project/block-bundle:$SONATA_BLOCK" --no-update; fi;
