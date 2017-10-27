<?php

	function fcm ($verb)
	{
		$ret 		= false;
		$sqlite 	= new SQLite3(DIR .'/var/hbrain.db');

		switch ($verb)
		{
			case 'reg':

				if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) break;

				$sql = 	"INSERT OR REPLACE INTO fcm (timestamp, email, token)
							 VALUES(datetime('now', 'localtime'), '".$_POST["email"]."', '".$_POST["token"]."')";

				$ret = $sqlite->query($sql);

				$code = substr($_POST["token"], strlen($_POST["email"])*(-1));
				exec (DIR . "/notify/email.php " . "'HomeBrain: verify email..' " . "'SupeSecretCODE: " . $code . "' " . $_POST["email"]);

				exec ("cp ". DIR ."/var/hbrain.db ". DIR ."/saved_var/hbrain.db");

				if ( $ret !== false) $ret = true;
			break;

			case 'verify':
			
				$res = $sqlite->query("SELECT token FROM fcm WHERE email = '". $_POST["email"]."'");
				while ( $row = $res->fetchArray()) {
					if ( true || $_POST["code"] == substr($row["token"], strlen($_POST["email"])*(-1)) ) {
						// in fcm schema change approved to verified
						// update fcm table to verified=true

						if ( $_POST["email"] == "marijo.novosel@gmail.com" )
							$cfgMessage = '{"pages":["home", "multimedia", "grijanje", "lan", "vrt"], "homeUrl":"10.10.10.10"}';
						else
							$cfgMessage = '{"pages":["multimedia"], "homeUrl":"10.10.10.10"}';
						exec(DIR . "/notify/fcm.php '".$cfgMessage."' '".$row["token"]."'");

		ob_start();
		echo DIR . "/notify/fcm.php '".$cfgMessage."' '".$row["token"]."'";
		//echo $sql;
		//echo $sqlite->lastErrorMsg();
		//var_dump($verb);
		$out = ob_get_clean();
		file_put_contents('newapiDbg.txt', $out . PHP_EOL . PHP_EOL, FILE_APPEND);
		/*
		*/
					}
				}

				
			break;
		}
		
		
		$sqlite->close();
		return json_encode($ret);
	}

?>