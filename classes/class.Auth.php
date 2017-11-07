<?php

class Auth {

    public static function OK() {

        if ( isset($_POST["secToken"]) ) {

            $reqtime = floor($_SERVER["REQUEST_TIME"]/20);
            
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
        
        $allowedIPs[] = Configs::getIP("HomeBrain"); // HomeBrain is allways allowed
        foreach ( $arrayIPs as $ip ) $allowedIPs[] = $ip;

        if ( array_search($_SERVER["REMOTE_ADDR"], $allowedIPs) === false ) {
            if ( DEBUG ) file_put_contents(DIR.'/'.Configs::get("DEBUG_LOG"), PHP_EOL. "IP not allowed!" .PHP_EOL . PHP_EOL, FILE_APPEND);
            return false;
        }

        return true;
    }
}

?>