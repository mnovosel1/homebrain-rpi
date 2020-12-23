#!/bin/bash

read -r str < /dev/ttyUSB0;
arrStr=(${str//:/ })

while [ "${arrStr[0]}" == "Sending" ]; do
    read -r str < /dev/ttyUSB0;
    arrStr=(${str//:/ })
done

/usr/bin/mosquitto_pub -h 10.10.10.11 -u hassio -P sonopass -t /hassio/sens1/ -m '{ "light": '${arrStr[0]}', "sound": '${arrStr[1]}' }' -q 2 -r

echo ${arrStr[0]};
echo ${arrStr[1]};
