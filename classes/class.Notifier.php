<?php

/* FCM: TimeToLive */
define('TTL', 86400);

class Notifier {
    public static $debug = false;
    static $clock1Notifying = false;

    public static function h() {
        return MyAPI::help(Notifier::class);
    }

    public static function help() {
        return MyAPI::help(Notifier::class);
    }

    public static function notify($msg, $title = "HomeBrain") {
        debug_log(__METHOD__.":".__LINE__, $msg);
	    Notifier::kodi($msg, $title);

        $msg = str_replace("_", " ", $msg);
        if ( Notifier::fcmBcast($title, $msg) ) return null;
        return false;
    }

    public static function kodi($msg, $title = "HomeBrain") {
	debug_log(__METHOD__.":".__LINE__, $msg);
        // if ( !Auth::allowedIP() ) return false;
        $data = Notifier::getPostData();
        LAN::SSH("KODI", "/usr/bin/kodi-send -a 'Notification(". $title .", ". $msg .")'");
        //exec('curl -X POST -H "Content-Type: application/json" -d \'{"jsonrpc":"2.0","method":"GUI.ShowNotification","params":{"title":"'.$title.'","message":"'.$msg.'"},"id":1}\' http://10.10.10.25:80/jsonrpc 2>/dev/null');
        return true;
    }

    public static function fcmBcast($title, $msg, $data = "none") {
        // if ( !Auth::allowedIP() ) return false;
        if ( $title === null ) $title = "HomeBrain";

        $tokens = SQLITE::query("SELECT token, email FROM fcm WHERE approved='true'");
        foreach ( $tokens as $tok ) {
            hbrain_log(__METHOD__.":".__LINE__, $title .": ". $msg ." -> ". $tok['email']);
            Notifier::sendFcm($title, $msg, $data, $tok['token']);
        }
        return true;
    }

    public static function alert($secs) {
	hbrain_log(__METHOD__.":".__LINE__, "Alerting ". $secs ." secs.");
	exec('sudo '. DIR .'/bin/nrf 0 on >/dev/null 2>&1 && sleep '. $secs .' && sudo '. DIR .'/bin/nrf 0 off >/dev/null 2>&1 && sudo '. DIR .'/bin/nrf 0 off >/dev/null 2>&1 &');
    }

    public static function speak($text) {
        LAN::SSH("KODI", "/usr/bin/flite -voice slt -t '". $text ."' &");
    }

    public static function rgb($r = NULL, $g = NULL, $b = NULL) {

        $light = SQLITE::query("SELECT light FROM datalog ORDER BY timestamp DESC LIMIT 1")[0]["light"];

        if ($light <= Configs::get("LIGHT", "MIN") || HomeBrain::isSilentTime()) {
            if ($r !== NULL) $r = 0;
            if ($g !== NULL) $g = 0;
            if ($b !== NULL) $b = 0;
        }

        else {
            $lightCorrection = round($light/100)-1;

            if ($r !== NULL && $r != 0) {
                $r += $lightCorrection;
                $r = $r < 0 ? 0 : $r;
                $r = $r > 255 ? 255 : $r;
            }

            if ($g !== NULL && $g != 0) {
                $g += $lightCorrection;
                $g = $g < 0 ? 0 : $g;
                $g = $g > 255 ? 255 : $g;
            }

            if ($b !== NULL && $b != 0) {
                $b += $lightCorrection;
                $b = $b < 0 ? 0 : $b;
                $b = $b > 255 ? 255 : $b;
            }
        }

        debug_log(__METHOD__.":".__LINE__, "RGB: ". $r .", ". $g .", ". $b);

        if ($r !== NULL) exec(DIR ."/bin/red ". $r);
        if ($g !== NULL) exec(DIR ."/bin/green ". $g);
        if ($b !== NULL) exec(DIR ."/bin/blue ". $b);
    }

    public static function notifyClock1 ($text, $beep = 0) {        

        if (HomeBrain::isSilentTime()) return;

        while (Notifier::$clock1Notifying) sleep(5);
        Notifier::$clock1Notifying = true;

        debug_log(__METHOD__.":".__LINE__, $text);

        $text = str_split($text, 25);
        $lines = count($text);
        if ($lines > 9) $lines = 9;

        foreach($text as $key => $line) {
            exec("sudo ". DIR ."/bin/nrf 8 'no". ($key+1) . $lines. $beep .":". $line ."'");
            sleep(1);
            if ($key+1 == 9) break;
        }
        
        Notifier::$clock1Notifying = false;
    }

    //* private helper methods *///////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////
    public static function sendFcm ($title, $msg, $data, $token, $ttl = null) {

        // hbrain_log(__METHOD__.":".__LINE__, $title .": ". $msg);

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
            $fields['data']['data']     = $data;
            $fields['data']['title']    = $title;
            $fields['data']['body'] 	= $msg;
        }

        $fields["to"]               = $token;
        $fields["time_to_live"]     = $ttl;

        debug_log(__METHOD__.":".__LINE__, $fields);

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
        debug_log(__METHOD__.":".__LINE__, json_encode( $fields ));

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
