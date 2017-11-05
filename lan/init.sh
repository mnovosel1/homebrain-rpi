#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && cd .. && pwd )"


if [ ! -f $DIR/var/lan.db ]; then
  if [ -f $DIR/saved_var/lan.db ]; then
    cp $DIR/saved_var/lan.db $DIR/var/lan.db
  else
    sqlite3 $DIR/var/lan.db -init $DIR/lan/lan.sql &
  fi
fi

if [ ! -f $DIR/var/srvWakeTime.log ]; then
  if [ -f $DIR/saved_var/srvWakeTime.log ]; then
    cp $DIR/saved_var/srvWakeTime.log $DIR/var/srvWakeTime.log
  else
    touch $DIR/var/srvWakeTime.log
  fi
fi

if [ ! -f $DIR/var/dailyCronWake.log ]; then
  if [ -f $DIR/saved_var/dailyCronWake.log ]; then
    cp $DIR/saved_var/dailyCronWake.log $DIR/var/dailyCronWake.log
  else
    touch $DIR/var/dailyCronWake.log
  fi
fi

sleep 3s
chown -R brain:brain $DIR/var/*
