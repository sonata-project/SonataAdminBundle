#!/usr/bin/env sh
set -ev

mkdir --parents "${HOME}/bin"

# PHPUnit install
if [ ${TRAVIS_PHP_VERSION} '<' '5.6' ]; then
    PHPUNIT_PHAR=phpunit-old.phar
else
    PHPUNIT_PHAR=phpunit.phar
fi
wget "https://phar.phpunit.de/${PHPUNIT_PHAR}" --output-document="${HOME}/bin/phpunit"
chmod u+x "${HOME}/bin/phpunit"

# Coveralls client install
wget https://github.com/satooshi/php-coveralls/releases/download/v1.0.1/coveralls.phar --output-document="${HOME}/bin/coveralls"
chmod u+x "${HOME}/bin/coveralls"

# To be removed when this issue will be resolved: https://github.com/composer/composer/issues/5355
if [ "${COMPOSER_FLAGS}" = '--prefer-lowest' ]; then
    composer update --prefer-dist --no-interaction --prefer-stable --quiet
fi
composer update --prefer-dist --no-interaction --prefer-stable ${COMPOSER_FLAGS}
