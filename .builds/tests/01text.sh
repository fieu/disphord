#!/usr/bin/env bash
set -x
set -e
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
$DIR/../../builds/disphord webhook:send --text "Test Text"
