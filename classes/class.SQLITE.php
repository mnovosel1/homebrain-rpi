<?php

class SQLITE {
    public static $debug = false;

    private static $result;

    public static function getResult() {
	return SQLITE::$result;
    }

    public static function insert($table, $attributes, $values, $insertOrReplace = false) {
        if ( count($attributes) != count($values) ) {
            hbrain_log(__METHOD__.":".__LINE__, "Not enough SQL attribute - values!");
            return false;
        }

        $attributes = implode(", ", $attributes);
        $values     = implode(", ", $values);

        $sql = "INSERT";
        if ( $insertOrReplace ) $sql .= " OR REPLACE";
        $sql .= " INTO ".$table." (".$attributes.") VALUES (".$values.")";

        debug_log(__METHOD__.":".__LINE__, $sql);

        return SQLITE::query($sql, true);
    }

    public static function update($table, $attribute, $value, $condition = null) {
        if ( $condition === null ) return false;

	    debug_log(__METHOD__.":".__LINE__, $attribute ." ". $condition);

        if ( $table == "states" ) {
            SQLITE::query("SELECT `".$attribute."` FROM `states` WHERE ".$condition);
            $oldValue = SQLITE::$result[0][$attribute];
        }


        if ( ($value*1) != ($oldValue*1) ) {
            hbrain_log(__METHOD__.":".__LINE__, $condition ." changed to ". $value);
            SQLITE::query("UPDATE `".$table."` SET `".$attribute."`='".$value."' WHERE ".$condition);
            $res = SQLITE::query("SELECT timestamp, statebefore, state, changedto FROM changelog ORDER BY timestamp DESC LIMIT 1");

	    think("I turned ". ucfirst($res[0]["state"]) . (($res[0]["changedto"] == 0) ? " off" : " on") .".");
            HomeBrain::mobDbUpdate($res[0]);
        }
    }

    public static function approve($token) {
        SQLITE::query("UPDATE fcm SET approved = 'true' WHERE token = '".$token."'");
    }

    public static function query($sql, $insert = false) {

        $sqlite = new SQLite3(DIR.'/var/'.Configs::get("HOMEBRAIN", "DB"));
        $tmp = "";

        debug_log(__METHOD__.":".__LINE__, $sqlite->lastErrorMsg ());
        debug_log(__METHOD__.":".__LINE__, $sql);

        $res = $sqlite->query($sql);
        $ret = $sqlite->lastErrorMsg();

	if (strtoupper(substr($sql, 0 , 6)) == "INSERT") {
		 debug_log(__METHOD__.":".__LINE__, "lastInsertRowID(): ". $sqlite->lastInsertRowID());
                 // debug_log(__METHOD__.":".__LINE__, "/usr/bin/ssh bubulescu.org '/home/bubul/mydb \"". str_replace("OR REPLACE ", "", $sql) ."\"'");
                 // exec("/usr/bin/ssh bubulescu.org '/home/bubul/mydb \"". $sql ."\"'");
        }

        if ( $res !== false && !$insert ) {
            $tmp = array();
            while ( $row = $res->fetchArray(SQLITE3_ASSOC) ) {
                $tmp[] = $row;
            }
        }

        $sqlite->close();
        SQLITE::$result = $tmp;

        if ( $ret == "not an error" ) {
            $ret = null;
        }
        else hbrain_log(__METHOD__.":".__LINE__, $ret ." SQL: ". $sql);

        return SQLITE::$result;
    }

    public static function mySqlQuery($sql) {
	$sql = preg_replace('# {2,}#', ' ', (str_replace(array("\r\n","\r","\n"),' ',trim($sql))));
	exec("/usr/bin/ssh bubulescu.org \"/home/bubul/mydb ".'\\'."\"". $sql ."".'\\'."\"\"", $ret);
	debug_log(__METHOD__.":".__LINE__, "/usr/bin/ssh bubulescu.org \"/home/bubul/mydb ".'\\'."\"". $sql ."".'\\'."\"\"");
        return $ret;
    }

    public static function dbdump() {

        // HBRAIN //////////////////////////////////////////////////////////////////////////////////
        $sql = "
    BEGIN TRANSACTION;

    /***********************************************************************************/

        CREATE TABLE states (
            name varchar(50) PRIMARY KEY,
            auto int(1) NOT NULL DEFAULT 1,
            active int(1) NOT NULL DEFAULT 0
        );
        ";

        $output = "";
        exec('sqlite3 '. DIR .'/var/hbrain.db \'.dump "states"\' | grep \'^INSERT\'', $output);
        foreach ( $output as $line )
          $sql .= "\n        ".trim($line);

        // TABLE changelog
        $sql .= "

    /***********************************************************************************/

        CREATE TABLE lan (
            timestamp DATETIME DEFAULT (DATETIME('now', 'localtime')),
            mac varchar(20) PRIMARY KEY,
            ip varchar(20) DEFAULT NULL,
            name varchar(99) DEFAULT NULL,
            known int(1) NOT NULL DEFAULT 0
        );
        ";

        $output = "";
        exec('sqlite3 '. DIR .'/var/hbrain.db \'.dump "lan"\' | grep \'^INSERT\'', $output);
        foreach ( $output as $line )
            $sql .= "\n        ".trim($line);

        // TABLE changelog
        $sql .= "

    /***********************************************************************************/

        CREATE TABLE changelog (
            timestamp DATETIME,
            statebefore varchar(30) NOT NULL,
            light double DEFAULT NULL,
            tempin double DEFAULT NULL,
            tempout double DEFAULT NULL,
            sound double DEFAULT NULL,
            state varchar(50) NOT NULL,
            changedto int(1) NOT NULL,
            FOREIGN KEY (state) REFERENCES states(name)
        );
        ";

        $sql .= "
        CREATE TRIGGER changelog_trigg
            BEFORE UPDATE ON states
            FOR EACH ROW
            WHEN OLD.active <> NEW.active
            BEGIN

                INSERT INTO changelog (timestamp, statebefore, tempin, tempout, light, sound, state, changedto)
                VALUES (
                            (STRFTIME('%Y-%m-%d %H:%M:00', DATETIME('now', 'localtime'))),
                            (SELECT group_concat(active, '') FROM states),
                            (SELECT tempin FROM datalog WHERE timestamp <= (STRFTIME('%Y-%m-%d %H:%M:00', DATETIME('now', 'localtime'))) ORDER BY timestamp DESC LIMIT 1),
                            (SELECT tempout FROM datalog WHERE timestamp <= (STRFTIME('%Y-%m-%d %H:%M:00', DATETIME('now', 'localtime'))) ORDER BY timestamp DESC LIMIT 1),
                            (SELECT light FROM datalog WHERE timestamp <= (STRFTIME('%Y-%m-%d %H:%M:00', DATETIME('now', 'localtime'))) ORDER BY timestamp DESC LIMIT 1),
                            (SELECT sound FROM datalog WHERE timestamp <= (STRFTIME('%Y-%m-%d %H:%M:00', DATETIME('now', 'localtime'))) ORDER BY timestamp DESC LIMIT 1),
                            NEW.name,
                            NEW.active
                        );

                DELETE FROM datalog WHERE timestamp <= DATE('now', '-90 day');
                DELETE FROM changelog WHERE timestamp <= DATE('now', '-90 day');

            END;
        ";

        $output = '';
        exec('sqlite3 '. DIR .'/var/hbrain.db \'.dump "changelog"\' | grep \'^INSERT\'', $output);
        foreach ( $output as $line )
            $sql .= "\n        ".trim($line);

        // VIEW logic
        $sql .= "

    /***********************************************************************************/

        CREATE VIEW logic AS
            SELECT
                COUNT(*) AS weight,
                STRFTIME('%w', timestamp)*1 AS wday,
                STRFTIME('%H', timestamp)*1 AS hour,
                c.statebefore,
                s.name,
                c.changedto
            FROM changelog c join states s ON c.state=s.name
            WHERE s.auto=1
            GROUP BY c.statebefore, hour, wday, s.name, c.changedto
            ORDER BY weight DESC;
        ";

        // VIEW tmpavg
        $sql .= "

    /***********************************************************************************/

        CREATE VIEW tmpavg AS
		SELECT
			STRFTIME('%d-%m-%Y', timestamp) AS date,
			ROUND(AVG(tempin), 1) AS avgtemp,
			ROUND(AVG(humidin), 1) AS avghumid,
			ROUND(AVG(tempin)-AVG(tempout), 1) AS tmpdiff,
                        ROUND(AVG(hindex), 1) AS avghindex
		FROM datalog
		GROUP BY STRFTIME('%d-%m-%Y', timestamp)
		ORDER BY timestamp DESC;
        ";


        // TABLE datalog
        $sql .= "
    /***********************************************************************************/

        CREATE TABLE datalog (
            timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            tempset double DEFAULT NULL,
            tempin double DEFAULT NULL,
            tempout double DEFAULT NULL,
            heatingon int(11) DEFAULT NULL,
            humidin double DEFAULT NULL,
            humidout double DEFAULT NULL,
            light double DEFAULT NULL,
            sound double DEFAULT NULL,
            hindex double DEFAULT NULL,
            PRIMARY KEY (timestamp)
        );
        ";

        $output = '';
        exec('sqlite3 '. DIR .'/var/hbrain.db \'.dump "dataLog"\' | grep \'^INSERT\'', $output);
        foreach ( $output as $line )
            $sql .= "\n        ".trim($line);



        // TABLE finlog
        $sql .= "
    /***********************************************************************************/

        CREATE TABLE finlog (
            timestamp timestamp PRIMARY KEY DEFAULT CURRENT_TIMESTAMP,
            name TEXT NOT NULL,
            category TEXT DEFAULT NULL,
            amount double NOT NULL
        );
        ";

        $output = '';
        exec('sqlite3 '. DIR .'/var/hbrain.db \'.dump "finlog"\' | grep \'^INSERT\'', $output);
        foreach ( $output as $line )
            $sql .= "\n        ".trim($line);


        // TABLE findata
        $sql .= "
    /***********************************************************************************/

        CREATE TABLE findata (
            timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            ernt_value double DEFAULT NULL,
            ernt_change double DEFAULT NULL,
            ernt_change_precent double DEFAULT NULL,
            zdmfent_value double DEFAULT NULL,
            eur_k double DEFAULT NULL,
            eur_s double DEFAULT NULL,
            eur_p double DEFAULT NULL,
            usd_k double DEFAULT NULL,
            usd_s double DEFAULT NULL,
            usd_p double DEFAULT NULL,
            chf_k double DEFAULT NULL,
            chf_s double DEFAULT NULL,
            chf_p double DEFAULT NULL,
            gbp_k double DEFAULT NULL,
            gbp_s double DEFAULT NULL,
            gbp_p double DEFAULT NULL,
            jpy_k double DEFAULT NULL,
            jpy_s double DEFAULT NULL,
            jpy_p double DEFAULT NULL,
            sek_k double DEFAULT NULL,
            sek_s double DEFAULT NULL,
            sek_p double DEFAULT NULL,
            PRIMARY KEY (timestamp)
        );
        ";

        $output = '';
        exec('sqlite3 '. DIR .'/var/hbrain.db \'.dump "findata"\' | grep \'^INSERT\'', $output);
        foreach ( $output as $line )
            $sql .= "\n        ".trim($line);


        // TABLE tempConf
        $sql .= "

    /***********************************************************************************/

        CREATE VIEW tempconf AS
        SELECT
            COUNT() AS weight,
            CAST(AVG(tempin) AS INT) AS tempinavg,
            CAST(AVG(humidin) AS INT) AS humidinavg,
            CAST(AVG(tempout) AS INT) AS tempoutavg,
            STRFTIME('%H', timestamp)*1 AS hour,
            STRFTIME('%w', timestamp)*1 AS wday
        FROM datalog
            WHERE timestamp > DATETIME(DATETIME('now', 'localtime'), '-21 DAYS')
            GROUP BY wday, hour
        ORDER BY wday, hour ASC;
        ";

        // TABLE fcm
        $sql .= "

    /***********************************************************************************/

        CREATE TABLE fcm (
            timestamp DATETIME,
            email varchar(99) NOT NULL,
            approved BOOLEAN DEFAULT false,
            token varchar(99) NOT NULL,
            PRIMARY KEY(token)
        );
        ";

        $output = '';
        exec('sqlite3 '. DIR .'/var/hbrain.db \'.dump "fcm"\' | grep \'^INSERT\'', $output);
        foreach ( $output as $line )
            $sql .= "\n        ".trim($line);

        $sql .= "

    /***********************************************************************************/

    COMMIT;\n";

        file_put_contents(DIR .'/var/hbrain.sql', $sql);
    }

    public static function dbrepair($dbFile) {
        exec('cat <( sqlite3 "'. $dbFile .'" .dump | grep "^ROLLBACK" -v ) <( echo "COMMIT;" ) | sqlite3 "fix_'. $dbFile .'"');
    }
}

?>
