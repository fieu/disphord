#!/usr/bin/env bash
set -x
set -e
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
for file in $DIR/tests/*.sh; do echo "Executing $(basename $file)..." && command "$file"; done
