#!/usr/bin/env sh
set -ev

mkdir --parents "${HOME}/bin"
BC_CHECK_VERSION="$(curl --silent 'https://api.github.com/repos/Roave/BackwardCompatibilityCheck/releases/latest' | grep 'tag_name' | grep -o -E '[0-9\.]+')"

wget "https://github.com/Roave/BackwardCompatibilityCheck/releases/download/${BC_CHECK_VERSION}/roave-backward-compatibility-check.phar" --output-document="${HOME}/bin/bcc"
chmod u+x "${HOME}/bin/bcc"
