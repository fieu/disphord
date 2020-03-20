#!/usr/bin/env bash
set -x
set -e
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
$DIR/../../builds/disphord webhook:send  \
    --username "$(php -r 'echo "PHP " . phpversion();')" \
    --title "builds.sr.ht - Build environment" \
    --description "Build URL: $JOB_URL" \
    --field "Distribution, \`$(lsb_release -i | cut -d':' -f2 | xargs)\`, true" \
    --field "Version, \`$(lsb_release -c | cut -d':' -f2 | xargs) ($(lsb_release -r | cut -d':' -f2 | xargs))\`, true" \
    --field "Kernel, \`$(uname -r)\`, true" \
    --field "Architecture, \`$(hostnamectl | grep Architecture | cut -d':' -f2 | xargs)\`, true" \
    --field "Hostname, \`$(hostname -f)\`, true" \
    --field "Job ID, \`#$JOB_ID\`, false" \
