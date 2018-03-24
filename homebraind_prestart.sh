#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"


if [ ! -f $DIR/var/hbrain.db ]; then
  if [ -f $DIR/saved_var/hbrain.db ]; then
    cp $DIR/saved_var/hbrain.db $DIR/var/hbrain.db
  else
    sqlite3 $DIR/var/hbrain.db -init $DIR/hbrain.sql &
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

/bin/chown -R hbrain:www-data $DIR/var/*
/bin/chmod -R 774 $DIR/var/*