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

        if ( socket_sendto($s, $msg, strlen($msg), 0, "255.255.255.255", 1223) !== false ) {
            debug_log(__FILE__, "WOL sent..");
            return true;
        }
        else {
            debug_log(__FILE__, "WOL not sent..");
            return false;
        }
    }

    public static function SSH($host, $command) {
        $connection = ssh2_connect(Configs::getIP($host), 22, array('hostkey'=>'ssh-rsa'));
        if ( $connection === false ) {
            hbrain_log(__FILE__, "SSH connection failed on ". $host);
            return false;
        }
        if (!ssh2_auth_pubkey_file($connection,
                                    Configs::get($host, "user"),
                                    Configs::get("pubkeyfile"),
                                    Configs::get("privkeyfile"))) {
            hbrain_log(_FILE_, "SSH auth failed on ". $host);
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

        exec("sudo nmap 10.10.10.0/24 -sP", $out);

        foreach ($out as $host) {
            if ( strpos($host, "scan report for") !== false ) 
                $IPs[] = explode(" ", $host)[4];

            else if ( strpos($host, "MAC Address:") !== false ) {
                $MACs[] = explode(" ", $host)[2]; 
                $names[] = str_replace(")", "", str_replace("(", "", explode(" ", $host, 4)[3]));
            }
        }

        $ret = "";
        for ($i = 0; $i < count($MACs); $i++) {
            SQLITE::query("INSERT OR IGNORE INTO lan (mac, name) VALUES ('". $MACs[$i] ."', '". $names[$i] ."')");
            SQLITE::query("UPDATE lan SET timestamp = datetime(CURRENT_TIMESTAMP, 'localtime'), ip = '". $IPs[$i] ."' WHERE mac = '". $MACs[$i] ."'");
            $ret .= $MACs[$i] ." ". $IPs[$i] ." ". $names[$i] .PHP_EOL;
        }
        return $ret;
    }
}

?>