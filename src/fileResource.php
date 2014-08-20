<?php
/* 
 * Class: PHPFile
 * Autor: Thompson Moreira Filgueiras
 * Contato: thom@dcc.ufmg.br
 */
class fileResource extends \classes\Interfaces\resource{

    static private $instance;
    private function __construct(){}

    public static function getInstanceOf(){
        $class_name = __CLASS__;
        if (!isset(self::$instance)) {
            self::$instance = new $class_name;
        }

        return self::$instance;
    }

    /**
     * Função: GetFileContent
     * Complexidade do algoritmo: O(1) - considerando que as funções do php são todas O(1) (isto nao eh verdade)
     * Parâmetros: $Documento -> nome do arquivo a ser aberto
     * Retorna: Conteudo de um arquivo de texto
     * Resumo: Abre um arquivo e retorna o seu conteudo
     */
    public function GetFileContent($Documento){
        getTrueDir($Documento);
        if(!file_exists($Documento)){
            throw new \classes\Exceptions\resourceException('fileResource',"O documento $Documento não existe!");
        }
        $file = @file_get_contents ($Documento);
        if($file === false){
            throw new \classes\Exceptions\resourceException('fileResource',"Erro ao abrir arquivo $Documento");
        }
        return $file;
    }
    
    public function dropFile($fileName){
        if(!is_file($fileName) && !is_link($fileName)){ return true; }
        if(@unlink($fileName) !== FALSE){ return true; }
        if(@chmod($fileName, 0777) === TRUE && @unlink($fileName) === TRUE){ return true;}
        if(!file_exists($fileName)){return true;}
        $last = error_get_last();
        if(!is_readable($fileName)){
             $this->setErrorMessage("Arquivo $fileName não pode ser lido (e nem apagado) - {$last['message']}");
        }
        else {$this->setErrorMessage("Erro ao apagar Arquivo $fileName - {$last['message']}");}
        return false;
    }
    
    public function savefile($filename, $conteudo, $chmod = 0755){
        $dir = dirname($filename);
        getTrueDir($dir);
        getTrueDir($filename);
        if(!is_dir($dir) && !$this->createDir($dir)) return false;
        if(file_put_contents($filename, $conteudo) === false){
            $this->setErrorMessage("Não foi possível criar o arquivo ($filename) ");
            return false;
        }
        if(@chmod($filename, $chmod) === FALSE){
            $this->setAlertMessage("Não foi possível dar a permissão $chmod para o arquivo!");
            return true;
        }
        $this->setSuccessMessage("Arquivo salvo corretamente!");
        return true;
    }
    
    public function append($filename, $conteudo){
        getTrueDir($filename);
        if(!file_exists($filename)){
            die('jajaja');
            $this->setErrorMessage("Arquivo $filename não existe!");
            return false;
        }
        
        $fp = fopen($filename, 'a+');
        if($fp === FALSE){
            die("dooo");
            $this->setErrorMessage("Não foi possível abrir o arquivo $filename");
            return false;
        }
        
        if(fwrite ($fp, $conteudo) === FALSE){
            die("oinc");
            $this->setErrorMessage("Não foi possível escrever no arquivo $filename");
            return false;
        }
        fclose($fp);
        return true;
    }
    
    private function createDir($dir){
        $this->LoadResource('files/dir', 'dir');
        $dir  = str_replace(DIR_BASIC, "", $dir);
        getTrueDir($dir);
        $temp = explode(DIRECTORY_SEPARATOR, $dir);
        $fname = DIR_BASIC;
        foreach($temp as $t){
            if($t == "") continue;
            if(!file_exists($fname . $t)){
                if(!$this->dir->create($fname, $t)){
                    $this->setErrorMessage("Diretório ($fname) não existe e não pode ser criado");
                    return false;
                }
            }
            //echo "$fname<br/>";
            $fname .= $t . DIRECTORY_SEPARATOR;

        }

        if(!file_exists(DIR_BASIC . $dir)){
            $this->setErrorMessage("Diretório ($dir) não existe e não pode ser criado");
            return false;
        }
        
        return true;
    }

    /*
     * Função: ShiftAndAproximado
     * Algoritmo original em C
     * Créditos: Nivio ziviani
     * Url: http://www2.dcc.ufmg.br/livros/algoritmos/cap8/codigo/c/8.1a8.6e8.8-pesquisacadeia.c
     * Complexidade do algoritmo: O(n * E) onde n é o tamanho do texto e E o número de erros
     *
     * Parâmetros: $texto     -> texto fonte, onde será feita a busca pelo padrao
     *             $Padrao    -> string que será procurada no texto
     *             $num_erros -> Número de letras que pode estar errada ao fazer a busca.
     *                           Caso numerros seje maior do que 10 por exemplo, a maioria das palavras irao casar
     *                           Um valor recomendado é entre 0 e 2, o google faz casamentos de tamanho 1
     * 
     * Retorna: True caso o padrao esteje presente, false caso não esteja
     *
     * Resumo: Compara um Texto com uma string Padrao com uma tolerancia num_erros na busca.
     * Cria uma máscara de bits onde marca a posição de cada ocorrência de uma letra no texto padrao
     * Atravéz de operacoes logicas, shift, and e or compara onde ocorreu casamento de strings
     *
     * Mais Informações: Projeto de algoritmos e implementações em c e pascal, nívio zivianni
     */
    public function ShiftAndAproximado($Texto, $Padrao, $num_erros = 1){
        //verificações
        if($Padrao == ""){
            echo "O padrão não pode ser uma string vazia! Erro!";
            return false;
        }
        if($Texto == ""){
            echo "O Texto não pode ser uma string vazia! Erro!";
            return false;
        }
        if($num_erros < 0 || $num_erros > 10){
            echo "O número de erros deve ser um número positivo entre 0 e 10";
            return false;
        }

        //recupera tamanho das strings
        $n = strlen($Texto);
        $m = strlen($Padrao);

        //define o maior numero de erros e o maior tamanho da mascara
        $MAXCHAR = 256;
        $NUMMAXERROS = 10;

        //inicializações
        $Masc = array();
        $R = array();

        //zera a máscara de bits e marca nesta as posicoes onde apareceram determinados caracteres
        for ($i = 0; $i < $MAXCHAR; $i++) $Masc[$i] = 0;
        for ($i = 1; $i <= $m; $i++) {$Masc[ord($Padrao[$i-1]) + 127] |= 1 << ($m - $i);}

        //inivializa r0 com o valor 0
        $R[0] = 0;

        //este valor será constante no resto do algoritmo
        $Ri = 1 << ($m - 1);

        //algoritmo propriamente dito
        for ($j = 1; $j <= $num_erros; $j++) $R[$j] = (1 << ($m - $j)) | $R[$j-1];
        for ($i = 0; $i < $n; $i++){
            $Rant = $R[0];
            $Rnovo = ((($Rant) >> 1) | $Ri) & $Masc[ord($Texto[$i]) + 127];
            $R[0] = $Rnovo;
            for ($j = 1; $j <= $num_erros; $j++){
                $Rnovo = ((($R[$j]) >> 1) & $Masc[ord($Texto[$i]) + 127]) | $Rant | ((($Rant | $Rnovo)) >> 1);
                $Rant = $R[$j];
                $R[$j] = $Rnovo | $Ri;
            }

            //se o ultimo bit é 1, então houve casamento!
            if (($Rnovo & 1) != 0) return true;
            
        }
        return false;
    }
    
    /**
     * Simula o wget sem salvar dados na memória
     * @param string $file_source
     * @param string $file_target
     * @return boolean true se a operação for concluída com sucessso
     */
    public function download($file_source, $file_target) {
        $rh = fopen($file_source, 'rb');
        if ($rh===false) {
            $this->setErrorMessage("Não foi possível abrir o arquivo $file_source");
            return false;
        }
        
        $wh = fopen($file_target, 'wb');
        if ($wh===false) {
            $this->setErrorMessage("Não foi possível abrir o arquivo $file_target");
            return false;
        }
        
        while (!feof($rh)) {
            if (fwrite($wh, fread($rh, 1024)) === FALSE) {
                $this->setErrorMessage("Não foi possível escrever no arquivo $file_target");
                return false;
            }
        }
        fclose($rh);
        fclose($wh);
        return true;
    }

    /*
     * Função: FindTextInPDF
     * Créditos: Thompson Moreira
     *
     * Parâmetros: $Documento -> Arquivo em formato pdf que contem o texto a ser procurado
     *             $Padrao    -> string que será procurada no texto
     *
     * Retorna: True caso o padrao esteje presente, false caso não esteja
     *
     * Resumo: Decodifica um arquivo pdf usando a classe PDF2TXT e procura no texto transformado
     * o padrao, usando o shift and aproximado
     *
     * Mais Informações: Projeto de algoritmos e implementações em c e pascal, nívio zivianni
     */
    static public function FindTextInPDF($Documento, $Padrao){

        require_once 'Pdf2text.php';
        $pdf = new PDF2Text();
        $Texto = $pdf->decodePDF($Documento);

        $file_obj = new PHPFile();
        return($file_obj->ShiftAndAproximado($Texto, $Padrao));
    }

    /*
     *  Conta as palavras repetidas em um arquivo
     *  Recebe o nome do arquivo ou uma string
     *  Recebe o numero maximo de palavras a ser devolvido, se $numPalavras = 0,
     *           retorna todas as palavras que ocorreram pelo menos 2 vezes
     *  Recebe um flag dizendo se $arquivo é arquivo ou string
     */
    public function GetPalavrasMaisRepetidas($arquivo, $numPalavras = 0, $isFile = 1){
        
        //verifica se e ou nao um arquivo
        if($isFile) $f_contents = preg_split ("/[\s+/", implode ("", file ($arquivo)));
        else        $f_contents = $arquivo;
        

        //lista todas as palavras repetidas
        foreach ($f_content as $palavra){
            $ar[$palavra]++;
            $ArrayPalavras[] = $palavra;
        }

        //retorna o array caso queira todas as repeticoes
        if($numPalavras == 0) return $ArrayPalavras;

        //ordena o array
        unset($ArrayPalavras);
        arsort($ar);

        //lista todas as palavras repetidas
        foreach ($ar as $palavra => $count){

            //verifica se já encontrou o numero minimo de palavras
            $ArrayPalavras[] = $palavra;
            $num++;
            if($num > $numPalavras) break;
            
        }
        return $ArrayPalavras;
    }
    
    public function display_filesize($filesize){
        if(is_numeric($filesize)){
            $decr = 1024; $step = 0;
            $prefix = array('Byte','KB','MB','GB','TB','PB');

            while(($filesize / $decr) > 0.9){
                $filesize = $filesize / $decr;
                $step++;
            } 
            return round($filesize,2).' '.$prefix[$step];
        }else return 'NaN';
    }
    
    /**
     * Retorna a extensão do arquivo de acordo com o mime type informado. Retorna false caso não encontre a extensão
     * do mime tipo informado.
     * @param string $mime_type
     * @return string/boolean
     */
     public function getExtension($mime_type) {
        $array = $this->extensions();
        foreach ($array as $ext => $mime) {
            if ($mime == $mime_type)
                return $ext;
        }
        return false;
    }

    private function extensions(){
         return array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
         );
     }
    
    
}
