#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && cd .. && pwd )"
. $DIR/heating/config.ini

#tempSet=`sqlite3 $DIR/var/heating.db "SELECT temp FROM tempConf WHERE time('now', 'localtime') BETWEEN start AND stop LIMIT 1;"`
tempSet=`sqlite3 $DIR/var/heating.db "SELECT temp FROM tempConf WHERE wday = STRFTIME('%w', DATETIME('now', 'localtime')) AND hour = STRFTIME('%H', DATETIME('now', 'localtime'));"`


tempIn=$(cat /sys/bus/w1/devices/28-0000056107f9/w1_slave | sed -n 's/^.*\(t=[^ ]*\).*/\1/p' | sed 's/t=//' | awk '{x=$1}END{print((x-1000-(x%100))/1000)}')

temp=`sqlite3 $DIR/var/heating.db "SELECT (timestamp < datetime(datetime('now', 'localtime'), '-300 seconds')) as old, * FROM tempLog ORDER BY timestamp DESC LIMIT 1;"`
IFS='|' eval 'tempLog=($temp)'

oldLog=${tempLog[0]}
tempOut=${tempLog[4]}
heatingOn=${tempLog[5]}

minute=$(date -d NOW +"%M");
if [ $((minute%5)) -eq 0 ]; then
  json=`curl -s -X GET http://api.wunderground.com/api/65bdc72ba6b054fd/geolookup/conditions/q/Europe/Samobor.json`
  prop='temp_c'
  temp=`echo $json | sed 's/\\\\\//\//g' | sed 's/[{}]//g' | awk -v k="text" '{n=split($0,a,","); for (i=1; i<=n; i++) print a[i]}' | sed 's/\"\:\"/\|/g' | sed 's/[\,]/ /g' | sed 's/\"//g' | grep -w $prop`
  tempOut=$(echo ${temp} | egrep -o '\-?[0-9]+(\.[0-9][0-9]?)?')
fi

echo $oldLog"|"$tempSet"|"$tempIn"|"$tempOut"|"$heatingOn
