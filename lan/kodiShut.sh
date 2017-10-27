#!/bin/bash

#net rpc shutdown -s -t 0 -f -I 10.10.10.10 -U raspi%raspass >> /dev/null
#ssh -q root@10.10.10.10 "shutdown -P 0" &

#/usr/bin/ssh root@10.10.10.20 -p 22 "shutdown -h now"


curl -X POST -H "Content-Type: application/json" -d '{"jsonrpc":"2.0","method":"System.Shutdown","id":1}' http://10.10.10.20:80/jsonrpc
