#!/usr/bin/env bash
set -ev

PHP_INI_DIR="$HOME/.phpenv/versions/$(phpenv version-name)/etc/conf.d/"
TRAVIS_INI_FILE="$PHP_INI_DIR/travis.ini"
echo "memory_limit=3072M" >> "$TRAVIS_INI_FILE"


sed --in-place "s/\"dev-master\":/\"dev-${TRAVIS_COMMIT}\":/" composer.json

    if [ "$SYMFONY" != "" ]; then composer require "symfony/symfony:$SYMFONY" --no-update; fi;
        if [ "$SONATA_CORE" != "" ]; then composer require "sonata-project/core-bundle:$SONATA_CORE" --no-update; fi;
        if [ "$SONATA_BLOCK" != "" ]; then composer require "sonata-project/block-bundle:$SONATA_BLOCK" --no-update; fi;
        # TODO: remove when dropping sf < 3.4 support
if [[ -z "${SYMFONY}" || ("${SYMFONY:0:3}" != "2.8" && "${SYMFONY:0:3}" != "3.3") ]]; then
    composer require "symfony/maker-bundle:${SYMFONY_MAKER:=1.7}" --no-update
fi
    