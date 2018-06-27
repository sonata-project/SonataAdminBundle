#!/usr/bin/env sh
set -ev

PHP_INI_DIR="$HOME/.phpenv/versions/$(phpenv version-name)/etc/conf.d/"
TRAVIS_INI_FILE="$PHP_INI_DIR/travis.ini"
echo "memory_limit=3072M" >> "$TRAVIS_INI_FILE"



sed --in-place "s/\"dev-master\":/\"dev-${TRAVIS_COMMIT}\":/" composer.json

# symfony/maker-bundle only works with PHP 7 and higher
if [ "$TRAVIS_PHP_VERSION" '>' '7.0' ]; then
    # but only with Symfony 3.4 and higher
    
    if [ "$SYMFONY" != "" ]; then 
        if [ "$SYMFONY" '>=' '3.4' ] ; then
            composer require "symfony/maker-bundle:1" --no-update
        fi
    else
        composer require "symfony/maker-bundle:1" --no-update
    fi
fi

if [ "$SYMFONY" != "" ]; then composer require "symfony/symfony:$SYMFONY" --no-update; fi;
if [ "$SONATA_CORE" != "" ]; then composer require "sonata-project/core-bundle:$SONATA_CORE" --no-update; fi;
if [ "$SONATA_BLOCK" != "" ]; then composer require "sonata-project/block-bundle:$SONATA_BLOCK" --no-update; fi;
