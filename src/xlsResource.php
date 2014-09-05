<?php
class xlsResource extends \classes\Interfaces\resource{

    private static $instance = NULL;
    public $where = '';
    public $columns = array();
    public $fileName = '';
    public $titles = array();
    public $callBack = NULL;
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
        $var = $this->readXlsx($file);
        if(is_array($var) && !empty($var) && ($array_shift)){array_shift($var);}
        if(empty($var) || $var === false){
            return false;
        }
        return $var;
    }

                /**
                 * Ler arquivo .xlsx
                 * @param type $file
                 * @return type
                 */
                private function readXlsx($file){
                    require_once 'xls/simplexlsx.class.php';
                    $xlsx = new SimpleXLSX($file);
                    return $xlsx->rows();
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
    
    
    
    /**
     * Salva arquivo excel formato .xlsx
     * @param string $model - ex: usuario/login
     * @param string $filename - ex: DIR_FILES.'out'
     * @return boolean
     */
    public function saveXlsx($model, $filename) {
        $this->LoadModel($model, 'md');
        $data = $this->md->getDados();
        $col = ($this->columns == array())?true:false;
        $tit = ($this->titles == array())?true:false;
            foreach ($data as $name => $dt) {
                if (isset($dt['private']) || !isset($dt['display']))
                    continue;
                if($col)$this->columns[] = $name;
                if($tit)$this->titles[] = $dt['name'];
            }
        $arr = $this->md->selecionar($this->columns, $this->where);
        if($arr == false || empty($arr))return false;
        $fn = $this->callBack;
        foreach ($arr as $var) {
            if (is_callable($fn)) {$var = $fn($var);}
            $out[] = array_values($var);
        }
        array_unshift($out, $this->titles);
        if (file_exists($filename))
            unlink($filename);
        require_once 'xls/xlsxwriter/xlsxwriter.class.php';
        $writer = new XLSXWriter();
        $writer->writeSheet($out);
        $writer->writeToFile($filename . '.xlsx');
        return true;
    }
    
    /**
     * Donwnload arquivo excel formato .xlsx . 
     * Exemplo de uso no controller:
     * $this->LoadResource('files/xls','xls');
        if($this->xls->downloadXlsx('carteira/corretora',DIR_FILES.'out') == false){
            Mensagem de Erro
            $this->display(LINK . "/index");
        }
         exit(0);
     * @param string $model - ex: usuario/login
     * @param string $filename - ex: DIR_FILES.'out'
     * @return boolean
     */
    public function downloadXlsx($model, $filename) {
        $status = $this->saveXlsx($model, $filename);
        if(!$status)return false;
        header('Content-Disposition: attachment; filename='.basename($filename).'.xlsx');
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Length: ".filesize($filename.'.xlsx'));
        readfile($filename.'.xlsx');
        unlink($filename.'.xlsx');
    }
    
    public function setTitles($titles){
        $this->titles = $titles;
        return $this;
    }

    public function setColumns($columns){
        $this->columns = $columns;
        return $this;
    }
    
    public function setWhere($where){
        $this->where = $where;
        return $this;
    }
    
    /**
     * Seta CallBack . Exemplo de uso:
     * $this->LoadResource('files/xls','xls');
     *  $this->xls->setCallBack($this->getCallback());
     * 
     * private function getCallback(){
     *   return function($row) {
     *       $row['coluna'] = '2131';
     *       return $row;
     *   };
     * }
     * 
     * @param function $callBack
     * @return \xlsResource
     */
    public function setCallBack($callBack){
        $this->callBack = $callBack;
        return $this;
    }
}