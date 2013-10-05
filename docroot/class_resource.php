<?php
abstract class Resource
{
    protected static $httpMethods = array("GET", "POST", "HEAD","PUT", "OPTIONS", "DELETE", "TRACE", "CONNECT");

	protected $version;
	/**
	public function __construct() {
    }
     **/
	
    public function __construct($request) {
		if (isset($request->parameters['version'])) {
			$_version = $request->parameters['version'];
			// handle the existing clients
			if ($_version == null) 
				$this->version = 1;
			else 
				$this->version = $_version;
		}
		else
			$this->version = 1;
    }
    /**
    protected function allowedHttpMethods() {

        $myMethods = array();

        $r = new ReflectionClass($this);

        foreach ($r->getMethods(ReflectionMethod::IS_PUBLIC) as $rm) {
            $myMethods[] = strtoupper($rm->name);
        }

        return array_intersect(self::$httpMethods, $myMethods);
    }

    public function __call($method, $arguments) {
        header("HTTP/1.1 405 Method Not Allowed", true, 405);
        header("Allow: " . join($this->allowedHttpMethods(), ", "));
    }
   **/
}
?>