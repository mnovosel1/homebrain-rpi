<?php

require_once DIR . "/classes/protected/class.Configs.php";

class MyAPI extends API {
    
        public function __construct($request, $origin) {    
            parent::__construct($request);
        }
        
        public function __call($name, $args) {
            
            $name = ucfirst($name);
			$verb = strtolower($this->verb);
            
            if ( DEBUG ) { ////////////////////////////////////////////////////////////////////////
                
                    ob_start();
                    
                    echo PHP_EOL . date("H:i:s");

                    echo PHP_EOL . "IP " . $_SERVER["REMOTE_ADDR"];

                    echo PHP_EOL . "AUTH " . Auth::OK() . PHP_EOL;
                    
                    echo PHP_EOL . "NAME: ";
                    var_dump($name);
                    
                    echo PHP_EOL . "VERB: ";
                    var_dump($this->verb);
                
                    echo PHP_EOL . "POST" . PHP_EOL;
                    var_dump($_POST);
                
                    //var_dump(parse_ini_file(DIR.'/config.ini'));

                    $out = ob_get_clean();
                    file_put_contents(DIR.'/'.Configs::get("DEBUG_LOG"), $out.PHP_EOL);

            } /////////////////////////////////////////////////////////////////////////////////////

            if ( Auth::OK() ) {
                if ( class_exists($name) && method_exists($name, $this->verb) ) {                    
					return $name::$verb();
                }
            }
    
            return false;  
        }
    }

?>