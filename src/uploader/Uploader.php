<?php

use classes\Classes\Object;
class Uploader extends classes\Classes\Object{

    private $size = 0;
    private $dir  = "";
    public function __construct() {
        $this->dir = dirname(__FILE__). '/up/';
        require_once dirname(__FILE__). '/type/Upload.php';
    }

    public function Upload($arquivo){
        $obj = $this->LoadTypeObject($arquivo['type']);
        if($obj == null) return false;
        
        $obj->setDir($this->dir);
        $obj->set_max_size($this->size);
        $bool = $obj->Save($arquivo);
        $this->setMessages($obj->getMessages());
        return $bool;
    }
    
    public function setDir($diretorio){
        $this->dir = $diretorio;
    }
    
    public function set_max_size($max_size){
        $this->size = $max_size;
    }
    
    private function LoadTypeObject($type){
        $type  = explode('/', $type);
        $class = array_shift($type) . "Upload";
        $file  = dirname(__FILE__) . "/type/$type/$class.php";
        if(!file_exists($file)){
            $class = "commonUpload";
            $file = dirname(__FILE__) . "/type/common/$class.php";
            if(!file_exists($file)){
                $this->setErrorMessage("O arquivo da class $class não foi encontrado ou não existe!");
                return null;
            }
        }
        require_once $file;
        if(!class_exists($class, false)){
            $this->setErrorMessage("A class $class não foi encontrada");
            return null;
        }
        return new $class();
    }
    
}

?>