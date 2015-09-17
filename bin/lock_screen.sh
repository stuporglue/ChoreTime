#!/bin/bash

# http://ubuntuforums.org/showthread.php?t=2268427
# disable-> xset s off -dpms
# enable-> xset s on +dpms
# pgrep -u calvin -f game
sudo -u "$1" DISPLAY="$2" xscreensaver-command -lock
