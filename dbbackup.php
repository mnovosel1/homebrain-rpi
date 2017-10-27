#!/usr/bin/php
<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

/* WORKING DIR constant */
define('DIR', dirname(__FILE__));

$configs = parse_ini_file(DIR .'/config.ini');

$sqlitedb = new SQLite3(DIR .'/var/hbrain.db');
$mysqlidb = new mysqli($configs["DB_REPLIC_HOST"], $configs["DB_REPLIC_USER"], $configs["DB_REPLIC_PASS"], $configs["DB_REPLIC_DBNAME"]);

$sqliteres = $sqlitedb->query('SELECT c.timestamp, c.statebefore, c.changedto, s.name state, s.auto FROM changelog c JOIN states s ON c.state = s.name;');

while ($entry = $sqliteres->fetchArray(SQLITE3_ASSOC)) {
    $mysqlidb->query("REPLACE INTO changeLog (timestamp, statebefore, state, auto, changedto) 
                        VALUES (
                                '".$entry['timestamp']."',
                                '".$entry['statebefore']."',
                                '".$entry['state']."',
                                '".$entry['auto']."',
                                ".$entry['changedto']."
                                )");
}

$sqlitedb->close();
$mysqlidb->close();

// HBRAIN //////////////////////////////////////////////////////////////////////////////////
$sql = "
    BEGIN TRANSACTION;

        CREATE TABLE states (
            name varchar(50) PRIMARY KEY,
            auto int(1) NOT NULL DEFAULT 1,
            active int(1) NOT NULL DEFAULT 0
        );

";

$output = '';
exec('sqlite3 '. DIR .'/var/hbrain.db \'.dump "states"\' | grep \'^INSERT\'', $output);
foreach ( $output as $line )
  $sql .= "        ".$line . "\n";

$sql .= "

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
  $sql .= "        ".$line . "\n";
  
$sql .= "

        CREATE TABLE changelog (
            timestamp DATETIME,
            statebefore varchar(30) NOT NULL,
            state varchar(50) NOT NULL,
            changedto int(1) NOT NULL,
            PRIMARY KEY(statebefore, state, changedto),
            FOREIGN KEY (state) REFERENCES states(name)
        );
";

$sql .= "
        CREATE TRIGGER changelog_trigg
            BEFORE UPDATE ON states
            FOR EACH ROW
            WHEN OLD.active <> NEW.active
            BEGIN
                INSERT OR REPLACE INTO changelog (timestamp, statebefore, state, changedto)
                VALUES (
                            datetime('now','localtime'), 
                            (SELECT group_concat(active, '') FROM states), 
                            NEW.name, 
                            NEW.active
                        );
                DELETE FROM changelog WHERE timestamp <= date('now', '-60 day');
            END;

";

$output = '';
exec('sqlite3 '. DIR .'/var/hbrain.db \'.dump "changelog"\' | grep \'^INSERT\'', $output);
foreach ( $output as $line )
  $sql .= "        ".$line . "\n";

$sql .= "

        CREATE VIEW logic AS 
            SELECT 
                    COUNT(*) AS weight, 
                    STRFTIME('%H', timestamp)*1 AS hour,
                    STRFTIME('%w', timestamp)*1 AS dow,
                    c.statebefore, 
                    c.changedto, 
                    s.name, 
                    s.auto
                FROM changelog c join states s ON c.state=s.name
                GROUP BY c.statebefore, c.state, c.changedto
                ORDER BY weight desc, c.timestamp desc;
";

$sql .= "
    COMMIT;

";

file_put_contents(DIR .'/hbrain.sql', $sql);
/////////////////////////////////////////////////////////////////////////////////////////////

exec ("cp ". DIR ."/var/hbrain.db ". DIR ."/saved_var/hbrain.db");

?>