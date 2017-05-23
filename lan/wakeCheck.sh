#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && cd .. && pwd )"
. $DIR/lan/config.ini

function email {
  /usr/bin/php $DIR/notify/email $1 $2
}

function srvShut {
  if [ $(who | wc -l) -lt 1 ]; then
	$DIR/lan/srvShut.sh;
  fi
  exit 0;
}

function srvWake {
  $DIR/lan/srvWake.sh
  exit 0;
}

function kodiShut {
  email "Gasim KODI!"
  $DIR/lan/kodiShut.sh
  exit 0;
}


dailyCronWake=5
dailyCronWakeLog=$(cat $DIR/var/dailyCronWake.log)

# provjeri server, kodi
serverlive=$(ping -c1 10.10.10.100 | grep 'received' | awk -F ',' '{print $2}' | awk '{ print $1}');
kodilive=$(ping -c1 10.10.10.10 | grep 'received' | awk -F ',' '{print $2}' | awk '{ print $1}');



# ako je server upaljen azuriraj waketime
if [ $((serverlive)) -gt 0 ]; then
  /usr/bin/ssh 10.10.10.100 -p 22 "/root/chkforwake.sh" > $DIR/var/srvWakeTime.log
fi

nowtime=$(date +"%s");
nowhour=$(date +"%k");
waketime=$(cat $DIR/var/srvWakeTime.log);
diff=$(($waketime-$nowtime));


# ako je kodi upaljen i server ugasen - upali server
if [ $((kodilive)) -gt 0 -a $((serverlive)) -lt 1 ]; then
  srvWake;
fi

# ako je server ugasen i timer unutar 15 min - upali server
if [ $((serverlive)) -lt 1 -a $((diff)) -gt 0 -a $((diff)) -lt 900 ]; then
  srvWake;
fi

# ako je server upaljen, kodi ugasen i timer veci od pola sata - ugasi server
if [ $((c2alarm)) -lt 1 -a $((serverlive)) -gt 0 -a $((kodilive)) -lt 1 -a $((diff)) -gt 1800 ]; then
	srvShut;
fi

# ako je server ugasen, a vrijeme je za dailyCronWake
if [ $((serverlive)) -lt 1 -a $((dailyCronWake)) -eq $(date +%k) ]; then
	if [ $dailyCronWakeLog != $(date +"%d-%m-%y") ]; then
		date +"%d-%m-%y" > $DIR/var/dailyCronWake.log
		srvWake
	fi
fi