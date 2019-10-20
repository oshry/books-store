#!/bin/sh
set -e

if [ "$1" = "" ]; then
    rm -f /var/run/apache2/apache2.pid
    exec /usr/sbin/apache2ctl -DFOREGROUND
else
    exec "$@"
fi