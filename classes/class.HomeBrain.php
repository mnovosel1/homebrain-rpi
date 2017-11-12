<?php

class HomeBrain {
    
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

            if ( $oldState != $newState ) {
                $msg = $class." is".(((bool)$newState) ? " " : " not ").$method.".";
                SQLITE::update("states", "active", $newState, "`name`='".$condition."'");
            }
        }

        // HomeServer is off
        if ( !(bool)$newStates["HomeServer"]["active"] ) {

            // wake HomeServer if:
            switch (true) {
                case ((bool)$newStates["KODI"]["active"]):
                case ((HomeServer::getWakeTime()-time()) < 1800):
                    HomeServer::wake();
                break;

                default: break;
            }
        }

        // HomeServer is on
        else {

            // do NOT shutdown HomeServer if:
            switch (true)
            {
                case ((bool)$newStates["KODI"]["active"]):
                case ((bool)$newStates["HomeServer busy"]["active"]):
                case ((bool)$newStates["HomeBrain user"]["active"]):
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
    
    public static function mobAppConfig($token) {
        
        $cfgMessage["pages"]    = ["home", "multimedia", "grijanje", "lan", "vrt"];
        $cfgMessage["homeUrl"]  = "10.10.10.10";

        Notifier::sendFcm ("HomeBrain", "configuring mobile app..", ["configs" => json_encode($cfgMessage)], $token);
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
        Notifier::fcmBcast($row["state"], $msg[$row["changedto"]], array("data" => '{"table":"changelog","values":'.(json_encode($row)).'}'));
    }

    public static function mobAppUpdate() {
        Notifier::fcmBcast("HomeBrain", "application update..", array("configs" => $_POST["param1"]));
    }
}

?>