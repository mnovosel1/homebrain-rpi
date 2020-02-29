<?php

class Person {
    public static $debug = true;

    public static function h() {
        return MyAPI::help(self::class);
    }

    public static function help() {
        return MyAPI::help(self::class);
    }

    public static function setState($person, $state) {
        $ch   = curl_init("http://hassio:8123/api/states/person.". $person);
        $data = array(
            "state" => $state,
            "attributes" => array("entity_picture" => "/local/pics/$person.jpg")
        );
        $payload = json_encode($data);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($cHandler, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($cHandler, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiIxOWI0ZTEwZTRkZGU0NzU3ODE1ZmRmZTQ4NTU2NzVkMCIsImlhdCI6MTU4MDAzNjQwNywiZXhwIjoxODk1Mzk2NDA3fQ.iBz1CiaS0HAZOhybSxAu0fKfOxRD2bANi7H4bHk6rWA",
            "Content-Type: application/json",
        ));

        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_exec($ch);
        
        curl_close($ch);
    }
}

?>
