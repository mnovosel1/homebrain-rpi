#!/bin/bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

date=`date +"%d-%m-%Y"`
hour=$(( $((10#$(date +'%H')))*1 ))
minute=$(( $((10#$(date +'%M')))*1 ))
lastminute=$(( minute-1 ))

while true ; do

	if [ $((minute)) -ne $((lastminute)) ]; then

		# svake minute
		$DIR/homebrain medvedi check
		$DIR/homebrain hbrain alarm

		# svakih 2 minute
		# if [ $(( minute%2 )) -eq 0 ]; then
		# fi

		# svakih 5 minuta
		if [ $(( minute%5 )) -eq 0 ]; then
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

		# svako jutro u 2:22
		if [ $((hour)) -eq 2 -a $((minute)) -eq 22 ]; then

			$DIR/homebrain amp off

			sudo /sbin/shutdown -F -r now
		fi

		# u ponoć
		if [ $((hour)) -eq 0 -a $((minute)) -eq 0 ]; then
			$DIR/homebrain hbrain uploadData
			$DIR/homebrain hbrain dbbackup

			cp $DIR/var/hbrain.sql /srv/PiStorage/backups/SQL_hbrain_$date.sql

			cp $DIR/var/hbrain.log /srv/PiStorage/backups/LOG_hbrain_$date.log
			echo "" > $DIR/var/hbrain.log

			tar -zcf /srv/PiStorage/backups/node-red_$date.tgz /home/hbrain/.node-red/
			tar -zcf /srv/PiStorage/backups/HomeBrain_$date.tgz /srv/HomeBrain
		fi

		lastminute=$(( minute ))
	fi

	sleep 30s

	hour=$(( $((10#$(date +'%H')))*1 ))
	minute=$(( $((10#$(date +'%M')))*1 ))

done
