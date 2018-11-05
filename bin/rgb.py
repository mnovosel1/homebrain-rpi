#! /usr/bin/env python
#
# Fade an LED (or one color of an RGB LED) using GPIO's PWM capabilities.
#
# Usage:
#   sudo python fade.py
#
# @author Jeff Geerling, 2015

import os, sys, time
import RPi.GPIO as GPIO

# LED pin mapping.
redPin = 15
greenPin = 12
bluePin = 11

# GPIO Setup.
GPIO.setmode(GPIO.BOARD)
GPIO.setwarnings(False)

GPIO.setup(redPin, GPIO.OUT)
GPIO.setup(greenPin, GPIO.OUT)
GPIO.setup(bluePin, GPIO.OUT)

# Use PWM to fade an LED.
fadeR = GPIO.PWM(redPin, 100)
fadeG = GPIO.PWM(greenPin, 100)
fadeB = GPIO.PWM(bluePin, 100)

fadeR.start(0)
fadeG.start(0)
fadeB.start(0)

try:
  os.mkfifo("/tmp/RGB")
except OSError, e:
    print "Failed to create FIFO: %s" % e

lastRed = 0
lastGreen = 0
lastBlue = 0

try:
  while True:
    with open('/tmp/RGB') as RGB:   # add `rb` for binary mode
      for line in RGB:
        try:
          red = float(line[0:2])
          green = float(line[2:4])
          blue = float(line[4:6])

          if red != lastRed:
            print("RED=%d" % red)
            fadeR.ChangeDutyCycle(red)
          lastRed = red

          if green != lastGreen:
            print("GREEN=%d" % green)
            fadeG.ChangeDutyCycle(green)
          lastGreen = green

          if blue != lastBlue:
            print("BLUE=%d" % blue)
            fadeB.ChangeDutyCycle(blue)
          lastBlue = blue

          time.sleep(1.05)

        except ValueError:
          lastRed = 0
          lastGreen = 0
          lastBlue = 0
          fadeR.ChangeDutyCycle(0)
          fadeG.ChangeDutyCycle(0)
          fadeB.ChangeDutyCycle(0)

except KeyboardInterrupt:
  print "Stopping...."

finally:
  GPIO.cleanup()
