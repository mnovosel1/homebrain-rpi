<?php

class HomeBrain {

    public static function h($class = self::class) {
        return self::help($class);
    }

    public static function help($class = self::class) {

        $methods = get_class_methods($class);

        $ret = $class .': ';
        foreach ($methods as $method) {
            $ret .= $method .", ";
        }

        return substr($ret, 0, strlen($ret)-2);
    }

    public static function toDo() {

        for ($i = 0; $i <= 3; $i++) {
            $rows = SQLITE::query("SELECT weight, name, changedto
                                    FROM logic
                                    WHERE hour BETWEEN ". date("H") ."
                                            AND ". date("H", strtotime("+".$i." hour")) ."
                                    AND statebefore = (SELECT group_concat(active, '') FROM states)
                                    ORDER BY weight DESC LIMIT 1");

            $todoNotify = "";
            $todoRet = "";
            foreach ($rows as $row) {
                $todoNotify .= "[". $row["weight"] ."] ". $row["name"] ." to ".$row["changedto"] . PHP_EOL;
		        think("Should I set ". $row["name"] ." to ". $row["changedto"] ." ?");
                $todoRet .= $row["name"] .":". $row["changedto"] .":". $row["weight"] ."|";
            }
            if ($todoNotify != "") break;
        }

        if ($todoNotify != "") {
            HomeBrain::notify($todoNotify);
        } else $todoRet = "Dunno.. ";

        return "ToDo: ". substr($todoRet, 0, strlen($todoRet)-1);
    }

    public static function allOff() {
        TV::off();
        MPD::off();
        Amp::volDown(10);
        Amp::off();
        KODI::off();
        Light::off("light/tv");
        Light::off("light/lr", 1);
        Light::off("light/lr", 2);
    }

    public static function dbBackup() {
        think("Doing database backup.");
        SQLITE::dbdump();
        exec("chmod -R 0770 ". DIR ."/var");
        exec("cp -a ". DIR ."/var/* ". DIR ."/saved_var"); /**/
    }

    public static function dbRestore($fromDump = "false") {

        if ($fromDump == "true") {
            exec("rm ". DIR ."/var/hbrain.db");
            exec("sqlite3 ". DIR ."/var/hbrain.db < ". DIR ."/var/hbrain.sql");
        }

        else {
            exec("cp -f ". DIR ."/saved_var/* ". DIR ."/var"); /**/
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

        //debug_log(__METHOD__.":".__LINE__, "WakeChecking..");

        // get old states from db
        $rows = SQLITE::query("SELECT name, auto, active FROM states");

        $oldStates = array();
        $newStates = array();

        foreach ( $rows as $row ) $oldStates[$row["name"]] = ["auto" => $row["auto"], "active" => $row["active"]];

        foreach ( $oldStates as $hostName => $values ) {
            $oldState = $values["active"];

            if ( strpos($hostName, " ") === false ) {
                $class = $hostName;
                //debug_log(__METHOD__.":".__LINE__, $class ."::isOn();");
                $newState = ($class::isOn() == "false") ? 0 : 1;
            }

            else {
                $object = explode(" ", $hostName);
                $class = trim($object[0]);
                $method = trim($object[1]);
                //debug_log(__METHOD__.":".__LINE__, $class ."::". $method ."();");
                $newState = ($class::$method() == "false") ? 0 : 1;
            }

	    // if ( $oldState != $newState ) think(ucfirst($hostName) ." is now ". (($newState == "false") ? "off" : "on") .". (". $oldState ."/". $newState . ")");

            $newStates[$hostName]["active"] = $newState;
        }

        // HomeServer is off
        if ( $newStates["HomeServer"]["active"] == 0 ) {

	        $srvWakeTime = HomeServer::getWakeTime();
            // wake HomeServer if:
            $reason = "";
            switch (true) {
/*                case ((bool)$newStates["KODI"]["active"]):
                    think("KODI is on, I'm waking up Homeserver!");
                    hbrain_log(__METHOD__.":".__LINE__, "Waking HomeServer, KODI is on.");
                    $reason .= "KODI ";
                break;
*/
                case (($srvWakeTime-time()) < 1800):
                    think("It's time to wake HomeServer: ". date("H:i d.m.", $srvWakeTime) .".");
                    hbrain_log(__METHOD__.":".__LINE__, "Waking HomeServer, it's WakeTime: ".date("H:i d.m.", $srvWakeTime));
                    $reason .= "WakeTime ".date("H:i d.m.", $srvWakeTime)." ";
                break;
            }
            if ( $reason != "" ) HomeServer::wake($reason);
        }

        // HomeServer is on
        else {
            $shutDownHomeServer = true;
            $thought = "";

            // do NOT shutdown HomeServer if:
            if (exec('cat '.DIR.'/var/serverKeepOn') == 1) {
                $shutDownHomeServer = false;
		        $thought .= "Someone wants to keep HomeServer on. ";
                hbrain_log(__METHOD__.":".__LINE__, "serverKeepOn is set, HomeServer stays on.");
            }

            if ((bool)$newStates["KODI"]["active"]) {
                $shutDownHomeServer = false;
		        $thought .= "KODI is active. ";
                hbrain_log(__METHOD__.":".__LINE__, "KODI is active, HomeServer stays on.");
            }

            if ((bool)$newStates["HomeServer busy"]["active"]) {
                $shutDownHomeServer = false;
		        $thought .= "HomeServer is busy. ";
                hbrain_log(__METHOD__.":".__LINE__, "HomeServer is busy, HomeServer stays on.");
            }

            if ((bool)$newStates["HomeBrain user"]["active"]) {
                $shutDownHomeServer = false;
		        $thought .= "HomeBrain user is logged on. ";
                hbrain_log(__METHOD__.":".__LINE__, "HomeBrain user is logged on, HomeServer stays on.");
            }

            if ($shutDownHomeServer) {
                think("I'm shutting down HomeServer");
                HomeServer::shut();
            }

            else {
                think($thought ."HomeServer will stay on.");
            }

        }
/*
        // TV is off, KODI is on
        if ( !(bool)$newStates["TV"]["active"] && (bool)$newStates["KODI"]["active"] ) {
            KODI::off();

            // ..and it's silentTime
            if (HomeBrain::isSilentTime()) {
                MPD::off();
                Amp::off();
            }
        }
*/
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

        SQLITE::update("states", "active", $active, "name='HomeBrain user'");

        return ($active > 0) ? "true" : "false";
    }

    public static function mobAppConfig($token = null) {
        if ( $token === null ) $token = $_POST["param1"];
        //$cfgMessage["pages"]    = ["home", "mmkodi", "mmmpd", "grijanje", "lan", "vrt"];
        $cfgMessage["pages"]    = ["home", "mmkodi", "mmmpd"];
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

            case (bool)strpos($row["state"], "Sound"):
		$msg = ["is OK..", "is loud!"];
            	if ($row["changedto"] == 1) exec(DIR ."/homebrain hbrain alert 4 &");
            break;

            default:
            	// if ($row["state"] == "HomeServer" && $row["changedto"] == 1) exec(DIR ."/homebrain hbrain alert 3 &"); // alert if server is on
            	$msg = ["is off..", "is on!"];
        }
        hbrain_log(__METHOD__.":".__LINE__, $row["state"] ." ". $msg[$row["changedto"]]);
        //debug_log(__METHOD__.":".__LINE__, $row["state"] .'{"table":"changelog","values":'.json_encode($row).'}');

        $dbUpdates["table"] = "changelog";
        $dbUpdates["values"] = $row;
        MQTTclient::publish("hbrain/stat/". strtolower($row["state"]) ."/", $row["changedto"] == 1 ? "on" : "off", true);
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

        if ($timeNow >= $silentTimeStart || $timeNow < $silentTimeEnd) {
            hbrain_log(__METHOD__.":".__LINE__, "SilentTime is from ".$silentTimeStart." to ".$silentTimeEnd."h.");
            return true;
        }
        return false;
    }

    public static function notify($msg) {
        if ( HomeBrain::isSilentTime() ) return;
        Notifier::notify($msg);
    }

    public static function alert($secs) {
        if ( HomeBrain::isSilentTime() ) return;
        Notifier::alert($secs);
    }

    public static function speak($text) {
        if ( HomeBrain::isSilentTime() ) return;
        Notifier::speak($text);
    }

    ///// HomeBrain::uploadData() /////////////////////////////////////////////////////////////
    public static function uploadData() {
        uploadChangeLogData();
        uploadDataLogData();
    }

    public static function uploadChangeLogData() {

        $res = SQLITE::mySqlQuery("SELECT unix_timestamp(timestamp) AS utimestamp ".
                                    "FROM changelog ".
                                    "ORDER BY timestamp DESC LIMIT 1");

        $rows = SQLITE::query("SELECT * FROM changelog ".
                    "WHERE STRFTIME('%s', timestamp, 'localtime')-(60*60*0) >= ". trim($res[1])*1 ." ".
                    "ORDER BY timestamp ASC");

        hbrain_log(__METHOD__.":".__LINE__, "Uploading ". count($rows) ." rows to changelog, since ". date("d.m.Y H:i:s", $res[1]) . " - " . $res[1]);

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

            // //debug_log(__METHOD__.":".__LINE__, $sql);
            SQLITE::mySqlQuery($sql);
        }
    }

    public static function uploadDataLogData() {

        $res = SQLITE::mySqlQuery("SELECT unix_timestamp(timestamp) AS utimestamp ".
                                    "FROM datalog ".
                                    "ORDER BY timestamp DESC LIMIT 1");

        $sql = "SELECT * FROM datalog ".
                    "WHERE STRFTIME('%s', timestamp, 'localtime')-(60*60*0) >= ". trim($res[1])*1 ." ".
                    "ORDER BY timestamp ASC";
        $rows = SQLITE::query($sql);

        hbrain_log(__METHOD__.":".__LINE__, "Uploading ". count($rows) ." rows to datalog, since ". date("d.m.Y H:i:s", $res[1]) . " - " . $res[1]);

        foreach ($rows as $row)
        {
            $row['tempset'] = empty(trim($row['tempset'])) ? "NULL" : $row['tempset'];
            $row['tempin'] = empty(trim($row['tempin'])) ? "NULL" : $row['tempin'];
            $row['tempout'] = empty(trim($row['tempout'])) ? "NULL" : $row['tempout'];
            $row['heatingon'] = empty(trim($row['heatingon'])) ? "NULL" : $row['heatingon'];
            $row['humidin'] = empty(trim($row['humidin'])) ? "NULL" : $row['humidin'];
            $row['humidout'] = empty(trim($row['humidout'])) ? "NULL" : $row['humidout'];
            $row['light'] = empty(trim($row['light'])) ? "NULL" : $row['light'];
            $row['sound'] = empty(trim($row['sound'])) ? "NULL" : $row['sound'];
            $row['hindex'] = empty(trim($row['hindex'])) ? "NULL" : $row['hindex'];

            $sql = "INSERT INTO datalog ".
                    "VALUES('".$row['timestamp']."', ".
                    $row['tempset'].", ".
                    $row['tempin'].", ".
                    $row['tempout'].", ".
                    $row['heatingon'].", ".
                    $row['humidin'].", ".
                    $row['humidout'].", ".
                    $row['light'].", ".
                    $row['sound'].", ".
                    $row['hindex'].")";

            //debug_log(__METHOD__.":".__LINE__, $sql);
            SQLITE::mySqlQuery($sql);
        }
    }

    ///// HomeBrain::getTemps() ///////////////////////////////////////////////////////////////
    public static function getTemps() {
        $timestamp = date("Y-m-d H:i:00");

        $in = Weather::tempIn($timestamp);
        $inArr = explode(":", $in);

        $out = Weather::tempOut($timestamp);
        $outArr = explode(":", $out);

        $heatingOn = (int)Heating::isOn();
        $tempSet = Heating::getSetTemp();
        $hindex = Weather::heatIndex($inArr[0], $inArr[1]);

        $sndLight = explode(":", trim(exec("cat ". DIR ."/helpers/getsndnlight.sh")));

        //exec("sudo ". DIR ."/bin/nrf 8 'te:". $inArr[0] .":". $inArr[1] .":". $inArr[2] ."' &");

        //debug_log(__METHOD__.":".__LINE__, $inArr[4] ." ". $inArr[5]);

        SQLITE::insert("datalog",
                        ["timestamp",
                        "tempset",
                        "tempin",
                        "tempout",
                        "heatingon",
                        "humidin",
                        "humidout",
                        "light",
                        "sound",
                        "hindex",
                        "tempinliving",
                        "humidinliving",
                        "tempinbath",
                        "humidinbath"],
                        ["'". $timestamp ."'",
                        $tempSet,
                        $inArr[0],
                        $outArr[0],
                        $heatingOn,
                        $inArr[1],
                        $outArr[1],
                        $sndLight[0],
                        $sndLight[1],
                        $hindex,
                        $inArr[4],
                        $inArr[5],
                        $inArr[6],
                        $inArr[7]],
                        true);

        file_put_contents(DIR . "/var/lastTemp.dat",
                                    date("H:i:s") ."|".
                                    $inArr[0] ."|".     // tempIn
                                    $inArr[1] ."|".     // humidIn
                                    $outArr[0] ."|".    // tempOut
                                    $outArr[1] ."|".    // humidOut
                                    $heatingOn ."|".
                                    $tempSet ."|".
                                    $hindex ."|".
                                    $inArr[4] ."|".
                                    $inArr[5] ."|".
                                    $inArr[6] ."|".
                                    $inArr[7]
                                );

        Sound::isLoud();

        hbrain_log(__METHOD__.":".__LINE__, $in .":". $out .":". $hindex);
        return $in .":". $out .":". $hindex;
    }

    public static function temps($limit = 1) {
        $res = SQLITE::query("SELECT * FROM datalog ORDER BY timestamp DESC LIMIT ". $limit);
        return json_encode($res);
    }

    public static function alarm() {

        if ( date("N") > 5 ) return;

	    $alarmTime = strtotime(Configs::get("ALARM"));

        if (date("H:i", strtotime("+9 min")) == date("H:i", $alarmTime)) {
            MPD::off();
            sleep(20);

            Amp::on();
            Amp::volDown(30);
            sleep(10);
            Amp::volDown(30);
        }

        else if (date("H:i", strtotime("+3 min")) == date("H:i", $alarmTime)) {
            MPD::on();

            Amp::volUp(3);
            sleep(5);
            Amp::volUp(3);
            sleep(5);
            Amp::volUp(3);
            sleep(5);
            Amp::volUp(3);
            sleep(5);
            Amp::volUp(3);
        }

        else if (date("H:i", strtotime("+1 min")) == date("H:i", $alarmTime)) {
            Amp::volUp(5);
            sleep(15);
            Amp::volUp(5);
            sleep(15);
            Amp::volUp(5);
        }

        else if (date("H:i") == date("H:i", $alarmTime)) {
            Amp::volUp(5);
            Notifier::alert(7);
        }

        else if (date("H:i", strtotime("-1 min")) == date("H:i", $alarmTime)) {
            Amp::volUp(5);
            Notifier::alert(7);
        }

        else if (date("H:i", strtotime("-9 min")) == date("H:i", $alarmTime)) {
            Amp::volUp(5);
        }

        else if (date("H:i", strtotime("-50 min")) == date("H:i", $alarmTime)) {
		    MPD::off();
	}
    }

    public static function email ($to, $message) {
        exec('email '. $to .' "'. $message .'"');
    }

    public static function wifi($onoff = null) {
		if ($onoff === null) {
			return LAN::wifi();
		}
		else {
			return LAN::wifi($onoff);
		}
    }

    public static function debug($set = "") {
        switch($set) {

            case "1":
            case "on":
                Configs::set("DEBUG", "true");
            break;

            case "0":
            case "off":
                Configs::set("DEBUG", "false");
            break;

            default:
                return Configs::get("DEBUG");
        }
    }

    public static function clock() {
        $notifyText = "";

        if (date("H:i") == "04:58") Notifier::setClockTime();

        if (date("i") % 15 == 0) {

            if (date("d") == 31 && date("m") == 12 ) {
                $notifyText = "Sretna nova ". (date("Y")+1) ." godina!";

            } else if (date("d") == 1 && date("m") == 1 ) {
                $notifyText = "Sretna nova ". (date("Y")) ." godina!";
    
            } else if (date("d") == 13 && date("m") == 1) {
                $notifyText = "Marela sretan ti rođendan!";
                
            } else if (date("d") == 1 && date("m") == 10) {
                $notifyText = "Marijo sretan ti rođendan!";

            } else if (date("d") == 21 && date("m") == 11) {
                $notifyText = "Gabi sretan ti rođendan!";

            } else if (date("d") == 25 && date("m") == 12) {
                $notifyText = "Sretan Božić!";
            }
        }

        if ($notifyText != "") {
            Notifier::notifyClock($notifyText);
        }
    }
}

?>
