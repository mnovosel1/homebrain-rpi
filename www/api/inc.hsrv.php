<?php

	function hsrv($verb)
	{
		$sqlite = new SQLite3(DIR .'/var/hbrain.db');
		$sqlite->query("UPDATE states SET active=1 WHERE name='HomeServer'");
		
		$args   = func_get_args();
		
		switch ($verb)
		{
			case 'serverbusy':				
				return json_encode($sqlite->query("UPDATE states SET active=".$args[1][0][0]." WHERE name='HomeServer busy'"));
			break;
		}
			
		return FALSE;
	}

?>