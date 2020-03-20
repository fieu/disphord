#!/usr/bin/env bash
set -x
set -e
$TEST_DIR/../../builds/disphord webhook:send -v \
    --username "$(php -r 'echo "PHP " . phpversion();')" \
    --text "Text"
