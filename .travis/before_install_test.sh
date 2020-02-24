#!/usr/bin/env bash
set -ev

sed --in-place "s/\"dev-master\":/\"dev-${GITHUB_SHA}\":/" composer.json

if [ "$SYMFONY" != "" ]; then composer require "symfony/symfony:$SYMFONY" --no-update; fi;
if [ "$SONATA_CORE" != "" ]; then composer require "sonata-project/core-bundle:$SONATA_CORE" --no-update; fi;
if [ "$SONATA_BLOCK" != "" ]; then composer require "sonata-project/block-bundle:$SONATA_BLOCK" --no-update; fi;
