<?php

	function amp($verb)
	{
			switch ($verb)
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

?>