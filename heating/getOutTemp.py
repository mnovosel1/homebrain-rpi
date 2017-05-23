#!/usr/bin/python

print 23
exit

import urllib
import json

apikey="62865b741042ec553db7d9d91de5df89" # get a key from https://developer.forecast.io/register
# Latitude & longitude - current values are central Basingstoke.
lati="45.809530"
longi="15.707298"

# Add units=si to get it in sensible ISO units not stupid Fahreneheit.
url="https://api.darksky.net/forecast/"+apikey+"/"+lati+","+longi+"?units=si"

meteo=urllib.urlopen(url).read()
meteo=meteo.decode('utf-8')
#weather=json.loads(meteo)

print (weather['currently']['temperature'])
