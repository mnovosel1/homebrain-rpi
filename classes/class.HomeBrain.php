<?php

class HomeBrain {
    
    public static function wakecheck() {
        // get old states from db
        $rows = SQLITE::fetch("states", ["name", "auto", "active"], 1);

        $oldStates = array();
        $newStates = array();

        foreach ( $rows as $row ) $oldStates[$row[0]] = ["auto" => $row[1], "active" => $row[2]];

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

    public static function changedTo($state, $newValue) {
        debug_log($state);
        switch (true) {
            case (bool)strpos($state, "user"):
            $msg = ["user is logged off..", "user is logged on!"];
            break;
            
            case (bool)strpos($state, "busy"):
            $msg = ["is not busy..", "is busy!"];
            break;

            default:
            $msg = ["is off..", "is on!"];
            
        }        
        Notifier::fcmBcast($state, $msg[$newValue]);
    }
    
}

?>