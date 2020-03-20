#!/usr/bin/env bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
$DIR/../../builds/disphord webhook:send \
    --title "Title" \
    --title-url "https://github.com/NurdTurd/disphord" \
    --description "Description" \
    --author "Author" \
    --author-url "https://github.com/NurdTurd/disphord" \
    --author-icon "https://i.imgur.com/9FNnk6G.png" \
    --footer "Footer" \
    --footer-icon "https://i.imgur.com/9FNnk6G.png" \
    --timestamp \
    --image "https://i.imgur.com/XmMGVhZ.jpg" \
    --thumbnail "https://i.imgur.com/XmMGVhZ.jpg" \
    --color "0EF221" \
    --field "Field 1, Field 1 Value" \
    --field "Field 2, Field 2 Value, false" \
    --field "Field 3, Field 3 Value, true" \
    --field "Field 4, Field 4 Value, true"
