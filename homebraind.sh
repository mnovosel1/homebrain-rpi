#!/bin/bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

hour=$(( $((10#$(date +'%H')))*1 ))
minute=$(( $((10#$(date +'%M')))*1 ))
lastminute=$(( minute-1 ))

while true ; do

	if [ $((minute)) -gt $((lastminute)) ]; then

		# svake minute
		#$DIR/helpers/saveLastTemps.php
		#/usr/bin/php $DIR/heating/heating.php

		# svakih 2 minute
		if [ $(( minute%2 )) -eq 0 ]; then
			$DIR/homebrain medvedi check
		fi

		# svakih 5 minuta
		if [ $(( minute%5 )) -eq 0 ]; then
			# /usr/bin/php $DIR/lan/lan.php
			$DIR/homebrain hbrain wakecheck
		fi
		
		# svaki sat
		#if [ $((minute)) -eq 0 ]; then
		#fi

		# svako jutro u 4:44
		if [ $((hour)) -eq 4 -a $((minute)) -eq 44 ]; then
		
			$DIR/dbbackup.php
			# $DIR/lan/dbbackup.php
			# $DIR/heating/getTempSet.php
			# $DIR/heating/dbbackup.php
			$DIR/homebrain amp off

			$DIR/homebrain hserv wake DailyWake
			sudo /sbin/shutdown -r now
		fi
	fi

	lastminute=$(( minute ))
	sleep 45s

	hour=$(( $((10#$(date +'%H')))*1 ))
	minute=$(( $((10#$(date +'%M')))*1 ))

done
