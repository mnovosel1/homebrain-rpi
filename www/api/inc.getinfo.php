<?php

	function getinfo()
	{
		$sqlite 	= new SQLite3(DIR .'/var/hbrain.db');
		$sqliteres = $sqlite->query("SELECT group_concat(active, '') status FROM states ORDER BY rowid ASC");

		$status = $sqliteres->fetchArray(SQLITE3_ASSOC);
		$status = $status['status'];


		$sqliteres = $sqlite->query("SELECT rowid, * FROM states ORDER BY rowid ASC");
		while ($entry = $sqliteres->fetchArray(SQLITE3_ASSOC))
		{
			$states[$entry['rowid']] = $entry['name'];
		}

		for ($i=1; $i <= strlen($status); $i++)
		{ 
			$ret[$states[$i]] = (int)$status[$i-1];
		}		

		$configs = parse_ini_file(DIR .'/config.ini');
		$temp = explode('|', trim(file_get_contents(DIR .'/var/lastTemp.dat')));
		$ret['tempSet'] = $temp[0];
		$ret['tempIn'] = $temp[1];
		$ret['tempOut'] = $temp[2];
		$ret['humidIn'] = $temp[3];
		$ret['timestamp'] = $temp[4];
		$ret['tempSetForce'] = $configs["TEMPSET_FORCE"];

		if ( $ret['MPD playing'] != 0 ) $ret['MPD playing'] = exec("mpc current");
		if ( $ret['MPD playing'] == "" ) $ret['MPD playing'] = 0;
		if ( $ret['KODI'] != 0 ) 
		{
			$ret['KODI'] = json_decode(exec('curl -X POST -H "Content-Type: application/json" -d \'{"jsonrpc": "2.0", "method": "Player.GetItem", "params": { "properties": ["showtitle", "title", "season", "episode"], "playerid": 1 }, "id": "VideoGetItem"}\' http://10.10.10.20:80/jsonrpc'), true);

			$ret['KODI'] = $ret['KODI']['result']['item'];
		}
		
		return json_encode($ret);
	}

?>