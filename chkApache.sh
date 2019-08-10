#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

time=$(date +%H:%M)

if ! pidof apache2 > /dev/null
then
    echo $time "Apache web server is down, I'm trying to restart it." >> $DIR/var/thinking
    $DIR/homebrain hbrain notify "Apache web server is down, I'm trying to restart it."

    # web server down, restart the server
    sudo /etc/init.d/apache2 restart > /dev/null
    sleep 10

    #checking if apache restarted or not
    if pidof apache2 > /dev/null
    then
        message="Apache restarted successfully."
    else
        message="Restart failed, now what?"
    fi

    $DIR/homebrain hbrain notify "$message"
    echo $time "$message" >> $DIR/var/thinking
else
    echo $time "Just checked Apache server, it's running fine." >> $DIR/var/thinking
fi
