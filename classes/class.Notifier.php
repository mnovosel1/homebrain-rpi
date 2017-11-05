<?php

require_once DIR . "/classes/protected/class.Configs.php";
require_once DIR . "/classes/protected/class.SQLITE.php";


/* FCM: TimeToLive */
define('TTL', 300);

class Notifier {

    public static function kodi() {

        // allowed IPs
        if ( !Auth::allowedIP([Configs::getIP("HomeServer")]) ) return false;

        $data = self::getPostData();

        exec('curl -X POST -H "Content-Type: application/json" -d \'{"jsonrpc":"2.0","method":"GUI.ShowNotification","params":{"title":"'.$data["title"].'","message":"'.$data["msg"].'"},"id":1}\' http://10.10.10.20:80/jsonrpc 2>/dev/null');
        return true;
    }

    public static function fcm() {

        return true;
    }

    //* private methods - helpers *////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////
    private static function sendFcm ($title, $msg, $data, $token, $ttl = TTL) {

        $fields["to"]               = $token;
        $fields["time_to_live"]     = TTL;

        $fields['data']['title']    = $title;
        $fields['data']['msg'] 	    = $msg;
        $fields['data']['data']	    = $data;

        $headers[] = 'Authorization: key='.Configs::getFCM("API_KEY");
        $headers[] = 'Content-Type: application/json';

        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, Configs::getFCM("URL") );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
        $result = json_decode(curl_exec($ch));
        curl_close( $ch );

        if ( $result->failure > 0 ) return false;
        else return true;

    }

    private static function getPostData() {

        if ( strpos($_POST["param1"], "|") !== false ) {

            $tmp = explode("|", $_POST["param1"]);

            $data["title"] = $tmp[0];
            $data["msg"] = $tmp[1];
        }

        else {

            $data["title"] = "HomeBrain";
            $data["msg"] = $_POST["param1"];
        }

        return $data;
    }
}

?>