<?php

require_once 'csv/Csv.php';
class csvResource extends \classes\Interfaces\resource{

    private static $instance = NULL;
    public static function getInstanceOf(){
        $class_name = __CLASS__;
        if (!is_object(self::$instance))self::$instance = new $class_name();
        return self::$instance;
    }
    
    public function getCsvResource($filename, $separator = ";", $colum_key = "", $hasHeader = true, $reversed = false){
        return $this->setSeparator($separator)
                    ->setColumKey($colum_key)
                    ->setHeader($hasHeader)
                    ->setReversed($reversed)
                    ->execute($filename);
    }
    
    private $separator = ";";
    public function setSeparator($separator){
        $this->separator = $separator;
        return $this;
    }
    
    private $colkey = "";
    public function setColumKey($colkey){
        $this->colkey = $colkey;
        return $this;
    }
    
    private $header = true;
    public function setHeader($header){
        $this->header = $header;
        return $this;
    }
    
    private $reversed = true;
    public function setReversed($reversed){
        $this->reversed = $reversed;
        return $this;
    }
    
    public function execute($filename){
        if(!file_exists($filename)){
            $this->LoadResource('files/file', 'fobj');
            if(!$this->fobj->savefile($filename, "")){
                throw new \classes\Exceptions\resourceException("O arquivo $filename não existe e não pode ser criado");
            }
        }
        $obj = new Csv();
        $obj->setSeparator($this->separator)
            ->setColumKey($this->colkey)
            ->setHeader($this->header)
            ->setReversed($this->reversed)
            ->execute($filename);
        return $obj;
    }
    
}