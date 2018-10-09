#!/bin/bash

date=`date +"%d-%m-%Y"`
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

$DIR/homebrain hbrain dbbackup

echo "Backup /home/hbrain/.node-red to node-red_$date.tgz...."
tar -zcf /srv/PiStorage/backups/node-red_$date.tgz /home/hbrain/.node-red/

echo "Backup /srv/HomeBrain to HomeBrain_$date.tgz...."
tar -zcf /srv/PiStorage/backups/HomeBrain_$date.tgz /srv/HomeBrain
