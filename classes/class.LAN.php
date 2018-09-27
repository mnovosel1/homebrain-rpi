<?php

class LAN {
    public static $debug = false;

    public static function h() {
        return MyAPI::help(self::class);
    }

    public static function help() {
        return MyAPI::help(self::class);
    }

    public static function ping($host) {
        $live = exec("ping -c1 ".Configs::getIP($host)." | grep 'received' | awk -F ',' '{print $2}' | awk '{ print $1}'");
		if ($live > 0) { 
            debug_log(__FILE__, $host . " is live!");
            return true;
        }
        debug_log(__FILE__, $host . " is not live.");
		return false;
    }

    public static function WOL($mac) {
        $addr_byte = explode(':', $mac);
        $hw_addr = '';
        for ($a=0; $a <6; $a++) $hw_addr .= chr(hexdec($addr_byte[$a]));
        $msg = chr(255).chr(255).chr(255).chr(255).chr(255).chr(255);
        for ($a = 1; $a <= 16; $a++) $msg .= $hw_addr;
        
        $s = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_set_option($s, SOL_SOCKET, SO_BROADCAST, 1);

        if ( socket_sendto($s, $msg, strlen($msg), 0, "255.255.255.255", 1223) !== false ) return true;
        else return false;
    }

    public static function SSH($host, $command) {
        $connection = ssh2_connect(Configs::getIP($host), 22, array('hostkey'=>'ssh-rsa'));
        if ( $connection === false ) {
            hbrain_log(_FILE_, "SSH connection failed on ". $host);
            return false;
        }
        if (!ssh2_auth_pubkey_file($connection,
                                    Configs::get($host, "user"),
                                    Configs::get("pubkeyfile"),
                                    Configs::get("privkeyfile"))) {
            hbrain_log(_FILE_, "SSH login failed on ". $host);
            return false;
        }
                
        switch ($command) {
            case "shutdown":
            $cmd = "sudo /sbin/shutdown -h now";
            break;

            case "reboot":
            $cmd = "sudo /sbin/shutdown -r now";
            break;
            
            case "getWaketime":
            $cmd = "/home/hbrain/getWakeTime";
            break;
                        
            case "chkserver":
            $cmd = "/home/hbrain/chkServer";
            break;

            default:
            $cmd = $command;
        }
        $stream = ssh2_exec($connection, $cmd);
        stream_set_blocking($stream, true);
		return trim(stream_get_contents($stream));
    }

    public static function checkNetwork() {
        exec("sudo nmap 10.10.10.0/24 -sP | grep 'MAC' | cut -c14-99", $out);
        $out = explode(" ", $out, 2);
        hbrain_log(__FILE__, $out);

        $ret = "";
        foreach ($out as $macName) {
            $macName = explode(" ", $macName, 2);
            $macName[1] = str_replace(")", "", str_replace("(", "", $macName[1]));
            SQLITE::insert("lan", ["mac", "name"], ["'". $macName[0] ."'", "'". $macName[1] ."'"]);
            $ret .= $macName[0] ." ". $macName[1] .PHP_EOL;
        }
        return $ret;
    }
}

?>