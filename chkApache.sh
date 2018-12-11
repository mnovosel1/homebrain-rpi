#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

time=$(date +%H:%M)

if ! pidof apache2 > /dev/null
then
    echo $time "Apache web server  is down, Trying auto-restart" >> $DIR/var/thinking

    # web server down, restart the server
    sudo /etc/init.d/apache2 restart > /dev/null
    sleep 10

    #checking if apache restarted or not
    if pidof apache2 > /dev/null
    then
        message="Apache restarted successfully"
    else
        message="Restart Failed, try restarting manually"
    fi
    echo $time "$message" >> $DIR/var/thinking
else
    echo $time "Apache running OK." >> $DIR/var/thinking
fi
