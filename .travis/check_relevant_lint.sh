#!/usr/bin/env bash
set -ev

RELEVANT_FILES=$(git diff --name-only HEAD upstream/${TRAVIS_BRANCH} -- '*.json' '*.yml' '*.xml' '*.xliff' '*.php')

if [[ -z ${RELEVANT_FILES} ]]; then echo -n 'KO'; exit 0; fi;
