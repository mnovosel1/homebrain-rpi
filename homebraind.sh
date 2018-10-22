#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
date=$(date +%d-%m-%Y)
lasttime=""

while true
do

nowtime=$(date +%H:%M)

if [ "$lasttime" != "$nowtime" ]; then

  lasttime=$nowtime
  echo $nowtime

  # every midnight
  case $nowtime in
    (00:00)
	$DIR/homebrain hbrain uploadData
	$DIR/homebrain hbrain dbbackup

	cp $DIR/var/hbrain.sql /srv/PiStorage/backups/SQL_hbrain_$date.sql

	cp $DIR/var/hbrain.log /srv/PiStorage/backups/LOG_hbrain_$date.log
	echo "" > $DIR/var/hbrain.log

	tar -zcf /srv/PiStorage/backups/node-red_$date.tgz /home/hbrain/.node-red/
	tar -zcf /srv/PiStorage/backups/HomeBrain_$date.tgz /srv/HomeBrain
	;;
  esac

  # every day at 2:22
  case $nowtime in
    (2:22)
	$DIR/homebrain amp off
	sudo /sbin/shutdown -F -r now
	;;
  esac

  # every hour
  case $nowtime in
    (*:00)
	$DIR/homebrain hbrain todo
	$DIR/homebrain hbrain wifi
	$DIR/homebrain lan checknetwork
	;;
  esac

  # every 30 minutes
  case $nowtime in
    (*:[03]0)
	echo 'every 30 minutes'
	;;
  esac

  case $nowtime in
    (*:*[0])
	echo 'every 10 minutes'
	;;
  esac

  # every 5 minutes
  case $nowtime in
    (*:*[05])
	$DIR/homebrain hbrain wakecheck
	;;
  esac

  # every 2 minutes
  case $nowtime in
    (*:*[02468])
	echo 'every 2 minutes'
	;;
  esac

  # every minute
  case $nowtime in
    (*)
	$DIR/homebrain medvedi check
	$DIR/homebrain hbrain alarm
	;;
  esac

  sleep 30
fi

done
