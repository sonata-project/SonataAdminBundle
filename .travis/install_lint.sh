#!/usr/bin/env sh
set -ev

composer global require sllh/composer-lint:@stable --prefer-dist --no-interaction

gem install yaml-lint
sudo apt-get install -y libxml2-utils
