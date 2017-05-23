#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && cd .. && pwd )"


if [ ! -f $DIR/var/heating.db ]; then
  if [ -f $DIR/var_sav/heating.db ]; then
    cp $DIR/var_sav/heating.db $DIR/var/heating.db
  else
    sqlite3 $DIR/var/heating.db -init $DIR/heating/heating.sql &
  fi
fi

if [ ! -f $DIR/var/tempBoost.dat ]; then
  if [ -f $DIR/var_sav/tempBoost.dat ]; then
    cp $DIR/var_sav/tempBoost.dat $DIR/var/tempBoost.dat
  else
    echo '10|0' > $DIR/var/tempBoost.dat
  fi
fi

if [ ! -f $DIR/var/lastTemp.dat ]; then
  if [ -f $DIR/var_sav/lastTemp.dat ]; then
    cp $DIR/var_sav/lastTemp.dat $DIR/var/lastTemp.dat
  else
    echo '10|0|0' > $DIR/var/lastTemp.dat
  fi
fi

if [ ! -f $DIR/var/heating.log ]; then
  if [ -f $DIR/var_sav/heating.log ]; then
    cp $DIR/var_sav/heating.log $DIR/var/heating.log
  else
    touch $DIR/var/heating.log
  fi
fi


sleep 3s
chown -R pi:pi $DIR/var/*
