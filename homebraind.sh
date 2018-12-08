#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
date=$(date +%d-%m-%Y)
lasttime=""

sudo /usr/bin/pigpiod &

while true
do

nowtime=$(date +%H:%M)

if [ "$lasttime" != "$nowtime" ]; then

  lasttime=$nowtime
  # echo $nowtime

  #### every midnight
  case $nowtime in (00:00)
    $DIR/homebrain hbrain uploadData
    $DIR/homebrain hbrain dbbackup

    cp $DIR/var/hbrain.sql /srv/PiStorage/backups/SQL_hbrain_$date.sql

    cp $DIR/var/hbrain.log /srv/PiStorage/backups/LOG_hbrain_$date.log
    echo "" > $DIR/var/hbrain.log

    tar -zcf /srv/PiStorage/backups/node-red_$date.tgz /home/hbrain/.node-red/
    tar -zcf /srv/PiStorage/backups/HomeBrain_$date.tgz /srv/HomeBrain
  ;;
  esac

  #### every day at 2:22
    case $nowtime in (2:22)
    $DIR/homebrain hbrain allOff
    sudo /sbin/shutdown -F -r +5
  ;;
  esac

  #### every hour
  case $nowtime in (*:00)
    $DIR/homebrain hbrain todo
    $DIR/homebrain hbrain wifi
    $DIR/homebrain lan checknetwork
  ;;
  esac

  #### every 30 minutes
  case $nowtime in (*:[03]0)

	;;
  esac

  #### every 10 minutes
  case $nowtime in (*:*[0])

	;;
  esac

  #### every 5 minutes
  case $nowtime in (*:*[05])
	$DIR/homebrain hbrain wakecheck
	;;
  esac

  #### every 2 minutes
  case $nowtime in (*:*[02468])

	;;
  esac

  #### every minute
  case $nowtime in (*)
    $DIR/homebrain medvedi check
    $DIR/homebrain hbrain alarm
	;;
  esac

  sleep 30

fi

done
