<?php

class Auth {

    public static $debug = false;

    public static function OK() {

        if ( isset($_POST["secToken"]) ) {

            $reqtime = floor($_SERVER["REQUEST_TIME"]/20);

            /*
            debug_log(__METHOD__, "token rcvd: ".$_POST['secToken']);
            debug_log(__METHOD__, md5('H' . $reqtime));
            debug_log(__METHOD__, md5('o' . $reqtime));
            debug_log(__METHOD__, md5('m' . $reqtime));
            debug_log(__METHOD__, md5('e' . $reqtime));
            debug_log(__METHOD__, md5('B' . $reqtime));
            debug_log(__METHOD__, md5('r' . $reqtime));
            debug_log(__METHOD__, md5('a' . $reqtime));
            debug_log(__METHOD__, md5('i' . $reqtime));
            debug_log(__METHOD__, md5('n' . $reqtime));
            */

            switch (true) 
            {
                case ( $_POST['secToken'] == md5('H' . $reqtime) ):
                case ( $_POST['secToken'] == md5('o' . $reqtime) ):
                case ( $_POST['secToken'] == md5('m' . $reqtime) ):
                case ( $_POST['secToken'] == md5('e' . $reqtime) ):
                case ( $_POST['secToken'] == md5('B' . $reqtime) ):
                case ( $_POST['secToken'] == md5('r' . $reqtime) ):
                case ( $_POST['secToken'] == md5('a' . $reqtime) ):
                case ( $_POST['secToken'] == md5('i' . $reqtime) ):
                case ( $_POST['secToken'] == md5('n' . $reqtime) ):

                    return true;
                break;
            }
        }

        return false;
    }

    public static function allowedIP($arrayIPs = []) {

        if ( $arrayIPs == "ANY" ) return true;
        
        $allowedIPs[] = Configs::getIP("HomeBrain", "IP"); // HomeBrain is allways allowed
        foreach ( $arrayIPs as $ip ) $allowedIPs[] = $ip;

        if ( array_search($_SERVER["REMOTE_ADDR"], $allowedIPs) === false ) {
            hbrain_log(__METHOD__, "IP not allowed");
            return false;
        }

        return true;
    }
}

?>