<?php

class HomeBrain {

    public static function h() {
        return MyAPI::help(HomeBrain::class);
    }

    public static function help() {
        return MyAPI::help(HomeBrain::class);
    }

    public static function toDo() {

        for ($i = 0; $i <= 3; $i++) {
            $rows = SQLITE::fetch("logic", ["weight", "name", "changedto"],
                                    "hour BETWEEN ". date("H") ."
                                            AND ". date("H", strtotime("+".$i." hour")) ."
                                    AND statebefore = (SELECT group_concat(active, '') FROM states)");

            $todoNotify = "";
            $todoRet = "";
            foreach ($rows as $row) {
                $todoNotify .= "[". $row["weight"] ."] ". $row["name"] ." to ".$row["changedto"] . PHP_EOL;
                $todoRet .= $row["name"] .":". $row["changedto"] .":". $row["weight"] ."|";
            }
            if ($todoNotify != "") break;
        }

        if ($todoNotify != "") {
            HomeBrain::notify($todoNotify);
        } else $todoRet = "Dunno.. ";

        return substr($todoRet, 0, strlen($todoRet)-1);
    }

    public static function allOff() {
	Amp::off();
        TV::off();
        KODI::off();
        MPD::off();
        LAN::SSH("KODI", "sudo /sbin/shutdown -r now > /dev/null &");
    }

    public static function dbBackup() {
        SQLITE::dbdump();
        exec("chmod -R 0770 ". DIR ."/var");
        exec("cp -a ". DIR ."/var/* ". DIR ."/saved_var");
    }

    public static function dbRestore($fromDump = "false") {

        if ($fromDump == "true") {
            exec("rm ". DIR ."/var/hbrain.db");
            exec("sqlite3 ". DIR ."/var/hbrain.db < ". DIR ."/var/hbrain.sql");
        }

        else {
            exec("cp -f ". DIR ."/saved_var/* ". DIR ."/var");
        }
    }

    public static function speedTest() {
        if (HomeBrain::isOnline() === true) {
            exec("speedtest-cli --simple", $result);
            return $result;
        }
        return "false";
    }

    public static function isOnline() {
        $online = exec("ping -c1 google.com | grep 'received' | awk -F ',' '{print $2}' | awk '{ print $1}'");
        if ($online == 1 ) return true;
        // exec(DIR ."/homebrain hbrain alert 15");
        return "false";
    }

    public static function getInfo() {
        exec("/usr/bin/php ". DIR ."/classes/helpers/getfindata.php", $findata);
        return $findata;
    }

    public static function wakeCheck() {

	hbrain_log(__METHOD__, "WakeChecking..");

        // get old states from db
        $rows = SQLITE::fetch("states", ["name", "auto", "active"], 1);

        $oldStates = array();
        $newStates = array();

        foreach ( $rows as $row ) $oldStates[$row["name"]] = ["auto" => $row["auto"], "active" => $row["active"]];

        foreach ( $oldStates as $hostName => $values ) {
            $oldState = $values["active"];

            if ( strpos($hostName, " ") === false ) {
                $class = $hostName;
                $condition = $class;
                debug_log(__METHOD__, $class ."::isOn();");
                $newState = ($hostName::isOn() == "false") ? 0 : 1;
            }

            else {
                $object = explode(" ", $hostName);
                $class = trim($object[0]);
                $method = trim($object[1]);
                $condition = $class." ".$method;
                debug_log(__METHOD__, $class ."::". $method ."();");
                $newState = ($class::$method() == "false") ? 0 : 1;
            }

            $newStates[$hostName]["active"] = $newState;

            /*
            if ( $oldState != $newState ) {
                $msg = $class." is".(((bool)$newState) ? " " : " not ").$method.".";
                debug_log(__METHOD__, $hostName . ": " . $newState . " `name`='".$condition."'");
                //SQLITE::update("states", "active", $newState, "`name`='".$condition."'");
            }
            */
        }

        // HomeServer is off
        if ( !(bool)$newStates["HomeServer"]["active"] ) {

	    $srvWakeTime = HomeServer::getWakeTime();
            // wake HomeServer if:
            $reason = "";
            switch (true) {
                case ((bool)$newStates["KODI"]["active"]):
                    hbrain_log(__METHOD__, "Waking HomeServer, KODI is on.");
                    $reason .= "KODI ";
                break;

                case (($srvWakeTime-time()) < 1800):
                    hbrain_log(__METHOD__, "Waking HomeServer, it's WakeTime: ".date("H:i d.m.", $srvWakeTime));
		    Notifier::fcmBcast("HomeBrain", "it's HomeServer WakeTime (".date("H:i d.m.", $srvWakeTime).")");
                    $reason .= "WakeTime ".date("H:i d.m.", $srvWakeTime)." ";
                break;
            }
            if ( $reason != "" ) HomeServer::wake($reason);
        }

        // HomeServer is on
        else {
            $shutDownHomeServer = true;

            // do NOT shutdown HomeServer if:
            if ((bool)$newStates["KODI"]["active"]) {
                $shutDownHomeServer = false;
                hbrain_log(__METHOD__, "KODI is active, HomeServer stays on."); 
            }

            if ((bool)$newStates["HomeServer busy"]["active"]) {
                $shutDownHomeServer = false;
                hbrain_log(__METHOD__, "HomeServer is busy, HomeServer stays on.");
            }

            if ((bool)$newStates["HomeBrain user"]["active"]) {
                $shutDownHomeServer = false;
                hbrain_log(__METHOD__, "HomeBrain user is logged on, HomeServer stays on.");
            }

            if ($shutDownHomeServer) HomeServer::shut();
        }

        // TV is off, KODI is on
        if ( !(bool)$newStates["TV"]["active"] && (bool)$newStates["KODI"]["active"] ) {
            KODI::off();

            // ..and it's silentTime
            if (HomeBrain::isSilentTime()) {
                MPD::off();
                Amp::off();
            }
        }

        return null;
    }

    public static function user() {
		if ( $_POST["param1"] == "1" || $_POST["param1"] == "0" ) {
            $active = (int)$_POST["param1"];
        }

        else {
            $hbrainuser = exec("who | wc -l");
            $active = ($hbrainuser > 0) ? 1 : 0;
        }

        SQLITE::update("states", "active", $active, "`name`='HomeBrain user'");

        return ($active > 0) ? "true" : "false";
    }

    public static function mobAppConfig($token = null) {
        if ( $token === null ) $token = $_POST["param1"];
        $cfgMessage["pages"]    = ["home", "mmkodi", "mmmpd", "grijanje", "lan", "vrt"];
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
            // if ($row["changedto"] == 1) exec(DIR ."/homebrain hbrain alert 3 &"); // alert if server is busy!
            break;

            default:
            // if ($row["state"] == "HomeServer" && $row["changedto"] == 1) exec(DIR ."/homebrain hbrain alert 3 &"); // alert if server is on
            $msg = ["is off..", "is on!"];
        }
        hbrain_log(__METHOD__, $row["state"] ." ". $msg[$row["changedto"]]);
        debug_log(__METHOD__, $row["state"] .'{"table":"changelog","values":'.json_encode($row).'}');

        $dbUpdates["table"] = "changelog";
        $dbUpdates["values"] = $row;
        Notifier::fcmBcast($row["state"], $msg[$row["changedto"]], array("dbupdates" => $dbUpdates));
    }

    public static function mobAppUpdate() {
        Notifier::fcmBcast("HomeBrain", "APP update..", array("appupdate" => $_POST["param1"]));
    }

    public static function reboot() {
        if ( Auth::allowedIP() ) return "true";
        else return "false";
    }

    public static function isSilentTime() {
        $silentTimeStart = Configs::get("SILENT_TIME", "START");
        $silentTimeEnd = Configs::get("SILENT_TIME", "END");
        $timeNow = date('G');

        if ($timeNow > $silentTimeStart || $timeNow < $silentTimeEnd) {
            hbrain_log(__METHOD__, "SilentTime is from ".$silentTimeStart." to ".$silentTimeEnd."h.");
            return true;
        }
        return false;
    }

    public static function notify($msg) {
        Notifier::notify($msg);
    }

    public static function alert($secs) {
        Notifier::alert($secs);
    }

    public static function speak($text) {
        Notifier::speak($text);
    }

    ///// HomeBrain::uploadData() /////////////////////////////////////////////////////////////
    public static function uploadData() {

        $res = SQLITE::mySqlQuery("SELECT unix_timestamp(timestamp) AS timestamp ".
                                    "FROM changelog ".
                                    "ORDER BY timestamp DESC LIMIT 1");

	SQLITE::query("SELECT * FROM changelog ".
				"WHERE STRFTIME('%s', timestamp)*1 >= ".$res[1] ." ".
				"ORDER BY timestamp ASC");
	$rows = SQLITE::getResult();
	hbrain_log(__METHOD__, "Uploading ". count($rows) ." rows to changelog, since ". date("d.m.Y H:i:s", $res[1]));
	foreach ($rows as $row) {

		$row['light'] = empty(trim($row['light'])) ? "NULL" : $row['light'];
		$row['tempin'] = empty(trim($row['tempin'])) ? "NULL" : $row['tempin'];
		$row['tempout'] = empty(trim($row['tempout'])) ? "NULL" : $row['tempout'];
		$row['sound'] = empty(trim($row['sound'])) ? "NULL" : $row['sound'];

		$sql = "INSERT INTO changelog ".
				"VALUES('".$row['timestamp']."', ".
					"'".$row['statebefore']."', ".
					$row['light'].", ".
					$row['tempin'].", ".
					$row['tempout'].", ".
					$row['sound'].", ".
					"'".$row['state']."', ".
					$row['changedto'].")";
		SQLITE::mySqlQuery($sql);
	        //debug_log(__METHOD__, __LINE__.": row = ".var_export($sql, true));
	}
    }

    ///// HomeBrain::getTemps() ///////////////////////////////////////////////////////////////
    public static function getTemps() {
        $timestamp = date("Y-m-d H:i:00");

        $in = Weather::tempIn($timestamp);
        $inArr = explode(":", $in);

        $out = Weather::tempOut($timestamp);
        $outArr = explode(":", $out);

        SQLITE::insert("datalog",
                        ["timestamp",
                        "tempset",
                        "tempin",
                        "tempout",
                        "heatingon",
                        "humidin",
                        "humidout",
                        "light",
                        "sound"],
                        ["'". $timestamp ."'",
                        "(SELECT tempinavg FROM tempconf
                                    WHERE hour = STRFTIME('%H', DATETIME('now', 'localtime')) * 1
                                        AND wday = STRFTIME('%w', DATETIME('now', 'localtime')) * 1)",
                        $inArr[0],
                        $outArr[0],
                        "(SELECT active FROM states WHERE name = 'Heating')",
                        $inArr[1],
                        $outArr[1],
                        $inArr[2],
                        $inArr[3]],
                        true);

        return $in .":". $out;
    }

    public static function debug() {
        $numArgs = func_num_args();

        if ($numArgs == 1) {
            $args = func_get_args();
            Configs::set("DEBUG", $args[0]);
        }

        return implode(", ", Configs::get("DEBUG"));
    }
}

?>
