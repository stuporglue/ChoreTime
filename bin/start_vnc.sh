#!/bin/bash

EXISTING_VNC_PORTS=$(sudo - u $1 lsof -i | grep vino | sed 's/.*TCP \(.*:.*\) .*/\1/' | sort -u)

sudo -u $1 DISPLAY=$2 /usr/lib/vino/vino-server
