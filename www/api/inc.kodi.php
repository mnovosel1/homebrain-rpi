<?php

	function kodi($verb)
	{
			switch ($verb)
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
	
?>