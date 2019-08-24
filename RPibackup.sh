#!/bin/bash

date=`date +"%d-%m-%Y"`

#mount /srv/storage/

/etc/init.d/homebrain stop
#/etc/init.d/openvpn stop
/etc/init.d/apache2 stop
/etc/init.d/cron stop

echo "Backing up to /srv/PiStorage/backups/ && hbserver ..."

echo "Backup /home/hbrain/.node-red to hbrain_node-red_$date.tgz...."
tar -zcf /srv/PiStorage/backups/hbrain_node-red_$date.tgz /home/hbrain/.node-red/

echo "Backup /etc to hbrain_etc_$date.tgz...."
tar -zcf /srv/PiStorage/backups/hbrain_DIR-etc_$date.tgz /etc/

echo "Backup /srv/HomeBrain to hbrain_hbraindir_$date.tgz...."
tar -zcf /srv/PiStorage/backups/hbrain_DIR-hbraindir_$date.tgz /srv/HomeBrain/

echo "Backup /boot to hbrain_boot_$date.img...."
dd if=/dev/mmcblk0p1 | ssh hbrain@10.10.10.100 dd of=/srv/storage/2_backups/RPi_backup/hbrain_mmcblk0p1-boot_$date.img bs=1024k status=progress

echo "Backup / to hbrain_root_$date.img...."
dd if=/dev/sda2 | ssh hbrain@10.10.10.100 dd of=/srv/storage/2_backups/RPi_backup/hbrain_sda2-root_$date.img bs=1024k status=progress

/etc/init.d/cron start
/etc/init.d/apache2 start
#/etc/init.d/openvpn start
/etc/init.d/homebrain start
