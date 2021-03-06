#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
date=$(date +%d-%m-%Y)
lasttime=""

#exit 0;

# sudo /usr/bin/pigpiod &
# /srv/HomeBrain/remote/remote.py &

if !(pidof -x "mqttlistener.php" > /dev/null)
then
    sleep 1m
    $DIR/mqttlistener.php &
fi

if !(ps -C "python3 "$DIR"/helpers/getsndnlight.py" > /dev/null)
then
    touch $DIR/helpers/arduinocmd
    sleep 1m
    $DIR/helpers/getsndnlight.py &
fi

while true
do

nowtime=$(date +%H:%M)

if [ "$lasttime" != "$nowtime" ]; then

  lasttime=$nowtime

  #### every night at 4:59
  case $nowtime in (04:59)
    $DIR/homebrain notifier setClockDate
    $DIR/homebrain notifier setClockTime &
  ;;
  esac

  #### every night at 2:30
  case $nowtime in (02:30)
    $DIR/homebrain hbrain allOff

    $DIR/homebrain hbrain uploadData
    $DIR/homebrain hbrain dbbackup

    cp $DIR/var/hbrain.sql /srv/PiStorage/backups/SQL_hbrain_$date.sql
    cp $DIR/var/hbrain.log /srv/PiStorage/backups/LOG_hbrain_$date.log
    echo "" > $DIR/var/hbrain.log
    cp $DIR/var/thinking /srv/PiStorage/backups/thinking_hbrain_$date.log
    echo "" > $DIR/var/thinking

    sudo /bin/tar -zcf /srv/PiStorage/backups/node-red_$date.tgz /home/hbrain/.node-red/
    sudo /bin/tar -zcf /srv/PiStorage/backups/RPi_DIR-etc_$date.tgz /etc/
    sudo /bin/tar -zcf /srv/PiStorage/backups/RPi_DIR-hbraindir_$date.tgz /srv/HomeBrain/

    sudo /sbin/shutdown -F -r +5
  ;;
  esac

  #### every hour
  case $nowtime in (*:00)
    $DIR/homebrain hbrain todo
    $DIR/homebrain lan checknetwork
  ;;
  esac

  #### every 30 minutes
  case $nowtime in (*:[03]0)
    #$DIR/chkApache.sh
	;;
  esac

  #### every 10 minutes
  case $nowtime in (*:*[0])
    if !(pidof -x "mqttlistener.php" > /dev/null)
    then
        $DIR/mqttlistener.php &
    fi

    if !(ps -C "python3 "$DIR"/helpers/getsndnlight.py" > /dev/null)
    then
        $DIR/helpers/getsndnlight.py &
    fi
	;;
  esac

  #### every 5 minutes
  case $nowtime in (*:*[05])
    #$DIR/homebrain hbrain wakecheck &
    #$DIR/homebrain hbrain gettemps &
	;;
  esac

  #### every 2 minutes
  case $nowtime in (*:*[02468])
	;;
  esac

  #### every minute
  case $nowtime in (*)
    # $DIR/homebrain medvedi check
    # $DIR/homebrain hbrain alarm
    $DIR/homebrain hbrain clock

	if ping -c 1 10.10.10.100 &> /dev/null; then

		if [[ $(ssh 10.10.10.100 'if [ -d /tmp/rpiBackup.lock ]; then echo 1; else echo 0; fi') == 1 ]]; then

      			echo $(date +%H:%M) "I will now make HomeBrain RPi backup to HomeServer." >> $DIR/var/thinking

			scp /srv/PiStorage/backups/SQL_hbrain_$date.sql 10.10.10.100:/srv/shared/_BACKUPS_/RPi_backup
			scp /srv/PiStorage/backups/LOG_hbrain_$date.log 10.10.10.100:/srv/shared/_BACKUPS_/RPi_backup
      			scp /srv/PiStorage/backups/thinking_hbrain_$date.log 10.10.10.100:/srv/shared/_BACKUPS_/RPi_backup

			sudo /bin/tar zcf - /home/hbrain/.node-red | ssh 10.10.10.100 "cat > /srv/shared/_BACKUPS_/RPi_backup/node-red_$date.tgz"
			sudo /bin/tar zcf - /srv/HomeBrain | ssh 10.10.10.100 "cat > /srv/shared/_BACKUPS_/RPi_backup/hbraindir_$date.tgz"
			sudo /bin/tar zcf - /etc | ssh 10.10.10.100 "cat > /srv/shared/_BACKUPS_/RPi_backup/etc_$date.tgz"

			if [[ $(date +%u) -eq 5 ]] ; then
			  sudo /bin/dd if=/dev/mmcblk0p1 | ssh 10.10.10.100 dd of=/srv/shared/_BACKUPS_/RPi_backup/RPi_mmcblk0p1-boot_$date.img
			  sudo /bin/dd if=/dev/sda2 | ssh 10.10.10.100 dd of=/srv/shared/_BACKUPS_/RPi_backup/RPi_sda2-root_$date.img
			fi

			ssh 10.10.10.100 'rm -rf /tmp/rpiBackup.lock &>/dev/null'

      			echo $(date +%H:%M) "..and I just finished backuping HomeBrain RPi to HomeServer." >> $DIR/var/thinking
		fi

	fi

	;;
  esac

  sleep 30

fi

done
