<?php
/**
 * Classe para manipulação de diretórios
 * Exclusivo para uso em sistemas Unix Like
 * Sem vontade para portar para plataforma MS
 * @author André Gustavo Espeiorin
 * @version 1.0
 * @package X
 */
class dirResource extends \classes\Interfaces\resource{
    
	/**
	 * Metodo Responsavel pela criação de diretórios
	 * @param $location - onde a pasta deve ser criada
	 * @param $name - nome da mesma, evite o uso de caracteres especiais e espaços em branco
	 * @param $chmod - permissão para o acesso a nova pasta
	 * @param $recursive - criar recursivamente?
	 * @return <string> caminho da pasta criada
	 */
        private $error;
        private $pastas = array();
        private $arquivos = array();
        private $path;

        static private $instance;

        private function __construct(){
            $this->LoadResource('files/file', 'fobj');
        }

        public static function getInstanceOf(){
            $class_name = __CLASS__;
            if (!isset(self::$instance))
                self::$instance = new $class_name;
            return self::$instance;
        }

        public function getPath(){
            return $this->path;
        }

	public function create($location, $name, $chmod=0777, $recursive=true){

                //se é arquivo
                $loc  = "$location$name";
                $path = str_replace(realpath(DIR_BASIC).DS, '', $loc);
		$path = str_replace(array("//", "/", '\\'),DS ,DIR_BASIC . $path);

                //se o diretorio ja existe
		if(is_dir($path) && file_exists($path)){
                    //echo $path.'<br/>';
                    $this->setSuccessMessage("A pasta $loc já existe");
                    //echo("A pasta $name já existe");
                    return true;
		}
                
                //se não criar o diretorio
                //echo "$path<br/>";
		if(!@mkdir( $path, $chmod, $recursive ) ){
                    //se não tem permissão de escrita
                    if(!is_writable($path)){
                        $this->setErrorMessage("Não foi possível criar a pasta ($loc).
                            Não existe permissão de escrita no diretório $path.
                            Consulte o administrador para mais detalhes. ");
                        return false;
                    }
                    //se não tem permissão de leitura
                    if(!is_readable($path)){
                        $this->setErrorMessage("Não foi possível criar a pasta, 
                                Não existe permissão de leitura no diretório $path.");
                        return false;
                    }
		}
                else chmod( $path, $chmod );
                $this->path = $path . '/';
                return true;
	}

	/**
	 * Método criado para apagar um arquivo unicamente, e não um diretório
	 * Pode apagar arquivos e links simbólicos
	 * @param $fileName - Nome do arquivo
	 * @return <boolean>
	 */
	public function removeFile($fileName){
            getTrueDir($fileName);
            if(!file_exists($fileName)){
                $this->setAlertMessage("O Arquivo '$fileName' não existe");
                return true;
            }
            if(@is_dir($fileName) === TRUE){
                return $this->remove($fileName);
            }
            if(!$this->fobj->dropFile($fileName)) {
                $this->setMessages($this->fobj->getMessages());
                return false;
            }
            $this->setSuccessMessage("Arquivo $fileName apagado com sucesso!");
            return true;
	}
        
	/**
	 * Método responável por remover um diretório, é capaz de remover
	 * um diretório completo, incluindo subdiretórios e arquivos por meio
	 * da recursividade.
	 * @param $dirname - nome do diretório como seu caminho completo no Sistema de Arquvios
	 * @return <boolean>
	 */
	public function remove($dirname){
            
            // Checa se existe o diretório
            $path = $dirname;
            if (!file_exists($path)) {
               $this->setErrorMessage("Erro ao apagar pasta $path! Pasta não Existe.");
               return false;
            }

            /* Caso encontre um arquivo em meio a recursividade, ele o removerá
             * tratando ele como um arquivo e não diretório*/
            if (is_file($path) || is_link($path)) return $this->removeFile($dirname);
            

            /* Abre o diretório chamado como um objeto dir
             * Faz a iteração no mesmo, lendo seu interior
             * e chamando recursivamente o mesmo até eliminar
             * todo o conteúdo do diretório
             * @var <object> diretório */
            $itens = scandir($path);
            foreach($itens as $entry){
                if ($entry === '.' || $entry === '..') continue;
                $bool = true;
                if(!$this->remove( $dirname . DIRECTORY_SEPARATOR . $entry )) {$bool = false;}
                if(!$bool) return $bool;
            }

            /*
             * verifica se o diretório a ser apagado tem permissão de escrita, 
             * se não tiver tenta setar tal permissão antes de apagá-lo
             */
            if(is_writable($dirname) === false){
                @chmod( $dirname, 0777 );
            }
            
            // Finalmente remove o diretório
            $var = @rmdir($dirname);
            if($var === false){
                $this->setErrorMessage("erro ao apagar Diretório $dirname!");
                return false;
            }
            $this->setSuccessMessage("Diretório $dirname apagado com sucesso!");
            return true;
	}

	/**
	 * Este método é utilizado na necessidade de renomear um diretório
	 * Utiliza recursividade para sub-diretórios e funcionou muito
	 * bem em todos os testes
	 * @param $path - Caminho até a localização do diretório a ser renomeado
	 * @param $oldDir - Nome do diretório a ser renomeado
	 * @param $newDir - Novo Nome para o diretório
	 * @return <boolean>
	 */
	public function rename($path, $oldDir, $newDir){
            
            // Checa se o diretório existe
            if (!file_exists($path . $oldDir)) {return false;}
            $new = $this->create($path, $newDir);

            /**
             * Abre o velho diretório e itera ele, transferindo todos os arquivos para o novo
             * Os subdiretórios são transferidos do mesmo modo, por meio de recursividade
             * @var <object> dir
             */
            
            $dir = dir($path . $oldDir);
            while (false !== $entry = $dir->read()){
                
                // Pula os Ponteiros
                if ($entry == '.' || $entry == '..') continue;
                
                // Se o conteudo for um diretório, recursividade!
                if( is_dir($new . $entry) )
                        $this->rename($path . $oldDir, $entry, $path . $new . $entry );
                
                // Aqui é feita a transferencia de arquivos
                rename( $path . $oldDir . '/' . $entry, $new . $entry );
            }
            // Fecha o objeto
            $dir->close();
            
            // Remove o diretório antigo
            $this->remove( $path . $oldDir );
            $this->setSuccessMessage("Diretorio renomeado com sucesso!");
            return true;
	}

        public function RenameFile($path, $oldname, $newname){
            
              if(!file_exists($path . $oldname)){
                  $this->setErrorMessage("Arquivo $oldname não existe!");
                  return false;
              }
              
              if(!rename($path . $oldname, $path . $newname)){
                $this->setErrorMessage("Arquivo não pode ser renomeado!");
                return false;
              }

              $this->setSuccessMessage("Arquivo renomeado com sucesso!");
              return true;
        }

        public function FileExists($dir, $arquivo){
            $ponteiro  = opendir($dir);
            while ($nome_itens = readdir($ponteiro)){
                if($nome_itens == $arquivo) return true;
            }
            return false;
        }

        private $blacklist = array("..", ".", ".DS_Store");
        private function findFiles($diretorio){
            
            //apaga os arquivos e pastas, se ja foram setados
            getTrueDir($diretorio);
            $this->setAlertMessage("");
            $this->arquivos = $this->pastas = array();
            
            // abre o diretório
            if(!file_exists($diretorio)) {
                $this->setAlertMessage("diretorio ($diretorio) não existe!");
                return array();
            }
            //echo "($diretorio))<br/>";
            
            $ponteiro  = opendir($diretorio);
            
            // monta os vetores com os itens encontrados na pasta
            while ($listar = readdir($ponteiro)){
                if(in_array($listar, $this->blacklist)) continue;
                //echo "$diretorio/$listar<br/>";
                if    (is_dir ($diretorio . "/".$listar)) $this->pastas[]   = $listar;
                elseif(is_file($diretorio . "/".$listar)) $this->arquivos[] = $listar;
            }
        }

        public function getPastas($diretorio = ""){
            if($diretorio != "") $this->findFiles($diretorio);
            return $this->pastas;
        }

        public function getArquivos($diretorio = ""){
            if($diretorio != "") $this->findFiles($diretorio);
            return $this->arquivos;
        }
        
        public function getDirectoryTree(&$tree, $diretorio = ""){           
            $files   = $this->getArquivos($diretorio);
            foreach($files as $f){
                $tree[] = $f;
            }
            $folders = $this->getPastas();
            foreach($folders as $foldr){
                if(!isset($tree[$foldr])){
                    $tree[$foldr] = array();
                }
                $this->getDirectoryTree($tree[$foldr], $diretorio.DS.$foldr);
            }
        }
        
        public function getDirectoryTreeFolders(&$tree, $current_folder = '', $diretorio = ""){
            getTrueDir($diretorio);
            if($current_folder !== '' && $current_folder == $diretorio){
                $files   = $this->getArquivos($diretorio);
                foreach($files as $f){
                    $tree[] = $f;
                }
            }
            
            $folders = $this->getPastas($diretorio);
            foreach($folders as $foldr){
                if(!isset($tree[$foldr])){
                    $tree[$foldr] = array();
                }
                $this->getDirectoryTreeFolders($tree[$foldr], $current_folder, $diretorio.DS.$foldr);
            }
        }
        
        public function getFilesAndFolders($diretorio = ""){
            $this->findFiles($diretorio);
            return array_merge_recursive($this->pastas, $this->arquivos);
        }

        public function CopyDir($dir_src,$dir_dst){
            
            //se nao existe diretorio, cria o mesmo
            if (!is_dir($dir_dst)){
                $arr = explode("/", $dir_dst);
                $name = array_pop($arr);
                $location = implode("/", $arr);
                $this->create($location, $name);
            }

            /* @var $handle <type> */
            if($handle = opendir($dir_src)){
                
                while (false !== ($file = readdir($handle))){
                    
                    if ($file != "." && $file != ".."){
                        if (is_dir($dir_src.'/'.$file)){
                            if (!is_dir($dir_dst.'/'.$file)) mkdir($dir_dst.'/'.$file);
                            CopyDir($dir_src.'/'.$file,$dir_dst.'/'.$file);
                        }
                        else copy($dir_src.'/'.$file,$dir_dst.'/'.$file);
                    }
                }
                closedir($handle);
            }
            return true;
        }
}