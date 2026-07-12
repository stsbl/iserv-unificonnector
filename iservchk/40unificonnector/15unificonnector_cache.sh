#!/bin/bash
FN_CACHEDIR="/var/lib/iserv/unificonnector/app/cachedir"

[ -f "$FN_CACHEDIR" ] || exit 0

CACHE_DIR="$(< "$FN_CACHEDIR")"

[ -n "$CACHE_DIR" ] || exit 0
[[ "$CACHE_DIR" =~ ^/var/cache/iserv/unificonnector/app/ ]] || exit 0

cat<<EOT
MkDir 0755 root:root /var/cache/iserv/unificonnector
MkDir 2770 iserv-unificonnector:iserv-unificonnector /var/cache/iserv/unificonnector/app
MkDir 2770 iserv-unificonnector:iserv-unificonnector $CACHE_DIR
MkDir 2770 iserv-unificonnector:iserv-unificonnector $CACHE_DIR/{pools,templates}

EOT
