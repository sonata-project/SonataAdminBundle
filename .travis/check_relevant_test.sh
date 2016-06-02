#!/usr/bin/env bash
set -ev

RELEVANT_FILES=$(git diff --name-only HEAD upstream/${TRAVIS_BRANCH} -- '*.php' '*.yml' '*.xml' '*.twig' '*.js' '*.css' '*.json')

if [[ -z ${RELEVANT_FILES} ]]; then echo -n 'KO'; exit 0; fi;
