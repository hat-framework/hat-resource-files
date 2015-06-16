<?php

class zipClass extends \classes\Interfaces\resource{
    
    protected $zip = null;
    protected $zipFileName = '';
    static private $instance;
    public static function getInstanceOf(){
        $class_name = self::whoAmI();
        if (!isset(self::$instance[$class_name])){self::$instance[$class_name] = new $class_name;}
        return self::$instance[$class_name];
    }
    
    public function __construct(){
        $this->zip = new ZipArchive();
    }

    protected function openzip($zip, $mode = null){
        $zip = $this->getZipFileName($zip);
        $status = $this->zip->open($zip, $mode);
        $this->zipFileName = $zip;
        if($status === TRUE){return true;}
        $this->setErrorMessage("Erro ao abrir o arquivo zip ($zip): " .$this->ZipStatusString($status));
        return false;
    }
    
    protected function getZipFileName($zip){
        getTrueDir($zip);
        $e = explode(DS, $zip);
        if(end($e) === ".zip"){
            array_pop($e);
            $zip = implode(DS, $e).".zip";
            getTrueDir($zip);
        }
        return $zip;
    }
    
    protected function closezip($dropfiles = false){
        $status = $this->zip->close();
        if($status !== true){
            $motivo = $this->ZipStatusString($status);
            $this->setAlertMessage("Não foi possível fechar o arquivo ($this->zipFileName). Motivo: $motivo");
            return false;
        }
        if($dropfiles && $this->zipFileName !== "" && $this->fobj->dropFile($this->zipFileName) === false){
            $this->setMessages($this->fobj->getMessages(true));
        }
        return true;
    }
    
     /**
    * Check if the file is encrypted
    * 
    * Notice: if file doesn't exists or cannot be opened, function
    * also return false.
    * 
    * @param string $pathToArchive
    * @return boolean return true if file is encrypted
    */
    public function isEncryptedZip( $pathToArchive ) {
        $fp = @fopen( $pathToArchive, 'r' );
        $encrypted = false;
        if ( $fp && fseek( $fp, 6 ) == 0 ) {
            $string = fread( $fp, 2 );
            if ( false !== $string ) {
                $data = unpack("vgeneral", $string);
                $encrypted = $data[ 'general' ] & 0x01 ? true : false;
            }
            fclose( $fp );
        }
        return $encrypted;
    }
    
    public function ZipStatusString($status){
        switch( (int) $status ) {
            case ZipArchive::ER_OK           : return 'Sem erro';
            case ZipArchive::ER_MULTIDISK    : return 'Não há suporte para arquivos Zip Multi-disco';
            case ZipArchive::ER_RENAME       : return 'Erro ao renomear o arquivo temporário';
            case ZipArchive::ER_CLOSE        : return 'Erro ao fechar o arquivo zip';
            case ZipArchive::ER_SEEK         : return 'Erro na busca';
            case ZipArchive::ER_READ         : return 'Erro na leitura';
            case ZipArchive::ER_WRITE        : return 'Erro na escrita';
            case ZipArchive::ER_CRC          : return 'Erro no checksum';
            case ZipArchive::ER_ZIPCLOSED    : return 'O arquivo zip foi fechado';
            case ZipArchive::ER_NOENT        : return 'Arquivo não existe';
            case ZipArchive::ER_EXISTS       : return 'Arquivo já existe';
            case ZipArchive::ER_OPEN         : return 'Não é possível abrir o arquivo';
            case ZipArchive::ER_TMPOPEN      : return 'Erro ao criar um arquivo temporário';
            case ZipArchive::ER_ZLIB         : return 'Erro na biblioteca zlib';
            case ZipArchive::ER_MEMORY       : return 'Erro ao alocar espaço em disco';
            case ZipArchive::ER_CHANGED      : return 'A entrada foi modificada';
            case ZipArchive::ER_COMPNOTSUPP  : return 'Método de compressão não suportado';
            case ZipArchive::ER_EOF          : return 'Fim prematuro do arquivo (EOF)';
            case ZipArchive::ER_INVAL        : return 'Argumento inválido';
            case ZipArchive::ER_NOZIP        : return 'O Arquivo enviado não é um arquivo zip (descompactável)';
            case ZipArchive::ER_INTERNAL     : return 'Erro Interno';
            case ZipArchive::ER_INCONS       : return 'Arquivo zip Inconsistente';
            case ZipArchive::ER_REMOVE       : return 'Não foi possível remover o arquivo';
            case ZipArchive::ER_DELETED      : return 'Entrada foi deletada';

            default: return sprintf('Status Desconhecido %s', $status );
        }
    }
}