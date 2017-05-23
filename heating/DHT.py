#!/usr/bin/python

import sys
import Adafruit_DHT

humidity, temperature = Adafruit_DHT.read_retry(22, 4)

if humidity is not None and temperature is not None:
	print '{0:0.1f}|{1:0.1f}'.format(temperature, humidity)
else:
	print 'Failed to get reading. Try again!'
	sys.exit(1)
