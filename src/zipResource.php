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
class zipResource extends zipClass{
    
    private $err = array();
    public function compactar($diretorio){
        $this->LoadResource('files/dir', 'dobj');
        $zipfile = "$diretorio.zip";
        getTrueDir($zipfile);
        if(!$this->openzip($zipfile, ZIPARCHIVE::CREATE)){return false;}
        $this->diretorio = $diretorio;
        getTrueDir($this->diretorio);
        $bool = $this->compactarRecursivo($diretorio);
        $this->closezip();
        if(!$bool){$this->dobj->removeFile($zipfile);}
        else{$this->setSuccessMessage("Arquivos compactados com sucesso!");}
        if(!empty($this->err)){$this->setAlertMessage($this->err);}
        return $bool;
    }
    
    private function compactarRecursivo($diretorio){
        $diretorio = $diretorio.DS;
        getTrueDir($diretorio);
        if($this->setFiles($diretorio) === false){return false;}
        if($this->setFolders($diretorio) === false){return false;}
        return true;
    }
    
    private function setFolders($dir){
        $pastas = $this->dobj->getPastas($dir);
        if(empty($pastas)){return true;}
        foreach($pastas as $pasta){
            if(trim($pasta) === "" || $pasta == "."|| $pasta == ".."){continue;}
            $diretorio = $dir.DS.$pasta;
            getTrueDir($diretorio);
            $dirname = str_replace($this->diretorio.DS, "", $diretorio);
            if(trim($dirname) != ""){
                $status = $this->zip->addEmptyDir($dirname);
                if($status !== TRUE){
                    $this->setErrorMessage("Erro ao criar o diretório $diretorio <br/>Motivo: " .$this->ZipStatusString($status));
                    return false;
                }
            }
            if($this->compactarRecursivo($diretorio) === false){return false;}
        }
        return true;
    }
    
    private function setFiles($dir){
        $files = $this->dobj->getArquivos($dir);
        if(empty($files)){return true;}
        $dirname = str_replace($this->diretorio.DS, "", $dir);
        //echo $dirname . "<br/>";
        foreach($files as $file){
            if(trim($file) === ""){continue;}
            $relativeFile = (trim($dirname) !== "")?$dirname.DS.$file:$file;
            if($this->zip->addFile($dir.DS.$file,$relativeFile) === false){
                $this->err[] = "Erro ao zipar o arquivo ".$dir.DS.$file;
            }
        }
        return true;
    }
}