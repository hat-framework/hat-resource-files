<?php
class xlsResource extends \classes\Interfaces\resource{

    private static $instance = NULL;
    public static function getInstanceOf(){
        $class_name = __CLASS__;
        if (!is_object(self::$instance))self::$instance = new $class_name();
        return self::$instance;
    }
    
    /**
     * pega dados de arquivos .xlsx e retorna em array
     * @param string $file
     * @param boolean $array_shift
     * @return array/boolean
     */
    public function getXlsx($file,$array_shift = false){
        if(!file_exists($file))return false;
        require_once 'xls/simplexlsx.class.php';
        $xlsx = new SimpleXLSX($file);
        $var = $xlsx->rows();
        if(is_array($var) && !empty($var) && ($array_shift)){array_shift($var);}
        if(empty($var) || $var === false){
            return false;
        }
        return $var;
    }
    
    /**
     * pega dados de arquivos .xls e retorna em array
     * @param string $file
     * @param boolean $array_shift
     * @return array/boolean
     */
    public function getXls($file,$array_shift = false){
        if(!file_exists($file))return false;
        require_once 'xls/reader.php';
        $data = new Spreadsheet_Excel_Reader();
        $data->read($file);
        error_reporting(E_ALL ^ E_NOTICE);
        $var = $data->sheets[0]['cells'];
        if(is_array($var) && !empty($var) && ($array_shift)){array_shift($var);}
        if(empty($var) || $var === false){
            return false;
        }
        return $var;
    }
}