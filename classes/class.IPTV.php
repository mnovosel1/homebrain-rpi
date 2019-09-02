<?php

define("KEY_POWER",                "807F38C7");
define("KEY_SETUP",                "807FAA55");
define("KEY_INFO",                 "807F7A85");
define("KEY_SEARCH",               "807F9A65");
define("KEY_MENU",                 "807F18E7");
define("KEY_RED",                  "807F6A95");
define("KEY_GREEN",                "807FEA15");
define("KEY_YELLOW",               "807FF807");
define("KEY_BLUE",                 "807FDA25");
define("KEY_BACK",                 "807F807F");
define("KEY_PLAY",                 "807FFA05");
define("KEY_STOP",                 "807F9867");
define("KEY_FORWARD",              "807F1AE5");
define("KEY_HOME",                 "807F10EF");
define("KEY_B",                    "807FE817");
define("KEY_I",                    "807F926D");
define("KEY_EPG",                  "807F50AF");
define("KEY_OK",                   "807F609F");
define("KEY_LEFT",                 "807FE21D");
define("KEY_RIGHT",                "807FE01F");
define("KEY_UP",                   "807F58A7");
define("KEY_DOWN",                 "807F12ED");
define("KEY_VOLUMEUP",             "807FD22D");
define("KEY_VOLUMEDOWN",           "807FF20D");
define("KEY_MUTE",                 "807F42BD");
define("KEY_CHANNELUP",            "807F906F");
define("KEY_CHANNELDOWN",          "807FA05F");
define("KEY_1",                    "807F2AD5");
define("KEY_2",                    "807F6897");
define("KEY_3",                    "807FA857");
define("KEY_4",                    "807F0AF5");
define("KEY_5",                    "807F48B7");
define("KEY_6",                    "807F8877");
define("KEY_7",                    "807F32CD");
define("KEY_8",                    "807F708F");
define("KEY_9",                    "807FB04F");
define("KEY_0",                    "807F30CF");
define("KEY_REFRESH",              "807F827D");
define("KEY_DELETE",               "807F08F7");

class IPTV {
    public static function h() {
        return MyAPI::help(self::class);
    }

    public static function help() {
        return MyAPI::help(self::class);
    }

    public static function isOn() {
        return exec("ssh kodi 'cat /home/hbrain/remote/mode'") == "iptv" ? true : false;
    }

    public static function on() {
        self::sendKey("KEY_POWER");
        TV::on();
		if (trim(exec("ssh kodi 'cat /home/hbrain/remote/mode'")) == "kodi") KODI::off();
		exec("ssh kodi 'echo iptv > /home/hbrain/remote/mode' &");
        TV::iptv();
        Amp::on();
        Amp::tv();
        return true;
    }

    public static function off() {
        Amp::off();
    }

    public static function sendKey($key) {
        hbrain_log(__METHOD__.":".__LINE__, "sudo ". DIR ."/bin/nrf 1 irnec:". constant($key) ." &");
        exec("sudo ". DIR ."/bin/nrf 1 irnec:". constant($key) ." &");
    }
}

?>
