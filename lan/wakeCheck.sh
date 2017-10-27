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


dailyCronWake=6
dailyCronWakeLog=$(cat $DIR/var/dailyCronWake.log)

# provjeri server, kodi, br korisnika na HBrainu
serverlive=$(ping -c1 10.10.10.100 | grep 'received' | awk -F ',' '{print $2}' | awk '{ print $1}');

kodilive=$(ping -c1 10.10.10.10 | grep 'received' | awk -F ',' '{print $2}' | awk '{ print $1}');

if [ $(who | wc -l) -gt 0 ]; then
  hbrainuser=1
else
  hbrainuser=0
fi

if mpc status | grep playing >/dev/null; then
  mpdplaying=1
else
  mpdplaying=0
fi

# ako je server upaljen azuriraj waketime, provjeri server user i torrenting
if [ $((serverlive)) -gt 0 ]; then
  /usr/bin/ssh 10.10.10.100 -p 22 "/root/chkTvheadend.php" > $DIR/var/srvWakeTime.log
  if [ $(($(cat $DIR/var/srvWakeTime.log)-$(date +"%s"))) -lt 1800 ]; then
	recording=1
  else
	recording=0
  fi

	
  if [ $(/usr/bin/ssh 10.10.10.100 "who | wc -l") -gt 0 ]; then
    hserveruser=1
  else
    hserveruser=0
  fi

  if [ $(/usr/bin/ssh 10.10.10.100 "/root/chkTorrenting.sh") -gt 0 ]; then
    torrentactive=1
  else
    torrentactive=0
  fi
fi


# azuriraj bazu
sqlite3 $DIR/var/hbrain.db "UPDATE states SET active=$serverlive WHERE name='HomeServer'";
sqlite3 $DIR/var/hbrain.db "UPDATE states SET active=$kodilive WHERE name='KODI'";
sqlite3 $DIR/var/hbrain.db "UPDATE states SET active=$hbrainuser WHERE name='HomeBrain user'";
sqlite3 $DIR/var/hbrain.db "UPDATE states SET active=$mpdplaying WHERE name='MPD playing'";
sqlite3 $DIR/var/hbrain.db "UPDATE states SET active=$recording WHERE name='TV recording'";
sqlite3 $DIR/var/hbrain.db "UPDATE states SET active=$hserveruser WHERE name='HomeServer user'";
sqlite3 $DIR/var/hbrain.db "UPDATE states SET active=$torrentactive WHERE name='Torrenting'";


# ako je kodi upaljen i server ugasen - upali server
if [ $((kodilive)) -gt 0 -a $((serverlive)) -lt 1 ]; then
  srvWake;
fi

# ako je server ugasen i TV recording manji od pola sata - upali server
if [ $((serverlive)) -lt 1 -a $((recording)) -gt 0 ]; then
  srvWake;
fi

# ako je server upaljen, kodi ugasen, TV recording veci od pola sata i nema torrenta - ugasi server
if [ $((serverlive)) -gt 0 -a $((kodilive)) -lt 1 -a $((recording)) -lt 1 -a $((torrentactive)) -lt 1 ]; then
	srvShut;
fi

# ako je server ugasen, a vrijeme je za dailyCronWake
if [ $((serverlive)) -lt 1 -a $((dailyCronWake)) -eq $(date +%k) ]; then
	if [ $dailyCronWakeLog != $(date +"%d-%m-%y") ]; then
		date +"%d-%m-%y" > $DIR/var/dailyCronWake.log
		srvWake
	fi
fi