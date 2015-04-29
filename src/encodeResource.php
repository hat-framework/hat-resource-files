<?php 
// Unicode BOM is U+FEFF, but after encoded, it will look like this. 
define ('UTF32_BIG_ENDIAN_BOM'   , chr(0x00) . chr(0x00) . chr(0xFE) . chr(0xFF)); 
define ('UTF32_LITTLE_ENDIAN_BOM', chr(0xFF) . chr(0xFE) . chr(0x00) . chr(0x00)); 
define ('UTF16_BIG_ENDIAN_BOM'   , chr(0xFE) . chr(0xFF)); 
define ('UTF16_LITTLE_ENDIAN_BOM', chr(0xFF) . chr(0xFE)); 
define ('UTF8_BOM'               , chr(0xEF) . chr(0xBB) . chr(0xBF)); 

/** 
 * This class try to guess the proper encoding of a string/file. 
 * Example:  
 * $encoding_array = DetectEncoding::from_string("Feliz aÃ±o nuevo!"); 
 * $encoding_array = DetectEncoding::from_file("path/to/some/file.txt"); 
 */ 
class encodeResource extends \classes\Interfaces\resource{ 
    
    static private $instance;
    private function __construct(){}
    public static function getInstanceOf(){
        $class_name = __CLASS__;
        if (!isset(self::$instance))  self::$instance = new $class_name;
        return self::$instance;
    }
        
    /** 
     * A simple way to detect UTF-8/16/32 of string by its BOM (not work with string or file without BOM) 
     * @return string|false encoding found 
     */ 
    static private function detect_utf_encoding($text) { 
        $first2 = substr($text, 0, 2); 
        $first3 = substr($text, 0, 3); 
        $first4 = substr($text, 0, 3); 
        if ($first3 == UTF8_BOM) return 'UTF-8'; 
        elseif ($first4 == UTF32_BIG_ENDIAN_BOM) return 'UTF-32BE'; 
        elseif ($first4 == UTF32_LITTLE_ENDIAN_BOM) return 'UTF-32LE'; 
        elseif ($first2 == UTF16_BIG_ENDIAN_BOM) return 'UTF-16BE'; 
        elseif ($first2 == UTF16_LITTLE_ENDIAN_BOM) return 'UTF-16LE'; 
        return false; 
    } 
     
    /** 
     * Try to detect non UTF encodings 
     * @return array all provable encodings 
     */ 
    static private function detect_non_uft_encoding($text) { 
        $guesses = array(); 
        foreach(mb_list_encodings() as $item) { 
            //avoid checking for UTF, 'pass' & 'auto' encodings 
            if ($item === 'pass' || $item === 'auto' || strripos($item, 'UTF')!==false ){ 
                continue; 
            } 
            $sample = @iconv($item, $item, $text); 
            if (strlen($sample) & md5($sample)==md5($text)) {  
                $guesses[] = $item; 
            } 
        } 
        return $guesses; 
    } 
     
    /** 
     * Try to detect all provable encodings from a given string 
     * @return array with encoding guesses 
     */ 
    static function from_string($text, $default_encoding = 'UTF-8'){ 
        $default_guess = array($default_encoding); 
        if (empty($text)) return $default_guess; 
        $guesses = self::detect_non_uft_encoding($text); 
        $uft = self::detect_utf_encoding($text); 
        if ($uft){ 
            array_unshift($guesses, $uft); 
        } 
        //put the default encoding at the top 
        if (in_array($default_encoding, $guesses)){ 
            $guesses = array_diff($guesses, $default_guess); 
            array_unshift($guesses, $default_encoding); 
        } 
        return $guesses; 
    } 
     
    /** 
     * Try to detect all provable encodings from a given file 
     * @return array with encoding guesses 
     */ 
    static function from_file($filename, $default_encoding = 'UTF-8')  { 
        return self::from_string(@file_get_contents($filename), $default_encoding); 
    } 
    
    
    static function tryConvert($string, $default_encoding = 'UTF-8'){
        $array = self::from_string($string, $default_encoding); 
        if(empty($array)){return false;}
        foreach($array as $encode){
            $sample = @iconv($encode, $default_encoding, $string); 
            echo "($encode)<br/>$sample<hr/>";
        }
    }
} 