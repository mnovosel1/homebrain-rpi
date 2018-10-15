<?php

class Medvedi {

    // private static $tickerUrl = "http://liveticker.krone.at/eishockey/ebel/xml/laola1_eishockey_ebel.json";
    private static $tickerUrl = "http://liveticker.krone.at/eishockey/ebel/xml/spielplan.json";
    private static $logData = null, $newData = null, $gameDay, $gameLive;

    public static function h() {
        return MyAPI::help(self::class);
    }

    public static function help() {
        return MyAPI::help(self::class);
    }

    public static function check() {
        debug_log(__METHOD__, "Checking..");
        Medvedi::getData();

        if (Medvedi::$newData["medvedGolova"] > 0 && Medvedi::$newData["medvedGolova"] > Medvedi::$logData["medvedGolova"]) {
            hbrain_log(__METHOD__, "Medvedi goool!");
            Notifier::alert(5);
            Notifier::fcmBcast("MedvediGoal", date("H:i")." "."GOOOL!!!!   (".Medvedi::$newData["score"].")");
        }

        else if (!HomeBrain::isSilentTime()) {
            if (Medvedi::isGameDay()) {
                if (Medvedi::isGameLive()) {
                    if ( Medvedi::$newData != Medvedi::$logData ) {
                        $msg = "";
                        $msg .= Medvedi::$newData["playing"] ." ". Medvedi::$newData["score"];

                        switch (true) {

                            case (Medvedi::$newData["status"] == "pre-event" && Medvedi::$logData["status"] == "mid-event"):
                                $msg .= " Game started.";
                            break;

                            case (Medvedi::$newData["period"] != Medvedi::$logData["period"]):
                                $msg .= " ". Medvedi::$newData["period"] ." started.";
                            break;

                            case (Medvedi::$newData["status"] == "post-event" && Medvedi::$logData["status"] == "mid-event"):
                                if ( Medvedi::$newData["medvedGolova"] > Medvedi::$newData["antiMedvedGolova"]) {
                                    hbrain_log(__METHOD__, "Medved pobjeda ". Medvedi::$newData["score"] ." !");
                                    Notifier::alert(15);
                                    $msg .= " POBJEDAAA!!!!";
                                }
                                else {
                                    $msg .= " Game ended.";
                                }
                            break;

			    default:
				    $msg .= " ". Medvedi::$newData["period"];

                        }

                        hbrain_log(__METHOD__, $msg);

                        Notifier::fcmBcast("Medvedi", $msg);
                    }
                } else if (strtotime(Medvedi::$newData["time"])-time() > 0) {
                    hbrain_log(__METHOD__, Medvedi::timeToGame() . "!");
                    if ( time() - filemtime(DIR . "/var/medvedi.log") >= 60*60 ) {
                        Notifier::fcmBcast("Medvedi", date("H:i") .": ". Medvedi::timeToGame());
                    }
                }
            }
        }
    }

    public static function show() {
        Medvedi::getData();
        var_dump(Medvedi::$logData);
        var_dump(Medvedi::$newData);

        var_dump((Medvedi::isGameLive() || time() - filemtime(DIR . "/var/medvedi.log") >= 60*60));
    }

// private methods //////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    private static function getData() {
        Medvedi::getLogData();
        Medvedi::getTickerData();
    }

    private static function getLogData() {
        Medvedi::$logData = json_decode(file_get_contents(DIR . "/var/medvedi.log"), true);
    }

    private static function getTickerData() {

        if (Medvedi::isGameLive() || time() - filemtime(DIR . "/var/medvedi.log") >= 60*60) {
            $command = "curl ". Medvedi::$tickerUrl ."?". time() ."  2>/dev/null";
            exec($command, $output);

            $games = json_decode($output[0], true);
            $games = $games["c"]["r"];
            foreach ($games as $week) {
                if ($week["aktiv"] == "true") break;
            }
            $games = $week["s"];

            foreach ($games as $game) {
                //debug_log(__METHOD__, $game["datum"] .": ".  $game["th_name"] ." vs. ". $game["tg_name"]);
                //if (strpos($game["team_heim_kuerzel"], "MZA") !== false || strpos($game["team_gast_kuerzel"], "MZA") !== false ) {
                if (strpos($game["th_name"], "Medvescak") !== false || strpos($game["tg_name"], "Medvescak") !== false ) {

                    if (strpos($game["th_name"], "Medvescak") !== false) {
                        $game["th_name"] = "KHL Medveščak";
                        $medvedGoals = $game["tore_heim"];
                        $antiMedvedGoals = $game["tore_gast"];
                    } else if (strpos($game["tg_name"], "Medvescak") !== false) {
                        $game["tg_name"] = "KHL Medveščak";
                        $medvedGoals = $game["tore_gast"];
                        $antiMedvedGoals = $game["tore_heim"];
                    }
                    //$game["datum"] = substr(str_replace(",", "", $game["datum"]), 4, strlen($game["datum"]));
                    $game["datum"] = str_replace(",", "", $game["datum"]);
                    if ($game["tore_heim_pe"] != "" || $game["tore_gast_pe"] != "") {
                        $trecina = "SO";
                    }
                    else if ($game["tore_heim_ot"] != "" || $game["tore_gast_ot"] != "") {
                        $trecina = "OT";
                    }
                    else if ($game["tore_heim_3"] != "" || $game["tore_gast_3"] != "") {
                        $trecina = "3/3";
                    }
                    else if ($game["tore_heim_2"] != "" || $game["tore_gast_2"] != "") {
                        $trecina = "2/3";
                    }
                    else if ($game["tore_heim_1"] != "" || $game["tore_gast_1"] != "") {
                        $trecina = "1/3";
                    }

                    Medvedi::$newData = array(  "status"             => $game["event_status"],
                                                "date"               => date("d.m.Y.", strtotime(explode(" ", $game["datum"])[1].date("Y"))), 
                                                "time"               => date("H:i", strtotime(explode(" ", $game["datum"])[2])), 
                                                "playing"            => trim($game["th_name"]). " - " .trim($game["tg_name"]),
                                                "period"             => $trecina,
                                                "score"              => $game["tore_heim"] ." : ". $game["tore_gast"],
                                                "medvedGolova"       => $medvedGoals,
                                                "antiMedvedGolova"   => $antiMedvedGoals
                    );
                    if (Medvedi::$newData["status"] != "post-event") {
                        if (Medvedi::$newData["status"] == "pre-event") {
                            Medvedi::$newData["period"] = "";
                            Medvedi::$newData["score"] = "";
                            Medvedi::$newData["medvedGolova"] = "";
                            Medvedi::$newData["antiMedvedGolova"] = "";
                        }
                        break 1;
                    }
                }
            }
            if (Medvedi::$newData != null) {
                file_put_contents(DIR . "/var/medvedi.log", json_encode(Medvedi::$newData));
            }
        }

        else {
            Medvedi::$newData = Medvedi::$logData;
        }
    }

    public static function timeToGame() {
        Medvedi::getData();

		$gameTime = strtotime(Medvedi::$newData["date"] ." ". Medvedi::$newData["time"]);

		$howlong = '';
		$seconds = $gameTime - time(); 
		$minutes = (int)($seconds / 60);
		$hours = (int)($minutes / 60);
		$days = (int)($hours / 24);
		if (abs($days) >= 1) {
		  $howlong = $days . ' ' . ($days != 1 ? 'dana' : 'dan');
		} else if (abs($hours) >= 1) {
		  $howlong = $hours . ' sat' . ($hours != 1 ? 'i' : '');
		} else if (abs($minutes) >= 1) {
		  $howlong = $minutes . ' minuta';
		} else {
		  $howlong = $seconds . ' ' . ($seconds != 1 ? 'sekundi' : 'sekunda');
		}
		return Medvedi::$newData["playing"] . " počinje za " . $howlong;
    }

    private static function isGameDay() {
        if (date("d.m.Y.") == Medvedi::$logData["date"]) {
            hbrain_log(__METHOD__, "It's GameDay!!");
            return true;
        }
        return false;
    }

    private static function isGameLive() {
        if (!Medvedi::isGameDay()) return false;

        if (Medvedi::$logData["status"] != "post-event" && ceil((strtotime(Medvedi::$logData["time"])-time())/60) < 5) {            
            hbrain_log(__METHOD__, "Game is live!!");
            return true;
        }
        return false;
    }

}

?>
