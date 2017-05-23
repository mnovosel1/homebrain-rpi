#!/bin/bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
. $DIR/heating/config.ini


hour=$(( $((10#$(date +'%H')))*1 ))
minute=$(( $((10#$(date +'%M')))*1 ))
lastminute=$(( minute-1 ))

while true ; do

  /usr/bin/php $DIR/heating/heating.php

  if [ $((minute)) -gt $((lastminute)) ]; then

    case 0 in

      $(( minute )))
      ;;

      $(( minute%5 )))
        /usr/bin/php $DIR/lan/lan.php
        $DIR/lan/wakeCheck.sh
      ;;

      *)
      ;;

    esac
  fi

  lastminute=$(( minute ))
  sleep 45s
  
  hour=$(( $((10#$(date +'%H')))*1 ))
  minute=$(( $((10#$(date +'%M')))*1 ))

done
