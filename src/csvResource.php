<?php

require_once 'csv/Csv.php';
class csvResource extends \classes\Interfaces\resource{

    private static $instance = NULL;
    public static function getInstanceOf(){
        $class_name = __CLASS__;
        if (!is_object(self::$instance))self::$instance = new $class_name();
        return self::$instance;
    }
    
    public function getCsvResource($filename, $separator = ";", $colum_key = "", $hasHeader = true){
        if(!file_exists($filename)){
            $this->LoadResource('files/file', 'fobj');
            if(!$this->fobj->savefile($filename, ""))
                throw new \classes\Exceptions\resourceException("O arquivo $filename não existe e não pode ser criado");
        }
        return new Csv($filename, $separator, $colum_key, $hasHeader);
    }
    
}