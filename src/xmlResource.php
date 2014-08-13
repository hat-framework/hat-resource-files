<?php

class xmlResource extends \classes\Interfaces\resource{
    
    /**
    * retorna a instância do banco de dados
    * @uses Faz a chamada do contrutor
    * @throws DBException
    * @return retorna um objeto com a instância do banco de dados
    */
    private static $instance = NULL;
    public static function getInstanceOf(){
        
        $class_name = __CLASS__;
        if (!isset(self::$instance)) {
            self::$instance = new $class_name;
        }

        return self::$instance;
    }
    
     /** 
     * xml2array() will convert the given XML text to an array in the XML structure. 
     * Link: http://www.bin-co.com/php/scripts/xml2array/ 
     * Arguments : $contents - The XML text 
 *                $get_attributes - 1 or 0. If this is 1 the function will get the attributes as well as the tag values - this results in a different array structure in the return value.
 *                $priority - Can be 'tag' or 'attribute'. This will change the way the resulting array sturcture. For 'tag', the tags are given more importance.
     * Return: The parsed XML in an array form. Use print_r() to see the resulting array structure. 
     * Examples: $array =  xml2array(file_get_contents('feed.xml')); 
     *              $array =  xml2array(file_get_contents('feed.xml', 1, 'attribute')); 
     */ 
    public function xml2array($contents) { 
        require_once dirname(__FILE__).'/classes/XML2Array.php';
        return XML2Array::createArray($contents);
    }
    
    public function array2xml($array) { 
        require_once dirname(__FILE__).'/classes/XML2Array.php';
        return XML2Array::convert($array);
    }
    
    public function getXmlArray($file){
        if(!file_exists($file)){
            $this->setErrorMessage("O arquivo $file não existe");
            return false;
        }
        
        $contents = file_get_contents($file);
        return $this->xml2array($contents);
    }

}

?>