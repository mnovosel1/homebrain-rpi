#!/usr/bin/env python3

import os, serial
import time
import json
import paho.mqtt.client as mqtt

dir = os.path.dirname(os.path.realpath(__file__))

tmp = ""
lastPrint = 0
millis = lambda: int(round(time.time() * 1000))

mqttClient = mqtt.Client()
mqttClient.username_pw_set("hassio", "sonopass")

f = open(dir +"/arduinocmd", "r+")

if __name__ == '__main__':
    ser = serial.Serial('/dev/ttyUSB0', 9600, timeout=1)
    ser.flush()

    while True:
        cmd = "get"
        tmp = f.readline()

        if tmp != "":
            cmd = tmp.rstrip()
            f.seek(0)
            f.write("")
            f.truncate()

        if cmd != "get" or (millis() - lastPrint) > 300000:
            
            ser.write(str(cmd + "\n").encode('utf'))
            line = ser.readline().decode('utf-8').strip()

            if cmd == "get":
                lastPrint = millis()
                if line != "":
                    line = line.split(":")
                    line = '{"light":'+ line[0].split(".")[0] +', "sound":'+ line[1].split(".")[0] +'}'
                    mqttClient.connect("localhost")
                    mqttClient.publish("/hassio/sens1/", line)
                    mqttClient.disconnect()

            print(line)
