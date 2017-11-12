<?php

class SQLITE {

    private static $result;

    public static function insert() {
        // TODO
        return false;
    }

    public static function fetch() {
        // $table
        if ( func_num_args() == 1 ) {
            $sql = "SELECT * FROM ".func_get_arg(0);
        }

        // $table, $condition
        else if ( func_num_args() == 2 ) {
            $sql = "SELECT * FROM ".func_get_arg(0)." WHERE ".func_get_arg(1);
        }

        // $table, [$attributes], $condition
        else if ( func_num_args() == 3 ) {
            $attributes = "";
            foreach ( func_get_arg(1) as $attribute) $attributes .= $attribute.", ";
            $sql = "SELECT ".substr($attributes, 0, strlen($attributes)-2)." FROM ".func_get_arg(0)." WHERE ".func_get_arg(2);
        }

        if ( self::query($sql) != "OK" ) return false;
        return self::$result;
    }

    public static function update($table, $attribute, $value, $condition = null) {
        if ( $condition === null ) return false;

        $update = true;

        if ( $table == "states" ) {
            self::query("SELECT `".$attribute."` FROM `states` WHERE ".$condition);
            if ( $value == self::$result[0][$attribute] ) {
                $update = false;
            }
        }

        if ( $update ) { 
            $ret = self::query("UPDATE `".$table."` SET `".$attribute."`='".$value."' WHERE ".$condition);
            self::query("SELECT * FROM changelog ORDER BY timestamp DESC LIMIT 1");
            Notifier::dbUpdate(self::$result[0]);
            return $ret;
        }
        
        return null;
    }

    protected static function query($sql) {
        $sqlite = new SQLite3(DIR.'/var/'.Configs::get("HOMEBRAIN_DB"));

        //debug_log($sql);

        $res = $sqlite->query($sql);
        $ret = $sqlite->lastErrorMsg();
        $tmp = array();
        while ( $row = $res->fetchArray(SQLITE3_ASSOC) ) {
            $tmp[] = $row;
        }
        $sqlite->close();
        self::$result = $tmp;

        if ( $ret == "not an error" ) $ret = "OK";

        return $ret;
    }
}

?>