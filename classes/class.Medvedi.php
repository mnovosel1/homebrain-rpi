<?php

class Medvedi {
    public static $debug = false;

    public static function h() {
        return MyAPI::help(self::class);
    }

    public static function help() {
        return MyAPI::help(self::class);
    }

    private static $tickerUrl = "http://liveticker.krone.at/eishockey/ebel/xml/laola1_eishockey_ebel.json";
    private static $logData = null, $newData = null, $gameDay, $gameLive;

    public static function check() {
        if (!self::getData()) return;

        if (self::$newData["medvedGolova"] > self::$logData["medvedGolova"]) {
            Notifier::fcmBcast("Medvedi", date("H:i")." "."GOOOL!!!!   (".self::$newData["score"].")");
        }

        if (self::isGameDay()) {
            if (self::isGameLive()) {
                echo self::$newData["playing"] . " is live!!";
                if ( json_encode(self::$logData) != json_encode(self::$newData) ) {
                    self::notify();
                }
            } else if (strtotime(self::$logData["time"])-time() > 0) {
                echo self::timeToGame() . "!\n";
                if (time() - filemtime(DIR . "/var/medvedi.log") >= 60*60*3) Notifier::fcmBcast("Medvedi", date("H:i")." ".self::timeToGame());
            }
        }
    }

    public static function show() {
        self::getData();
        var_dump(self::$logData);
        var_dump(self::$newData);
        
        var_dump((self::isGameLive() || time() - filemtime(DIR . "/var/medvedi.log") >= 60*60*3));
    }

    public static function notify() {
        if (!self::getData()) return;

        $msg = "";
        //$msg .= self::$newData["time"] . " ";
        $msg .= self::$newData["playing"] . " ";
        $msg .= str_replace(" (", " \n".date("H:i")." ".self::$newData["period"]." (", self::$newData["score"]) . " ";
        echo $msg;

        Notifier::fcmBcast("Medvedi", $msg);
    }


// private methods //////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    private static function getData() {
        self::getLogData();
        self::getTickerData();

        if (self::$newData == null) return false;
        return true;
    }

    private static function getLogData() {
        self::$logData = json_decode(file_get_contents(DIR . "/var/medvedi.log"), true);
    }

    private static function getTickerData() {
        
        if (self::isGameLive() || time() - filemtime(DIR . "/var/medvedi.log") >= 60*60*3) {
            $command = "curl ". self::$tickerUrl ."  2>/dev/null";
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
            
                    self::$newData = array(     "status"        => $game["event_status"],
                                                "date"          => date("d.m.Y.", strtotime($game["spielbeginn"])), 
                                                "time"          => date("H:i", strtotime($game["spielbeginn"])), 
                                                "playing"       => trim($game["team_heim_name"]). " - " .trim($game["team_gast_name"]),
                                                "score"         => $game["ergebnis"],
                                                "period"        => $game["mzeit"],
                                                "medvedGolova"  => $medvedGoals
                    );
                }            
            }
            if (self::$newData != null) file_put_contents(DIR . "/var/medvedi.log", json_encode(self::$newData));
        } 
        
        else {
            self::$newData = self::$logData;
        }
    }

    public static function timeToGame() {
		
		$gameTime = strtotime(self::$newData["time"]);

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
		return self::$newData["playing"] . " počinje za " . $howlong;
    }
    
    private static function isGameDay() {
        if (date("d.m.Y.") == self::$logData["date"]) return true;
        return false;        
    }

    private static function isGameLive() {
        if (self::$logData["status"] != "post-event" && ceil((strtotime(self::$logData["time"])-time())/60) < 2) return true;
        return false;        
    }

}

?>