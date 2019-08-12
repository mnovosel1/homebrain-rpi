#!/usr/bin/env python3

import argparse
import re
import logging
import sys
from datetime import datetime

from btlewrap import BluepyBackend
from mitemp_bt.mitemp_bt_poller import MiTempBtPoller, \
    MI_TEMPERATURE, MI_HUMIDITY, MI_BATTERY

def main():
    i = 1
    while i < 2:
        try:
            t = datetime.now()

            poller = MiTempBtPoller("58:2d:34:32:62:fc", BluepyBackend)
            living = "{}|{}|{}".format(poller.parameter_value(MI_BATTERY), poller.parameter_value(MI_TEMPERATURE), poller.parameter_value(MI_HUMIDITY))

            poller = MiTempBtPoller("58:2d:34:32:66:fc", BluepyBackend)
            bath = "{}|{}|{}".format(poller.parameter_value(MI_BATTERY), poller.parameter_value(MI_TEMPERATURE), poller.parameter_value(MI_HUMIDITY))
        except:
            i += 1
            print("Polling failed.")
            pass
        else:
            f = open("/srv/HomeBrain/var/mitemps.log", "w")
            f.write("{}|livingroom|{}|bathroom|{}".format(t.strftime('%d/%m|%H:%M'), living, bath))
            f.close()
            print("Polling done, temps.log written")
            break

if __name__ == '__main__':
    main()
