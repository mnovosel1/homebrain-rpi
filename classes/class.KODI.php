<?php

class KODI {

    public static function isOn() {
		  return LAN::ping("KODI");
    }
    
}

?>