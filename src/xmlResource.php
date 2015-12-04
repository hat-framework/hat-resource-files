<?php

class xmlResource extends \classes\Interfaces\resource{
    
    /**
    * returns a xmlResource instance
    * @uses call xmlResource's constructor
    * @return returns a xmlResource instance
    */
    private static $instance = NULL;
    public static function getInstanceOf(){
        $class_name = __CLASS__;
        if (!isset(self::$instance)) {self::$instance = new $class_name;}
        return self::$instance;
    }
    
     /** 
     * xml2array() will convert the given XML text to an array in the XML structure. 
     * Link: http://www.bin-co.com/php/scripts/xml2array/ 
     * @param string $contents - The XML text 
     * 
     * @return The parsed XML in an array form. Use print_r() to see the resulting array structure.
     */ 
    public function xml2array($contents) { 
        require_once dirname(__FILE__).'/classes/XML2Array.php';
        return XML2Array::createArray($contents);
    }
    
    /** 
     * array2xml will convert the given array to XML text.
     * @param array $array multi dimensional array to be converted in XML data
     * @param string $file [optional] If specified, the function writes the data to the file rather than returning it.
     * 
     * @return mixed If the filename isn't specified, this function returns a XML string on success and FALSE on error. 
     * If the parameter is specified, it returns TRUE if the file was written successfully and FALSE otherwise.
     */ 
    public function array2xml($array, $file = "") { 
        $xml = new SimpleXMLElement('<?xml version="1.0"?><data></data>');
        $this->convertArray2xml($array, $xml);
        if($file != ""){return $xml->asXML($file);}
        return $xml->asXML();
    }
    
            private function convertArray2xml( $data, &$xml) {
                foreach( $data as $key => $value ) {
                    if( is_array($value) ) {
                        if( is_numeric($key) ){
                            $key = 'item'.$key; //dealing with <0/>..<n/> issues
                        }
                        $subnode = $xml->addChild($key);
                        $this->convertArray2xml($value, $subnode);
                    } else {
                        $xml->addChild("$key",htmlspecialchars("$value"));
                    }
                 }
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