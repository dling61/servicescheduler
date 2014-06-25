<?php

require_once('constants.php');
require_once('common_fn.php');

class Request {
    public $url_elements;
    public $action;
    public $parameters;
	public $body_parameters;
	public $lastid;

    public function __construct() {
        $this->action = $_SERVER['REQUEST_METHOD'];
		$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
		//$path = trim($path, "/");
		$this->url_elements = explode('/', $path);
		
        $this->parseIncomingParams();
        // initialise json as default format
        $this->format = 'json';
        if(isset($this->parameters['format'])) {
            $this->format = $this->parameters['format'];
        }
		
        return true;
    }

    public function parseIncomingParams() {
        $parameters = array();
		$body_parameters = array();

        // first of all, pull the GET vars
        if (isset($_SERVER['QUERY_STRING'])) {
            parse_str($_SERVER['QUERY_STRING'], $parameters);
        }
        
        // now how about PUT/POST/PUT bodies? 
        $body = file_get_contents("php://input");
	
		//log into the log table
		// TBD: for production it's 1; for testing it's 2
		if (DEBUG_FLAG == 1) {
				$url_string = implode("/", $this->url_elements);
				$this->lastid = logserver($url_string, $this->action, $body);
		}
			
        $content_type = false;
        if(isset($_SERVER['CONTENT_TYPE'])) {
            $content_type = $_SERVER['CONTENT_TYPE'];
        }
        switch($content_type) {
            case "application/json":
                //$body_params = json_decode($body);
				$body_params = json_decode($body, true, 512);
                if($body_params) {
                    foreach($body_params as $param_name => $param_value) {
                        $body_parameters[$param_name] = $param_value;
                    }
                }
                $this->format = "json";
                break;
            case "application/x-www-form-urlencoded":
                parse_str($body, $postvars);
                foreach($postvars as $field => $value) {
                    $body_parameters[$field] = $value;
                }
                $this->format = "html";
                break;
			case "application/json, application/json":
                $body_params = json_decode($body);
                if($body_params) {
                    foreach($body_params as $param_name => $param_value) {
                        $body_parameters[$param_name] = $param_value;
                    }
                }
                $this->format = "json";
                break;
            default:
                // we could parse other supported formats here
                break;
        }
        $this->parameters = $parameters;
		$this->body_parameters = $body_parameters;
    }
}
