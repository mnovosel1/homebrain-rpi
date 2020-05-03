#!/usr/bin/env python3

#import json
from yr.libyr import Yr

weather = Yr(location_name='Croatia/Zagreb_fylke/Samobor')

first = 0;

for forecast in weather.forecast(as_json=True):
    print(forecast)
#    x = json.loads(forecast)
    
#    if first == 0:
#        first = 1;
#        print(x)
#    elif x["@period"] == "1":
#        print(x)
