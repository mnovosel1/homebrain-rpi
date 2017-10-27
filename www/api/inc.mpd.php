<?php

	function mpd($verb)
	{
		switch ($verb)
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
	
?>