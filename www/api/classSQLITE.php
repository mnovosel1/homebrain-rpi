<?php

class SQLITE {

    public static function insert() {
        // TODO
        return false;
    }

    public static function update ($table, $attr, $value, $condition = 0) {

        if ( strpos($condition, "=") === false ) return false;

        $sqlite = new SQLite3(DIR .'/var/hbrain.db');        
        $sqlite->query("UPDATE ".$table." SET ".$attr."='".$value."' WHERE ".$condition."");
        $return = $sqlite->lastErrorMsg();
        $sqlite->close();

        if ( $return == "not an error" ) return true;
        else return $return;
    }

}

?>