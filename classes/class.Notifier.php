<?php

/* FCM: TimeToLive */
define('TTL', 86400);

class Notifier {
    public static $debug = false;

    public static function h() {
        return MyAPI::help(Notifier::class);
    }

    public static function help() {
        return MyAPI::help(Notifier::class);
    }

    public static function notify($msg, $title = "HomeBrain") {
        Notifier::kodi($msg, $title);

        if ( HomeBrain::isSilentTime() ) return;
        
        $msg = str_replace("_", " ", $msg);
        if ( Notifier::fcmBcast($title, $msg) ) return null;
        return false;
    }

    public static function kodi($msg, $title = "HomeBrain") {
        if ( !Auth::allowedIP() ) return false;
        $data = Notifier::getPostData();
        LAN::SSH("KODI", "/usr/bin/kodi-send -a 'Notification(". $title .", ". $msg .")'");
        //exec('curl -X POST -H "Content-Type: application/json" -d \'{"jsonrpc":"2.0","method":"GUI.ShowNotification","params":{"title":"'.$title.'","message":"'.$msg.'"},"id":1}\' http://10.10.10.25:80/jsonrpc 2>/dev/null');
        return true;
    }

    public static function fcmBcast($title, $msg, $data = null) {
        if ( !Auth::allowedIP() ) return false;
        hbrain_log(__METHOD__, $title .": ". $msg);
        if ( $title === null ) $title = "HomeBrain";

        Notifier::kodi($msg, $title);

        $tokens = SQLITE::fetch("fcm", ["token"], "approved='true'");
        foreach ( $tokens as $tok ) Notifier::sendFcm($title, $msg, $data, $tok['token']);
        return true;
    }

    public static function alert($secs) {
        if ( HomeBrain::isSilentTime() ) return;
        exec('sudo '. DIR .'/bin/nrf 0 on >/dev/null 2>&1 && sleep '. $secs .' && sudo '. DIR .'/bin/nrf 0 off >/dev/null 2>&1 &');
    }

    public static function speak($text) {
        if ( HomeBrain::isSilentTime() ) return;
        LAN::SSH("KODI", "/usr/bin/flite -voice slt -t '". $text ."' &");
    }

    //* private helper methods *///////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////
    public static function sendFcm ($title, $msg, $data, $token, $ttl = null) {

        hbrain_log(__METHOD__, $title .": ". $msg);

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

        debug_log(__METHOD__, $fields);

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
        debug_log(__METHOD__, json_encode( $fields ));

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
