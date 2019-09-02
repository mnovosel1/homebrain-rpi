<?php

class Reg {

    public static function h() {
        return MyAPI::help(self::class);
    }

    public static function help() {
        return MyAPI::help(self::class);
    }

    public static function register() {

        if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) return false;

        $ret = SQLITE::insert("fcm", ["timestamp", "email", "token"], ["datetime('now', 'localtime')", "'".$_POST["email"]."'", "'".$_POST["token"]."'"], true);

        $code = substr($_POST["token"], strlen($_POST["email"])*(-1));
        exec (DIR . "/notify/email.php " . "'HomeBrain: verify email..' " . "'SupeSecretCODE: " . $code . "' " . $_POST["email"]);
        exec ("cp ". DIR ."/var/hbrain.db ". DIR ."/saved_var/hbrain.db");

        if ( $ret !== null) $ret = false;
        return null;
    }

    public static function verify() {
        $tokens = SQLITE::query("SELECT token FROM fcm WHERE approved = 'false' AND email = '".$_POST["email"]."'");
        $verified = false;

        foreach ( $tokens as $token ) {
            if ( $_POST["code"] == substr($token["token"], strlen($_POST["email"])*(-1)) ) $verified = $token["token"];
        }

        if ( $verified !== false ) {
            HomeBrain::mobAppConfig($verified);
            SQLITE::approve($verified);
            return true;
        }

        return false;
    }
}

?>
