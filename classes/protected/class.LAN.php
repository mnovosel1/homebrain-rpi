<?php

require_once DIR . "/classes/protected/class.Configs.php";

class LAN {

    public static function WOL($mac) {

        $addr_byte = explode(':', $mac);
        $hw_addr = '';
        for ($a=0; $a <6; $a++) $hw_addr .= chr(hexdec($addr_byte[$a]));
        $msg = chr(255).chr(255).chr(255).chr(255).chr(255).chr(255);
        for ($a = 1; $a <= 16; $a++) $msg .= $hw_addr;
        
        $s = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_set_option($s, SOL_SOCKET, SO_BROADCAST, 1);

        if ( socket_sendto($s, $msg, strlen($msg), 0, "255.255.255.255", 1223) ) return true;
        else return false;
    }

    public static function SSH($host, $command) {
        $connection = ssh2_connect(Configs::getIP("HomeServer"), 22);
        if ( $connection === false ) return false;
        ssh2_auth_password($connection, Configs::get("HomeServer", "user"), Configs::get("HomeServer", "pass"));
                
        switch ($command) {
            case "shutdown":
            $cmd = "sudo /sbin/shutdown -h now";
            break;

            case "chkserver":
            $cmd = "/home/server/chkServer";
            break;

            default: return false;
        }
		$ret = ssh2_exec($connection, $cmd);
		if ( $ret !== false ) return "OK";
    }
}

?>