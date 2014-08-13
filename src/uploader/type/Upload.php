<?php

use classes\Classes\Object;
abstract class Upload extends classes\Classes\Object{
    
    protected $size = 300000;
    protected $dir  = "";
    public function setDir($diretorio){
        $this->dir = $diretorio;
    }
    
    public function set_max_size($max_size){
        $this->size = $max_size;
    }
    
    abstract public function Prepare(&$arquivo);
    public final function Save($arquivo){

        if(!$this->ValidaArquivo($arquivo)) return false;
        if(!$this->Prepare($arquivo)) return false;
        
        $exp  = explode(".", $arquivo['name']);
        $ext  = "." . end($exp);
        unset($exp[count($exp) -1]);
        $nome = implode(".", $exp);
        
        // Caminho de onde a imagem ficará
        $i         = 0;
        $diretorio = dirname(__FILE__)."/up/".$nome.$ext;
        while(file_exists($diretorio)){
            $i++;
            $diretorio = dirname(__FILE__)."/up/".$nome."($i)".$ext;
        }
        
        // Faz o upload da imagem
        if(!move_uploaded_file($arquivo["tmp_name"], $diretorio)){
            $this->setErrorMessage("O diretório $diretorio não tem permissão de escrita.");
            return false;
        }
        $this->setSuccessMessage("Arquivo enviado com sucesso!");
        return true;
    }
    
    private function ValidaArquivo($arquivo){
    
        if(empty($arquivo)){
            $this->setErrorMessage("Arquivo não pode ser vazio.");
            return false;
        }
        
        if($arquivo['error'] == 4){
            $this->setErrorMessage("Arquivo selecionado contém erros");
            return false;
        }
        
        return true;
    }
    
}
?>
