#!/usr/bin/env bash
set -ev

if [ -f before_script_test.local.sh ]
then
    ./before_script_test.local.sh
fi
