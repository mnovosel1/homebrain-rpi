<?php

/* FCM: TimeToLive */
define('TTL', 300);

class Notifier {

    public static function notify() {
        if ( !Auth::allowedIP() ) return false;
        self::kodi();
        $data = self::getPostData();
        if ( self::fcmBcast($data["title"], $data["msg"]) ) return "Notify sent..";
    }

    public static function kodi() {
        if ( !Auth::allowedIP() ) return false;
        $data = self::getPostData();

        exec('curl -X POST -H "Content-Type: application/json" -d \'{"jsonrpc":"2.0","method":"GUI.ShowNotification","params":{"title":"'.$data["title"].'","message":"'.$data["msg"].'"},"id":1}\' http://10.10.10.20:80/jsonrpc 2>/dev/null');
        return true;
    }

    public static function fcmBcast($title, $msg, $data = null) {
        if ( !Auth::allowedIP() ) return false;
        if ( $title === null ) $title = "HomeBrain";
        $tokens = SQLITE::fetch("fcm", ["token"], 1);
        foreach ( $tokens as $tok ) self::sendFcm($title, $msg, $data, $tok[0]);
        return true;
    }

    //* private methods - helpers *////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////
    private static function sendFcm ($title, $msg, $data, $token, $ttl = null) {

        if ( $ttl === null ) $ttl = 300;
        if ( $title === null ) $title = "HomeBrain";
        else $title = explode(" ", $title)[0];

        $fields["to"]               = $token;
        $fields["time_to_live"]     = $ttl;

        // NOTIFICATION only message
        if ( $data === null ) {

            $fields["notification"]["title"]    = $title;
            $fields["notification"]["body"]     = $msg;
            $fields["notification"]["sound"]    = strtolower($title);
        }
        
        // DATA message
        else {
            $fields['data']['title']    = $title;
            $fields['data']['msg'] 	    = $msg;
            if ( $data !== null ) $fields['data']['data'] = $data;
        }

        debug_log($fields);

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
            if ( isset($tmp[2]) ) $data["data"] = $tmp[2];
        }

        else {
            $data["title"] = "HomeBrain";
            $data["msg"] = $_POST["param1"];
        }

        return $data;
    }
}

?>