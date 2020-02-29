<?php

class MPD {

    public static function h() {
        return MyAPI::help(self::class);
    }

    public static function help() {
        return MyAPI::help(self::class);
    }

    public static function on() {

        Amp::on();
		exec("echo mpd > /srv/HomeBrain/remote/mode &");
        MQTTclient::publish("hbrain/stat/mpd/", "on", true);

        if (MPD::playing() == "false") {

            $command = "";
            $command .= "/usr/bin/mpc clear";
            $command .= " && ";
            $command .= "/usr/bin/mpc repeat on";
            $command .= " && ";
            $command .= "/usr/bin/mpc random on";
            $command .= " && ";
            $command .= "/usr/bin/mpc single off";
            $command .= " && ";
            $command .= "/usr/bin/mpc consume off";
            $command .= " && ";
            $command .= "/usr/bin/mpc load radio";
            $command .= " && ";
            $command .= "/usr/bin/mpc play 1";

            LAN::SSH("KODI", $command);
        }
        Amp::mpd();
    }

    public static function off() {
        MQTTclient::publish("hbrain/stat/mpd/", "off", true);

        LAN::SSH("KODI", "/usr/bin/mpc clear &");
        //Amp::off();        

        HomeBrain::wakecheck();
    }

    public static function play() {
		if ( $_POST["param1"] != "" ) {
            MPD::stop();
            LAN::SSH("KODI", "/usr/bin/mpc play " + $_POST["param1"]);
        }
        return MPD::playing();
    }

    public static function next() {
        LAN::SSH("KODI", "/usr/bin/mpc next");
    }

    public static function prev() {
        LAN::SSH("KODI", "/usr/bin/mpc prev");
    }

    public static function stop() {
        LAN::SSH("KODI", "/usr/bin/mpc stop");
    }

    public static function playing() {

        $mpdplay = LAN::SSH("KODI", "/usr/bin/mpc current");

        if ($mpdplay == "") {
            SQLITE::update("states", "active", 0, "name='MPD playing'");
            MQTTclient::publish("hbrain/stat/mpd/", "off", true);
            return "false";
        } else {
            SQLITE::update("states", "active", 1, "name='MPD playing'");
            MQTTclient::publish("hbrain/stat/mpd/", "on", true);
            return $mpdplay;
        }
    }
}

?>
