<?php
/**
 * Classe para manipulação de diretórios
 * Exclusivo para uso em sistemas Unix Like
 * Sem vontade para portar para plataforma MS
 * @author André Gustavo Espeiorin
 * @version 1.0
 * @package X
 */
require_once dirname(__FILE__). '/defines/zipClass.php';
class unzipResource extends zipClass{
    
    private $diretorio = '';
    private $zipFiles = array('zip','ZIP','WFL','itr','dfp');    
    public function __construct() {
        parent::__construct();
        $this->LoadResource('files/dir', 'dobj');
        $this->LoadResource('files/file', 'fobj');
    }
    
    private function setDiretorio($diretorio){
        $diretorio .= DS;
        getTrueDir($diretorio);
        $this->diretorio = $diretorio;
    }
    
    public function unzipFolder($diretorio) {
        $this->setDiretorio($diretorio);
        $zipFiles = implode(",",$this->zipFiles);
        $arquivos = (array) glob("$this->diretorio*.{{$zipFiles}}", GLOB_BRACE);
        if(empty($arquivos)){return true;}
        $done = 0;
        if(empty($arquivos)){$done = 1;}
        foreach ($arquivos as $arquivo) {
            if($this->extractFile($arquivo) === false){continue;}
            $done++;
        }
        return ($done > 0);
    }
    
    public function extractFile($arquivo, $dir = ""){
        getTrueDir($arquivo);
        if(!$this->openzip($arquivo)){return false;}
        $bool = $this->extract($arquivo);
        $this->closezip(true);
        if($bool){
            $localpasta = $this->getLocalPasta($arquivo);
            return $this->unzipFolder($localpasta);
        }
        return $bool;
    }

    private function extract($arquivo){
        $localpasta = $this->getLocalPasta($arquivo);
        if (file_exists($localpasta)) { return true;}
        if(!$this->dobj->create($localpasta, '')){
            $this->setMessages($this->dobj->getMessages());
            return false;
        }
        $status = $this->zip->extractTo($localpasta);
        if($status === true){return true;}
        $this->setErrorMessage($this->ZipStatusString($status));
        return false;
    }
    
    private function getLocalPasta($arquivo){
        if($this->diretorio !== ""){
            $e = explode(DS, $arquivo);
            $nomearquivo = end($e);
            $pasta       = explode(".", $nomearquivo);
            $localpasta      = $this->diretorio.DS.$pasta[0];
        }else{
            $e               = explode(".", $arquivo);
            $localpasta      = $e[0];
        }
        getTrueDir($localpasta);
        return $localpasta;
    }

}