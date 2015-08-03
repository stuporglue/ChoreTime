#!/bin/bash

sudo -u $1 DISPLAY=$2 xscreensaver-command -time 2>/dev/null | grep 'locked since' >/dev/null 2>&1

if [ $? == 0 ]; then
    echo 'true'
else
    echo 'false'
fi
