<?php

class Test {

	public static function glagol() {

		// allowed IPs
		$allowedIPs = array("127.0.0.1", "10.10.10.100");
		if ( array_search($_SERVER["REMOTE_ADDR"], $allowedIPs) === false) return false;


		/*
		// DEBUG
		ob_start();
		
		echo PHP_EOL . "NAME" . PHP_EOL . "Test" . PHP_EOL . PHP_EOL;
		echo PHP_EOL . "VERB" . PHP_EOL . "glagol" . PHP_EOL . PHP_EOL;

		echo "PARAMS" . PHP_EOL;
		var_dump($_POST["param1"]);

		$out = ob_get_clean();        
		file_put_contents('dbg.api.txt', $out . PHP_EOL . PHP_EOL);
		*/

		return $_POST["param1"];
	}
}

?>