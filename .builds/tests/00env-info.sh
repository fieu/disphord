#!/usr/bin/env bash
set -x
set -e
$TEST_DIR/../../builds/disphord webhook:send -v \
    --username "$(php -r 'echo "PHP " . phpversion();')" \
    --title "builds.sr.ht - Build environment" \
    --description "Build URL: $JOB_URL" \
    --field "Distribution, \`$(lsb_release -i | cut -d':' -f2 | xargs)\`, true" \
    --field "Version, \`$(lsb_release -c | cut -d':' -f2 | xargs) ($(lsb_release -r | cut -d':' -f2 | xargs))\`, true" \
    --field "Kernel, \`$(uname -r)\`, true" \
    --field "Architecture, \`$(hostnamectl | grep Architecture | cut -d':' -f2 | xargs)\`, true" \
    --field "Hostname, \`$(hostname -f)\`, true" \
    --field "Job ID, \`#$JOB_ID\`, false" \
