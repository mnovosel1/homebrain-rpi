<?php

/* FCM: TimeToLive */
define('TTL', 86400);

class Notifier {
    public static $debug = true;

    public static function notify($msg, $title = "HomeBrain") {
        if ( !Auth::allowedIP() ) return false;
        self::kodi();
        
        if ( self::fcmBcast($title, $msg) ) return null;
        return false;
    }

    public static function kodi($msg, $title = "HomeBrain") {
        if ( !Auth::allowedIP() ) return false;
        $data = self::getPostData();

        exec('curl -X POST -H "Content-Type: application/json" -d \'{"jsonrpc":"2.0","method":"GUI.ShowNotification","params":{"title":"'.$title.'","message":"'.$msg.'"},"id":1}\' http://10.10.10.20:80/jsonrpc 2>/dev/null');
        return true;
    }

    public static function fcmBcast($title, $msg, $data = null) {
        if ( !Auth::allowedIP() ) return false;
        if ( $title === null ) $title = "HomeBrain";
        
        $tokens = SQLITE::fetch("fcm", ["token"], "approved='true'");
        foreach ( $tokens as $tok ) self::sendFcm($title, $msg, $data, $tok['token']);
        return true;
    }

    //* private helper methods *///////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////
    public static function sendFcm ($title, $msg, $data, $token, $ttl = null) {


        if ( $ttl === null ) $ttl = TTL;
        if ( $title === null ) $title = "HomeBrain";
        else $title = explode(" ", $title)[0];

        // NOTIFICATION only message
        if ( $data === null ) {

            $fields["notification"]["title"]    = $title;
            $fields["notification"]["body"]     = $msg;
            $fields["notification"]["sound"]    = strtolower($title);
        }
        
        // DATA message
        else {
            if ( $data !== null ) { 
                $fields['data'] = $data;
            }
            
            $fields['data']['title']    = $title;
            $fields['data']['body'] 	= $msg;
        }

        $fields["to"]               = $token;
        $fields["time_to_live"]     = $ttl;

        //debug_log($fields);

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

        if ( $result->failure > 0 ) $success = false;
        else $success = true;

        $fields["success"] = $success;        
        debug_log(__FILE__, json_encode( $fields ));

        if (!$success) SQLITE::query("DELETE FROM fcm WHERE token = '".$token."'");

        return $success;
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