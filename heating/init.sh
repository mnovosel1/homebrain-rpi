#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && cd .. && pwd )"


if [ ! -f $DIR/var/heating.db ]; then
  if [ -f $DIR/saved_var/heating.db ]; then
    cp $DIR/saved_var/heating.db $DIR/var/heating.db
  else
    sqlite3 $DIR/var/heating.db -init $DIR/heating/heating.sql &
  fi
fi

if [ ! -f $DIR/var/tempBoost.dat ]; then
  if [ -f $DIR/saved_var/tempBoost.dat ]; then
    cp $DIR/saved_var/tempBoost.dat $DIR/var/tempBoost.dat
  else
    echo '10|0' > $DIR/var/tempBoost.dat
  fi
fi

if [ ! -f $DIR/var/lastTemp.dat ]; then
  if [ -f $DIR/saved_var/lastTemp.dat ]; then
    cp $DIR/saved_var/lastTemp.dat $DIR/var/lastTemp.dat
  else
    echo '10|0|0' > $DIR/var/lastTemp.dat
  fi
fi

if [ ! -f $DIR/var/heating.log ]; then
  if [ -f $DIR/saved_var/heating.log ]; then
    cp $DIR/saved_var/heating.log $DIR/var/heating.log
  else
    touch $DIR/var/heating.log
  fi
fi


sleep 3s
chown -R brain:brain $DIR/var/*
