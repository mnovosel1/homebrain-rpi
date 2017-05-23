#!/bin/bash

#net rpc shutdown -s -t 0 -f -I 10.10.10.10 -U raspi%raspass >> /dev/null
#ssh -q root@10.10.10.10 "shutdown -P 0" &

/usr/bin/ssh 10.10.10.10 -p 22 "shutdown -h now"