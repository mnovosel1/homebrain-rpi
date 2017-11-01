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
}

?>