<?php

class HomeBrain {
    public static $debug = true;
    
    public static function wakecheck() {
        // get old states from db
        $rows = SQLITE::fetch("states", ["name", "auto", "active"], 1);

        $oldStates = array();
        $newStates = array();

        foreach ( $rows as $row ) $oldStates[$row["name"]] = ["auto" => $row["auto"], "active" => $row["active"]];

        foreach ( $oldStates as $hostName => $values ) {
            $oldState = $values["active"];

            if ( strpos($hostName, " ") === false ) {
                $class = $hostName;
                $method = "on";
                $condition = $class;
                $newState = (int)$hostName::isOn();
            }

            else {
                $object = explode(" ", $hostName);
                $class = trim($object[0]);
                $method = trim($object[1]);
                $condition = $class." ".$method;
                $newState = (int)$class::$method();
            }

            $newStates[$hostName]["active"] = $newState;
            /*
            if ( $oldState != $newState ) {
                $msg = $class." is".(((bool)$newState) ? " " : " not ").$method.".";
                debug_log(__FILE__, $hostName . ": " . $newState . " `name`='".$condition."'");
                //SQLITE::update("states", "active", $newState, "`name`='".$condition."'");
            }
            */
        }

        // HomeServer is off
        if ( !(bool)$newStates["HomeServer"]["active"] ) {

            // wake HomeServer if:
            $reason = "";
            switch (true) {
                case ((bool)$newStates["KODI"]["active"]):
                    debug_log(__FILE__, "Waking HomeServer, KODI is on.");
                    $reason .= "KODI ";
                break;

                case ((HomeServer::getWakeTime()-time()) < 1800):
                    debug_log(__FILE__, "Waking HomeServer, it's WakeTime.");
                    $reason .= "WakeTime ".date("H:i d.m.", HomeServer::getWakeTime())." ";
                break;
            }
            if ( $reason != "" ) HomeServer::wake($reason);
        }

        // HomeServer is on
        else {

            // do NOT shutdown HomeServer if:
            switch (true)
            {
                case ((bool)$newStates["KODI"]["active"]):
                    hbrain_log(__FILE__, "KODI active, , HomeServer stays on.");

                case ((bool)$newStates["HomeServer busy"]["active"]):
                    hbrain_log(__FILE__, "HomeServer busy, HomeServer stays on.");
               
                case ((bool)$newStates["HomeBrain user"]["active"]):
                    hbrain_log(__FILE__, "HomeBrain user logged in, HomeServer stays on.");
                
                break;
                
                default: HomeServer::shut();                
            }
        }
        
        return null;
    }

    public static function user() {
		if ( $_POST["param1"] == "1" || $_POST["param1"] == "0" ) {
            SQLITE::update("states", "active", $_POST["param1"], "`name`='HomeBrain user'");
            return null;
        }
        
        else {
            $hbrainuser = exec("who | wc -l");
            return ($hbrainuser > 0) ? true : false;
        }
    }
    
    public static function mobAppConfig($token = null) {
        if ( $token === null ) $token = $_POST["param1"];
        $cfgMessage["pages"]    = ["home", "multimedia", "grijanje", "lan", "vrt"];
        $cfgMessage["homeUrl"]  = "10.10.10.10";

        Notifier::sendFcm ("HomeBrain", "APP config..", ["configs" => json_encode($cfgMessage)], $token);
    }

    public static function mobHeatUpdate($data = null) {
        if ( $data == null ) $data = $_POST["param1"];
        Notifier::fcmBcast("Heating", "update", array("heating" => $data));
    }

    public static function mobDbUpdate($row) {
        switch (true) {
            case (bool)strpos($row["state"], "user"):
            $msg = ["user is logged off..", "user is logged on!"];
            break;
            
            case (bool)strpos($row["state"], "busy"):
            $msg = ["is not busy..", "is busy!"];
            break;

            default:
            $msg = ["is off..", "is on!"];
        }
        //debug_log($row["state"] .", ". $msg[$row["changedto"]] .", ". '{"table":"changelog","values":'.json_encode($row).'}');
        $dbUpdates["table"] = "changelog";
        $dbUpdates["values"] = $row;
        Notifier::fcmBcast($row["state"], $msg[$row["changedto"]], array("dbupdates" => $dbUpdates));
    }

    public static function mobAppUpdate() {
        Notifier::fcmBcast("HomeBrain", "APP update..", array("appupdates" => $_POST["param1"]));
    }

    public static function reboot() {
        if ( Auth::allowedIP() ) return true;
        else return false;
    }

    public static function notify($msg) {

        $msg = str_replace("_", " ", $msg);
        $logMsg = "Not sent!";
        if (Notifier::fcmBcast("HomeBrain", $msg)) $logMsg = "FCM sent OK!";

        debug_log(__FILE__, 'Notifier::fcmBcast("HomeBrain", "'.$msg.'"); '.$logMsg);
    }
}

?>