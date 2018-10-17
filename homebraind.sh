#!/bin/bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

date=`date +"%d-%m-%Y"`
hour=$(( $((10#$(date +'%H')))*1 ))
minute=$(( $((10#$(date +'%M')))*1 ))
lastminute=$(( minute-1 ))

while true ; do

	if [ $((minute)) -gt $((lastminute)) ]; then

		# svake minute
		#$DIR/helpers/saveLastTemps.php
		#/usr/bin/php $DIR/heating/heating.php
		$DIR/homebrain medvedi check
		$DIR/homebrain hbrain alarm

		# svakih 2 minute
		# if [ $(( minute%2 )) -eq 0 ]; then
		# fi

		# svakih 5 minuta
		if [ $(( minute%5 )) -eq 0 ]; then
			# /usr/bin/php $DIR/lan/lan.php
			$DIR/homebrain hbrain wakecheck
		fi

		# svakih 30 minuta
		# if [ $(( minute%30 )) -eq 0 ]; then

		# fi

		# svaki sat
		if [ $((minute)) -eq 0 ]; then
			$DIR/homebrain hbrain todo
			$DIR/homebrain hbrain wifi
			$DIR/homebrain lan checknetwork
		fi

		# svako jutro u 4:44
		if [ $((hour)) -eq 4 -a $((minute)) -eq 44 ]; then

			$DIR/homebrain hbrain dbbackup
			# $DIR/lan/dbbackup.php
			# $DIR/heating/getTempSet.php
			# $DIR/heating/dbbackup.php
			$DIR/homebrain amp off

			# $DIR/homebrain hserv wake DailyWake
			sudo /sbin/shutdown -F -r now
		fi

		# u ponoć
		if [ $((hour)) -eq 23 -a $((minute)) -eq 59 ]; then
			$DIR/homebrain hbrain uploadData &

			cp $DIR/var/hbrain.sql /srv/PiStorage/backups/hbrain_$date.sql

			cp $DIR/var/hbrain.log /srv/PiStorage/backups/hbrain_$date.log
			echo "" > $DIR/var/hbrain.log

			cp $DIR/var/hbrain_dbg.log /srv/PiStorage/backups/hbrain_dbg_$date.log
			echo "" > $DIR/var/hbrain_dbg.log
		fi
	fi

	lastminute=$(( minute ))
	sleep 45s

	hour=$(( $((10#$(date +'%H')))*1 ))
	minute=$(( $((10#$(date +'%M')))*1 ))

done
