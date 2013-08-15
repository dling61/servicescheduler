<?php
abstract class Resource
{
    protected static $httpMethods = array("GET", "POST", "HEAD","PUT", "OPTIONS", "DELETE", "TRACE", "CONNECT");

	protected $version;
	
	public function __construct() {
    }

	/**
    public function __construct($version, array $params) {
        $this->params = $params;
		// handle the existing clients
		if ($version == null) 
			$this->version = 1;
		else 
			$this->version = $version;
    }
  
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