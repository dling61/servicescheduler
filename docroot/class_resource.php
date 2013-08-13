<?php
abstract class Resource
{
    protected static $httpMethods = array("GET", "POST", "HEAD","PUT", "OPTIONS", "DELETE", "TRACE", "CONNECT");

    protected $params;

    public function __construct(array $params) {
        $this->params = $params;
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

}
?>