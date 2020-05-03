<?php

class MQTTclient {

    public static function h() {
        return HomeBrain::help(Weather::class);
    }

    public static function help() {
        return HomeBrain::help(Weather::class);
    }

    public static function publish($topic, $message, $retain = false) {
        if ($retain)
            $end = " -r";
        else $end = "";

        exec("/usr/bin/mosquitto_pub -h 10.10.10.12 -t $topic -m '". $message ."' -q 2". $end ." &");
        hbrain_log(__METHOD__.":".__LINE__, "mosquitto_pub -h 10.10.10.12 -t $topic -m '". $message ."' -q 2". $end ." &");
    }
}

?>
