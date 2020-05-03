<?php

class Light {

    public static function h() {
        return HomeBrain::help(self::class);
    }

    public static function help() {
        return HomeBrain::help(self::class);
    }

    private static function getTopic($device) {
        switch($device) {
            case "tvlight":
                return "livingroom/cmnd/tvlight";
            break;
            
            case "lrlight1":
                return "livingroom/cmnd/lrlight1";
            break;
            
            case "lrlight2":
                return "livingroom/cmnd/lrlight2";
            break;
        }
    }

    public static function off($which) {
        hbrain_log(__METHOD__.":".__LINE__, Light::getTopic($which));
        MQTTclient::publish(Light::getTopic($which), "Off");
    }

    public static function on($which) {
        hbrain_log(__METHOD__.":".__LINE__, Light::getTopic($which));
        MQTTclient::publish(Light::getTopic($which), "On");
    }
}

?>