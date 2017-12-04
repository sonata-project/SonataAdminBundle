#!/usr/bin/env sh
set -ev

mkdir --parents "${HOME}/bin"

wget "http://cs.sensiolabs.org/download/php-cs-fixer-v2.phar" --output-document="${HOME}/bin/php-cs-fixer"
chmod u+x "${HOME}/bin/php-cs-fixer"

composer global require sllh/composer-lint:@stable --prefer-dist --no-interaction

gem install yaml-lint
