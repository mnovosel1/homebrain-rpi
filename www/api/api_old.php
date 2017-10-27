<?php
/* WORKING DIR constant */
define('DIR', str_replace('/www/api', '', dirname(__FILE__)));

/* CLASS definition */
require_once (DIR . '/www/api/class.api.php');

/* USER CLASS definition */
class MyAPI extends API
{
    protected $sqlite;

    public function __construct($request, $origin)
	{	
        parent::__construct($request);
    }
	
     protected function getinfo()
	{
		$this->sqlite 	= new SQLite3(DIR .'/var/hbrain.db');
		$sqliteres = $this->sqlite->query("SELECT group_concat(active, '') status FROM states ORDER BY rowid ASC");

		$status = $sqliteres->fetchArray(SQLITE3_ASSOC);
		$status = $status['status'];


		$sqliteres = $this->sqlite->query("SELECT rowid, * FROM states ORDER BY rowid ASC");
		while ($entry = $sqliteres->fetchArray(SQLITE3_ASSOC))
		{
			$states[$entry['rowid']] = $entry['name'];
		}

		for ($i=1; $i <= strlen($status); $i++)
		{ 
			$ret[$states[$i]] = (int)$status[$i-1];
		}		
		
		$configs = parse_ini_file(DIR .'/heating/config.ini');
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
	
	
	protected function update()
	{
		switch ($this->verb)
		{
			case 'hsrv':
				switch ($this->args[0])
				{
					case 0:
					break;
					
					case 1:
					break;
				}
			break;

			case 'hsrvbusy':
				switch ($this->args[0])
				{
					case 0:
					break;
					
					case 1:
					break;
				}
			break;
		}
	}
	 
	 protected function fcm()
	 {
		if ( $this->method == 'PUT' )
		{
			$this->sqlite 	= new SQLite3(DIR .'/var/hbrain.db');

			switch ($this->verb)
			{
				case 'reg':
					$argumenti = explode("____", $this->args[0]);

					$sql = "INSERT INTO fcm (timestamp, email, token)
								VALUES( datetime('now', 'localtime'), 
										'".$argumenti[0]."',
										'".$argumenti[1]."'
										)";

					$ret = $this->sqlite->query($sql);
					$error = $this->sqlite->lastErrorMsg();

					exec ("cp ". DIR ."/var/hbrain.db ". DIR ."/saved_var/hbrain.db");
/*
					ob_start();
					//var_dump($argumenti);
					echo $error . PHP_EOL;
					echo $sql;
					$out = ob_get_clean();
					file_put_contents('debug.txt', $out . PHP_EOL . PHP_EOL, FILE_APPEND);
*/
					if ( $ret !== false ) return json_encode(true);
					else return json_encode(false);
				break;
			}			
		}
		return FALSE;
	 }
	 
    /* HomeServer Endpoint */
	 protected function hsrv()
	 {
		if ( $this->method == 'PUT' )
		{
			$this->sqlite 	= new SQLite3(DIR .'/var/hbrain.db');
			$this->sqlite->query("UPDATE states SET active=1 WHERE name='HomeServer'");
			
			switch ($this->verb)
			{
				case 'serverbusy':
					return json_encode($this->sqlite->query("UPDATE states SET active=".$this->args[0]." WHERE name='HomeServer busy'"));
				break;
			}
		}
		return FALSE;
	 }
	 
	protected function amp()
	{
		if ( $this->method == 'PUT' )
		{
			switch ($this->verb)
			{
				case 'on':
					exec (DIR ."/IR/_amp-on");
				break;

				case 'off':
					exec (DIR ."/IR/_amp-off");
				break;
				
				case 'kodi':
					exec("irsend SEND_ONCE Yamaha MD_CDR_INPUT");
				break;
				
				case 'mpd':
					exec("irsend SEND_ONCE Yamaha D-TV_CBL_INPUT");
				break;
				
				case 'aux':
					exec("irsend SEND_ONCE Yamaha V-AUX_INPUT");
				break;
				
				case 'mute':
					exec("irsend SEND_ONCE Yamaha MUTE");
				break;
				
				case 'volup':
					exec("irsend SEND_ONCE Yamaha VOLUME_UP");
					exec("irsend SEND_ONCE Yamaha VOLUME_UP");
					exec("irsend SEND_ONCE Yamaha VOLUME_UP");
					exec("irsend SEND_ONCE Yamaha VOLUME_UP");
				break;
				
				case 'voldown':
					exec("irsend SEND_ONCE Yamaha VOLUME_DOWN");
					exec("irsend SEND_ONCE Yamaha VOLUME_DOWN");
					exec("irsend SEND_ONCE Yamaha VOLUME_DOWN");
					exec("irsend SEND_ONCE Yamaha VOLUME_DOWN");
				break;
				
			}
		}
	}
	
	protected function kodi()
	{
		if ( $this->method == 'PUT' )
		{
			switch ($this->verb)
			{
				case 'true':
					exec (DIR ."/lan/kodiWake.sh");
				break;

				case 'false':
					exec (DIR ."/lan/kodiShut.sh", $ret);
					exec (DIR . '/notify/kodi.php "Gasim KODI.."');
					//return json_encode($ret);
				break;
			}
			exec(DIR ."/lan/wakeCheck.php");
		}
	}

	protected function mpd()
	{
		if ( $this->method == 'PUT' )
		{
			switch ($this->verb)
			{
				case 'true':
					exec (DIR ."/IR/_playradio");
				break;

				case 'false':
					exec (DIR ."/IR/_stopradio");
				break;
			}
			exec(DIR ."/lan/wakeCheck.php");
		}
	}
 }

 
 
 /* Requests from the same server don't have a HTTP_ORIGIN header */
if (!array_key_exists('HTTP_ORIGIN', $_SERVER))
{
    $_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
}


try
{
	$API = new MyAPI($_REQUEST['request'], $_SERVER['HTTP_ORIGIN']);
	echo $API->processAPI();
}
catch (Exception $e)
{
	echo json_encode(Array('error' => $e->getMessage()));
}


?>