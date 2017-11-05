<?php

class SQLITE {

    private static $result;

    public static function insert() {
        // TODO
        return false;
    }

    public static function update($table, $attr, $value, $condition = null) {
        if ( $condition === null ) return false;

        return self::query("UPDATE `".$table."` SET `".$attr."`='".$value."' WHERE ".$condition);
    }

    public static function fetchone($table, $attr, $condition = null) {
        if ( $condition === null ) return false;
        
        self::query("SELECT `".$attr."` FROM `".$table."` WHERE ".$condition);

        return self::$result[0][0];
    }

    private static function query($sql) {
        $sqlite = new SQLite3(DIR.'/var/'.Configs::get("HOMEBRAIN_DB"));

        $res = $sqlite->query($sql);
        $ret = $sqlite->lastErrorMsg();
        while ( $row = $res->fetchArray() ) {
            self::$result[] = $row;
        }
        $sqlite->close();

        if ( $ret == "not an error" ) $ret = "OK";

        return $ret;
    }
}

?>