<?php

class curlResource extends \classes\Interfaces\resource{   
    
    public function __construct(){
        parent::__contruct();
        $this->LoadResource('files/dir', 'dobj');
    }
    
    public function downloadFile($downloadUrl, $filename){        
        
        $dir = dirname($filename);
        if(!file_exists($dir) || !is_dir($dir)){
            $this->dobj->create($dir, "");
        }
        
        $fp = @fopen ($filename, 'w+');//This is the file where we save the zip file
        if($fp === false){
            return $this->appendErrorMessage("erro ao abrir o arquivo $filename");
        }
        
        $ch = curl_init($downloadUrl);
        if($ch === false){
            return $this->appendErrorMessage("Erro ao acessar url $downloadUrl. Detalhes:".curl_error($ch));
        }
        
        //if(!curl_setopt($ch, CURLOPT_TIMEOUT       , $timeout)){}
        if(!curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false)){}
        if(!curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false)){}
        if(!curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true)){}
        if(!curl_setopt($ch, CURLOPT_RETURNTRANSFER, true)){}
        if(!curl_setopt($ch, CURLOPT_BINARYTRANSFER, true)){}
        //if($HTTP_REFERER){if(!curl_setopt($ch, CURLOPT_REFERER, $_SERVER['HTTP_REFERER'])){}}
        if(!curl_setopt($ch, CURLOPT_FILE          , $fp)){} // write curl response to file
        
        $ex = curl_exec($ch);
        if($ex === false){return $this->appendErrorMessage("Erro ao pegar a resposta da url $downloadUrl. Detalhes: ".curl_error($ch));} 
        curl_close($ch);
        fclose($fp);
        return true;
    }
}