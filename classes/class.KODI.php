<?php

class KODI {
    public static $debug = true;

    public static function isOn() {
          if ( LAN::ping("KODI") ) {
			SQLITE::update("states", "active", 1, "`name`='KODI'");
			return true;
		}

		else {
			SQLITE::update("states", "active", 0, "`name`='KODI'");
			return false;
		}
    }
    
}

?>