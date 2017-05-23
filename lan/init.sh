#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && cd .. && pwd )"


if [ ! -f $DIR/var/lan.db ]; then
  if [ -f $DIR/var_sav/lan.db ]; then
    cp $DIR/var_sav/lan.db $DIR/var/lan.db
  else
    sqlite3 $DIR/var/lan.db -init $DIR/lan/lan.sql &
  fi
fi

if [ ! -f $DIR/var/srvWakeTime.log ]; then
  if [ -f $DIR/var_sav/srvWakeTime.log ]; then
    cp $DIR/var_sav/srvWakeTime.log $DIR/var/srvWakeTime.log
    cp $DIR/var_sav/home.log $DIR/var/home.log
  else
    touch $DIR/var/srvWakeTime.log
    touch $DIR/var/home.log
  fi
fi


sleep 3s
chown -R pi:pi $DIR/var/*
