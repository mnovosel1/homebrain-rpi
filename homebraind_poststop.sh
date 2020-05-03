#!/bin/bash

/usr/bin/mosquitto_pub -h 10.10.10.11 -u hassio -P sonopass -t 'hbrain/stat/hbrain/' -m 'offline' -q 2 -r
