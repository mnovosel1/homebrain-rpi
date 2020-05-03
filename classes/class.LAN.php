<?php

class LAN {
    public static $debug = false;

    public static function h() {
        return HomeBrain::help(self::class);
    }

    public static function help() {
        return HomeBrain::help(self::class);
    }

   public static function wifi($what = "") {
	$out = "";
	if ($what == "") {
		$out = exec(DIR ."/bin/wifi.sh active | awk '!/P660HW-T3>/ && /wlan active/'");
		if (strpos($out, "active 1") !== false) {
            $out = "on";
            /*
			if (date("G") == Configs::get("SILENT_TIME", "START")) {
				hbrain_log(__METHOD__.":".__LINE__, "WiFi should be off");
				LAN::wifi(0);
            }
            */
		}
		else {
            $out = "off";
            /*
			if (date("G") == Configs::get("SILENT_TIME", "END")) {
				hbrain_log(__METHOD__.":".__LINE__, "WiFi should be on");
				LAN::wifi(1);
            }
            */
		}
		//debug_log(__METHOD__.":".__LINE__, "WiFi is ". $out);
	}
	else {
		switch ($what) {
			case "1":
                        case "0":
				exec(DIR ."/bin/wifi.sh active ". $what ." | awk '!/P660HW-T3>/ && /wlan active/' &");
				HomeBrain::notify(date("H:i") ." Switching WiFi ". ($what == 1 ? "on" : "off"));
				//debug_log(__METHOD__.":".__LINE__, "Switching WiFi ". ($what == 1 ? "on" : "off"));
                        break;

			case "scan":
				exec(DIR ."/bin/wifi.sh ". $what, $tmpOut);
				$out = "";
				$found = false;
				foreach ($tmpOut as $k => $v) {
					if (strpos($v, "SSID") !== false) $found = true;
					if ($found) {
						$out .= $v . PHP_EOL;
					}
					if (strpos($v, "Recommend Channel") !== false) $found = false;
				}
				//debug_log(__METHOD__.":".__LINE__, "WiFi scanning.");
			break;
		}
	}
	return $out;
   }

    public static function ping($host) {
        $live = exec("ping -c1 ".Configs::getIP($host)." | grep 'received' | awk -F ',' '{print $2}' | awk '{ print $1}'");
		if ($live > 0) {
            //debug_log(__METHOD__.":".__LINE__, $host . " is live!");
            return true;
        }
        //debug_log(__METHOD__.":".__LINE__, $host . " is not live.");
	    return false;
    }

    public static function WOL($mac) {
        debug_log(__METHOD__.":".__LINE__, "WOL to: ". $mac);

        $addr_byte = explode(':', $mac);
        $hw_addr = '';
        for ($a=0; $a <6; $a++) $hw_addr .= chr(hexdec($addr_byte[$a]));
        $msg = chr(255).chr(255).chr(255).chr(255).chr(255).chr(255);
        for ($a = 1; $a <= 16; $a++) $msg .= $hw_addr;

        $s = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_set_option($s, SOL_SOCKET, SO_BROADCAST, 1);

        if ( socket_sendto($s, $msg, strlen($msg), 0, "255.255.255.255", 1223) !== false ) {
            debug_log(__METHOD__.":".__LINE__, "WOL sent to: ". $mac);
            return true;
        }
        else {
            debug_log(__METHOD__.":".__LINE__, "WOL not sent..");
            return false;
        }
    }

    public static function SSH($host, $command) {
        $connection = @ssh2_connect(Configs::getIP($host), 22, array('hostkey'=>'ssh-rsa'));
        if ( $connection === false ) {
            //debug_log(__METHOD__.":".__LINE__, "SSH connection failed on ". $host);
            return false;
        }
        if (!ssh2_auth_pubkey_file($connection,
                                    Configs::get("HOMEBRAIN", "USER"),
                                    Configs::get("HOMEBRAIN", "PUBKEY"),
                                    Configs::get("HOMEBRAIN", "PRIVKEY"))) {
            
            think("I'm not authorized to connect Mr. ". ucfirst($host) . ", you know ?!");
            //debug_log(__METHOD__.":".__LINE__, "SSH auth failed on ". $host);
            return false;
        }

        switch ($command) {
            case "shutdown":
            $cmd = "sudo /sbin/shutdown -h now";
            break;

            case "reboot":
            $cmd = "sudo /sbin/shutdown -r now";
            break;

            default:
            $cmd = $command;
        }

        $stream = ssh2_exec($connection, $cmd);
        stream_set_blocking($stream, true);

        return trim(stream_get_contents($stream));
    }

    public static function checkNetwork() {

        exec("sudo /usr/bin/nmap -n -sn 10.10.10.0/24 -sP", $out);

        foreach ($out as $host) {
            if ( strpos($host, "scan report for") !== false )
                $IPs[] = explode(" ", $host)[4];

            else if ( strpos($host, "MAC Address:") !== false ) {
                $MACs[] = explode(" ", $host)[2];
                $names[] = str_replace(")", "", str_replace("(", "", explode(" ", $host, 4)[3]));
            }
        }

        //debug_log(__METHOD__.":".__LINE__ ." MACs = ", $MACs);

        $ret = "";
        for ($i = 0; $i < count($MACs); $i++) {
            SQLITE::query("INSERT OR IGNORE INTO lan (mac, name) VALUES ('". $MACs[$i] ."', '". $names[$i] ."')");
            SQLITE::query("UPDATE lan SET timestamp = datetime(CURRENT_TIMESTAMP, 'localtime'), ip = '". $IPs[$i] ."' WHERE mac = '". $MACs[$i] ."'");
            $ret .= $MACs[$i] ." ". $IPs[$i] ." ". $names[$i] .PHP_EOL;
        }

	SQLITE::query("SELECT timestamp, name, mac, ip FROM lan WHERE strftime('%s', timestamp, 'localtime') > strftime('%s', 'now', '-2 hour', 'localtime') AND known = 0;");
	$unknown = SQLITE::getResult();
	foreach ($unknown as $k => $v) {
		if (date('i')*1 == 0) HomeBrain::notify("!Known device: ". implode("\n   ", $v));
	}
        return $ret;
    }

    public static function killInet($host) {
        exec("ssh 10.10.10.10 sudo /sbin/iptables -I INPUT -s ". $host ." -p udp --dport 53 -j DROP");
    }

    public static function allowInet($host) {
        $index = "";
        
        exec("ssh 10.10.10.10 sudo /sbin/iptables -L", $out);
        foreach ($out as $key => $line) {
            if (strpos($line, $host) !== false) {
                $index = $key - 1;
                break;
            }
        }
        
        if ($index != "") {
            exec("ssh 10.10.10.10 sudo /sbin/iptables -D INPUT " . $index);
        }
    }
}

?>
