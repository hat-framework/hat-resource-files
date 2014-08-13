<?php

require_once dirname(__FILE__). '/compression/autoload.php';
class compressionResource extends classes\Interfaces\resource{

    private $types = array('bzip' => 'bzip', 'gzip' => 'gzip','tar' => 'tgz', 'zip' => 'zip');
    private $defaultMethod = "gzip";
    private $currentMethod = "";
    public function __construct() {
        $this->setMethod($this->defaultMethod);
    }
    
    public function setMethod($method){
        if(!isset($this->types[$method])) {return;}
        $this->currentMethod = $method;
        $this->ext           = $this->types[$method];
        return $this;
    }
    
    public function compactar($toCompact, $destination = ""){
        if(!is_array($toCompact)){$toCompact = array($toCompact);}
        $destination = ($destination === "")?DIR_FILES:$destination;
        $class = "{$this->currentMethod}_file";
        $test = new $class("$destination.{$this->ext}"); 
        $test->set_options(array('basedir' => $destination, 'overwrite' => 1)); 
        $test->add_files($toCompact); 
        $test->create_archive(); 
        if (count($test->error) > 0){
            $this->setErrorMessage($test->error);
            return false;
        }
        return true;
    }
            
}