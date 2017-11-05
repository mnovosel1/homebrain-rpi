<?php

class Test {

	public static function glagol() {

		// allowed IPs
		if ( !Auth::allowedIP([Configs::getIP("HomeServer")]) ) return false;

		return $_POST["param1"];
	}
}

?>