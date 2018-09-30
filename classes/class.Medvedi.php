<?php

class Medvedi {
    public static $debug = true;

    private static $tickerUrl = "http://liveticker.krone.at/eishockey/ebel/xml/laola1_eishockey_ebel.json";
    private static $logData = null, $newData = null, $gameDay, $gameLive;

    public static function h() {
        return MyAPI::help(Medvedi::class);
    }

    public static function help() {
        return MyAPI::help(Medvedi::class);
    }

    public static function check() {
        if (!Medvedi::getData()) return;

        if (Medvedi::$newData["medvedGolova"] > Medvedi::$logData["medvedGolova"]) {
            hbrain_log(__FILE__, "Medvedi goool!");
            Norifier::alert(5);
            Notifier::fcmBcast("Medvedi", date("H:i")." "."GOOOL!!!!   (".Medvedi::$newData["score"].")");
        }

        if (Medvedi::isGameDay()) {
            if (Medvedi::isGameLive()) {
                echo Medvedi::$newData["playing"] . " is live!!";
                if ( json_encode(Medvedi::$logData) != json_encode(Medvedi::$newData) ) {
                    Medvedi::notify();
                }
            } else if (strtotime(Medvedi::$logData["time"])-time() > 0) {
                hbrain_log(__FILE__, Medvedi::timeToGame() . "!");
                if (time() - filemtime(DIR . "/var/medvedi.log") >= 60*60) 
                    Notifier::fcmBcast("Medvedi", date("H:i")." ".Medvedi::timeToGame());
            }
        }
    }

    public static function show() {
        Medvedi::getData();
        var_dump(Medvedi::$logData);
        var_dump(Medvedi::$newData);
        
        var_dump((Medvedi::isGameLive() || time() - filemtime(DIR . "/var/medvedi.log") >= 60*60));
    }

    public static function notify() {
        if (!Medvedi::getData()) return;

        $msg = "";
        //$msg .= Medvedi::$newData["time"] . " ";
        $msg .= Medvedi::$newData["playing"] . " ";
        $msg .= str_replace(" (", " \n".date("H:i")." ".Medvedi::$newData["period"]." (", Medvedi::$newData["score"]) . " ";
        echo $msg;

        Notifier::fcmBcast("Medvedi", $msg);
    }


// private methods //////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    private static function getData() {
        Medvedi::getLogData();
        Medvedi::getTickerData();

        if (Medvedi::$newData == null) return false;
        return true;
    }

    private static function getLogData() {
        Medvedi::$logData = json_decode(file_get_contents(DIR . "/var/medvedi.log"), true);
    }

    private static function getTickerData() {
        
        if (Medvedi::isGameLive() || time() - filemtime(DIR . "/var/medvedi.log") >= 60*60) {
            $command = "curl ". Medvedi::$tickerUrl ."?". time() ."  2>/dev/null";
            exec($command, $output);

            $games = json_decode($output[0], true);
            $games = $games["c"]["ticker"]["game"];

            foreach ($games as $game) {
                if (strpos($game["team_heim_kuerzel"], "MZA") !== false || strpos($game["team_gast_kuerzel"], "MZA") !== false ) {
            
                    if (strpos($game["team_heim_name"], "Medvescak") !== false) {
                        $game["team_heim_name"] = "KHL Medveščak";
                        $medvedGoals = $game["tore_heim"];
                    } else if (strpos($game["team_gast_name"], "Medvescak") !== false) {
                        $game["team_gast_name"] = "KHL Medveščak";
                        $medvedGoals = $game["tore_gast"];
                    }
                    $game["mzeit"] = str_replace("Dr.", "trećina", $game["mzeit"]);
                    $game["mzeit"] = str_replace("Pause", "pauza", $game["mzeit"]);
                    $game["mzeit"] = str_replace("Beendet", "", $game["mzeit"]);
                    $game["mzeit"] = trim($game["mzeit"]);
            
                    Medvedi::$newData = array(  "status"        => $game["event_status"],
                                                "date"          => date("d.m.Y.", strtotime($game["spielbeginn"])), 
                                                "time"          => date("H:i", strtotime($game["spielbeginn"])), 
                                                "playing"       => trim($game["team_heim_name"]). " - " .trim($game["team_gast_name"]),
                                                "score"         => $game["ergebnis"],
                                                "period"        => $game["mzeit"],
                                                "medvedGolova"  => $medvedGoals
                    );
                }            
            }
            if (Medvedi::$newData != null) file_put_contents(DIR . "/var/medvedi.log", json_encode(Medvedi::$newData));
        } 
        
        else {
            Medvedi::$newData = Medvedi::$logData;
        }
    }

    public static function timeToGame() {
		
		$gameTime = strtotime(Medvedi::$newData["time"]);

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
            hbrain_log(__FILE__, "It's GameDay!!");
            return true;
        }
        return false;
    }

    private static function isGameLive() {
        if (Medvedi::$logData["status"] != "post-event" && ceil((strtotime(Medvedi::$logData["time"])-time())/60) < 5) {            
            hbrain_log(__FILE__, "Game is live!!");
            return true;
        }
        return false;        
    }

}

?>