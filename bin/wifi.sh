#!/usr/bin/expect


#If it all goes pear shaped the script will timeout after 20 seconds.
set timeout 20

set onoff [lindex $argv 0]

spawn telnet 10.10.10.101

expect "Password:"
send "passich\r"

send "24\r"
send "8\r"

expect "P660HW-T3>"

send "wlan active $onoff\r"

expect "P660HW-T3>"
send "exit\r"
send "99\r"

#send_user "OK!"

close
#interact
