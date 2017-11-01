<?php

class MyAPI extends API {
    
        public function __construct($request, $origin) {
    
            parent::__construct($request);
        }
        
        public function __call($name, $args) {
    
            if ( Auth::OK() ) {

                /*
                */
                ob_start();
				
				echo PHP_EOL . $_SERVER["REMOTE_ADDR"] . PHP_EOL;
				
                echo PHP_EOL . "NAME" . PHP_EOL;
                var_dump($name);
                
                echo PHP_EOL . "VERB" . PHP_EOL;
                var_dump($this->verb);
        
                echo PHP_EOL . "POST" . PHP_EOL;
                var_dump($_POST);
        
                $out = ob_get_clean();
                file_put_contents('dbg.api.txt', $out . PHP_EOL . PHP_EOL, FILE_APPEND);

                if ( class_exists( $name ) ) {
        
					if ( isset($_POST["param1"]) && strpos($_POST["param1"], "|") !== false ) {
		
						$_POST["param1"] = explode("|", trim($_POST["param1"]));
					}
					
					$verb = $this->verb;
					return $name::$verb();
                }
            }  
    
            return false;  
        }
    }

?>