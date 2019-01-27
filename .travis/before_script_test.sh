#!/usr/bin/env bash
set -ev

if [ -f .travis/before_script_test.local.sh ]
then
    ./.travis/before_script_test.local.sh
fi
