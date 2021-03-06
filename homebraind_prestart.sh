#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

/usr/bin/mosquitto_pub -h 10.10.10.13 -u hassio -P sonopass -t 'hbrain/stat/hbrain/' -m 'online' -q 2 -r

if [[ ! -L $DIR/www/api.php ]]; then
	rm -rf $DIR/www/api.php
	ln -s $DIR/classes/api.php $DIR/www/api.php
fi


if [ ! -f $DIR/var/hbrain.db ]; then
  if [ -f $DIR/saved_var/hbrain.db ]; then
    cp $DIR/saved_var/hbrain.db $DIR/var/hbrain.db
  else
    if [ ! -f $DIR/var/hbrain.sql ]; then
      cp $DIR/saved_var/hbrain.sql $DIR/var/hbrain.sql
    fi
    hbrain dbrestore true
  fi
fi

if [ ! -f $DIR/var/hbrain.log ]; then
  cp $DIR/saved_var/hbrain.log $DIR/var/hbrain.log
fi

if [ ! -f $DIR/var/srvWakeTime.log ]; then
  cp $DIR/saved_var/srvWakeTime.log $DIR/var/srvWakeTime.log
fi

if [ ! -f $DIR/var/medvedi.log ]; then
  cp $DIR/saved_var/medvedi.log $DIR/var/medvedi.log
fi

#if [ ! -f $DIR/var/objStates.php ]; then
  #cp $DIR/saved_var/objStates.php $DIR/var/objStates.php
#fi

/bin/chown -R hbrain:hbrain $DIR/var/*
/bin/chmod -R 0770 $DIR/var/*
