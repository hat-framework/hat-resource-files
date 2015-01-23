<?php 

/** 
* Implementation of CsvInterface 
*/ 
ini_set("auto_detect_line_endings", true);
use classes\Classes\Object;
class Csv extends classes\Classes\Object{ 

    /** 
    * CSV filename with path 
    * @type string 
    */ 
    protected $filename; 

    /** 
    * CSV separator 
    * @type string 
    */ 
    protected $separator; 

    /** 
    * CSV file resource link 
    * @type resource 
    */ 
    protected $csvH; 


    public function __construct($filename, $separator = ";", $colum_key = "", $hasHeader = true){ 
        if (!is_string($filename)) { 
            throw new Exception("Illegal parameter filename. Must be string."); 
        } 
        if (!is_string($separator)) { 
            throw new Exception("Illegal parameter separator. Must be string."); 
        }
        $this->key       = $colum_key;
        $this->filename  = $filename; 
        $this->separator = $separator; 
        $this->open();
        $this->csv2array($hasHeader);
    } 

    public function __destruct(){ 
        if (is_resource($this->csvH)) { 
            fclose($this->csvH); 
        } 
    } 

    /** 
    * open file defined with filename 
    * @return void 
    */ 
    protected function open(){ 
        if (is_resource($this->csvH)) { 
            return true; 
        } 
        if (!strlen($this->filename)) { 
            throw new Exception("There is no filename parameter."); 
        } 
        
        if(!$fc = iconv('windows-1250', 'utf-8', file_get_contents($this->filename))){
            throw new Exception("Cannot find/open '". $this->filename ."'."); 
        }
        $handle=fopen("php://memory", "rw"); 
        fwrite($handle, $fc); 
        fseek($handle, 0); 
        $this->csvH = $handle;
        return true; 
    } 
    
    private $sum   = array();
    private $array = array();
    private function csv2array($hasHeader = true){
        if(!empty($this->array)) return $this->array;
        $conteudo = array();
        while ($data = fgetcsv ($this->csvH, 1000, $this->separator)) {
            if(empty($this->header) && $hasHeader) {
                $this->header = $data;
                continue;
            }
            $blacklist = array();
            if(count($this->header) != count($data)){
                $c1 = count($this->header);
                $c2 = count($data);
                print_r($data);
                //print_r( $data);
                print_r($this->header);
                die("A quantidade de colunas das linhas de $this->filename não é igual:
                     Header: $c1, Line: $c2");
            }
            $var = array_combine($this->header, $data);
            foreach($var as $nm => $val){
                if(array_key_exists($nm, $blacklist)) continue;
                if(!is_numeric($val)) {
                    $blacklist[$nm] = $nm;
                    if(isset($this->sum[$nm])) unset($this->sum[$nm]);
                    continue;
                }
                
                if(!isset($this->sum[$nm])) $this->sum[$nm] = 0;
                $this->sum[$nm] += $val;
            }
            
            if($this->key != "" && array_key_exists($this->key, $var))
                 $conteudo[$var[$this->key]] = $var;
            else $conteudo[] = $var;
        }
        $this->array = $conteudo;
        return $conteudo;  
    }
    
    public function getSum($coluna){
        return isset($this->sum[$coluna])?$this->sum[$coluna]:0;
    }
    
    public function getMedia($coluna){
        $sum = isset($this->sum[$coluna])?$this->sum[$coluna]:0;
        if($sum == 0) return 0;
        $number = $this->getNumberOfLines();
        if($number <= 0) return 0;
        return ($sum/$number);
    }
    
    public function getAllLines(){
        return $this->array;
    }
    
    public function getNumberOfLines(){
        return count($this->array);
    }
    
    private $lineNumber = 0;
    public function getNextLine(){
        $current = $this->getCurrentLine();
        if($current != array()) $this->lineNumber++;
        return $current;
    }
    
    public function getCurrentLine(){
        return (!isset($this->array[$this->lineNumber]))? array(): $this->array[$this->lineNumber];
    }
    
    private $header = array();
    public function getHeader(){
        if(empty($this->header)) $this->arrayLines ();
        return $this->header;
    }
    
    public function removeColum($coluna){
        
        if(empty($this->array)) return;
        if(empty($this->header)) return;
        if(!in_array($coluna, $this->header)) return;
        unset($this->header[$coluna]);
        
        foreach($this->array as &$line){
            if(!isset($line[$coluna])) continue;
            unset($line[$coluna]);
        }
    }
    
    public function filtherColuns($colunas){
        if(!is_array($colunas)) return;
        foreach($this->header as $var){
            if(in_array($var, $colunas)) continue;
            $this->removeColum($var);
        }
    }
    
    /**
     * Recebe um array multi dimensional e transforma em um array csv
     * 
     * @param array $lines
     * Créditos deste método: http://stackoverflow.com/questions/3933668/convert-array-into-csv
     */
    public function arrayToCsv(array &$lines, $append = false) {
        $this->lineNumber = 0;
        if(!$append) $this->array = array();
        //$this->header[$this->key] = "$this->key";
        foreach ( $lines as $key => $line ) {
            $ch = count($this->header);
            $cl = count($line);
            if(!empty($this->header) && $ch > 1 && $cl != $ch) 
                throw new Exception(__METHOD__. ": Número de colunas diferentes entre o cabeçalho e a linha. Header: $ch, Line: $cl");
            foreach($line as $cod => $field){
                //$this->array[$key][$this->key] = $key;
                $this->header[$cod] = $cod; 
                if ($field === null) {
                    $this->array[$cod] = 'NULL';
                    continue;
                }
                if(is_array($field)){
                    throw new Exception("variável field não pode ser um array!");
                }
                $this->array[$key][$cod] = $field;
            }
        }
        
    }
    
    /**
     * Salva os dados do arquivo csv em disco. Atenção! O programa tentará sobrescrever
     * o arquivo!
     */
    public function save(){
        $str = implode($this->separator, $this->header) . "\n";
        foreach ( $this->array as $line ) {
            $str .= implode($this->separator, $line)."\n";
        }
        $this->LoadResource('files/file', 'fobj');
        $this->fobj->savefile($this->filename, $str);
        return true;
    }
    
}