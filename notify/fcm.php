#!/usr/bin/php
<?php
/* WORKING DIR constant */
define('DIR', str_replace('/notify', '', dirname(__FILE__)));

#API access key from Google API's Console
define( 'API_ACCESS_KEY', 'AAAAmadg5-I:APA91bFtQtjnp9899CTRWeWCeI39OobdY-mEmk4FUktw5ZRDiIYZ9NQ07scDNJ1R1tEDLdNJ0_DSUbXVhJGd4uH1bM1P8XlKg_Ia7eQF4n6miHb36jkf3NljXUodWFKi62Se0qg1oFRJ' );

$ttl = 300;
$singleMsg = false;

// if first parameter is JSON => singleMsg is true
if ( $argv[1][0] == '{' && $argv[1][strlen($argv[1]) - 1] == '}' ) {
	
		$singleMsg = true;
}

$sqlite = new SQLite3(DIR .'/var/hbrain.db');
$sqliteres = $sqlite->query("SELECT token FROM fcm WHERE approved = 'false'");
while ($entry = $sqliteres->fetchArray(SQLITE3_ASSOC)) {

	$fields["to"] = $entry["token"];
	if ( !$singleMsg ) $fields["time_to_live"] = $ttl;

	// single msg to one recipient
	if ( $singleMsg ) {

		$fields["to"] = $argv[2];

		// CONFIGS
		$fields['data'] = array("configs" => $argv[1]);
	}

	// broadcast messages
	else {	
		
		// notification data
		switch ( true )
		{
			// data with custom title and data payload
			case isset($argv[3]):
				$fields['data'] = array(
										'title' => $argv[1],
										'msg' 	=> $argv[2],
										'data' 	=> $argv[3]
										);
			break;
			
			// notify with custom title
			case isset($argv[2]):
				$fields['data'] = array(
										'title' => $argv[1],
										'msg' 	=> $argv[2]
										);
			break;

			// notify simple
			default:
				$fields['data'] = array(
										'title' => 'HomeBrain', 
										'msg' => $argv[1]
										);
		}
	}

	$headers = array
		(
			'Authorization: key=' . API_ACCESS_KEY,
			'Content-Type: application/json'
		);

	#Send Reponse To FireBase Server	
			$ch = curl_init();
			curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
			curl_setopt( $ch,CURLOPT_POST, true );
			curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
			curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
			$result = curl_exec($ch);
			curl_close( $ch );


	$result = json_decode($result);
	if ( $result->failure > 0 ) {

		$sqlite->busyTimeout(5000);
		$sqlite->query("DELETE FROM fcm WHERE token = '" . $entry["token"] . "'");		
	}

	if ( $singleMsg ) break;
}

$sqlite->close();
