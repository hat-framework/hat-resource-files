<?php

use classes\Classes\Object;
class VideoConverter extends classes\Classes\Object{
    
    private $type           = '';
    private $ext            = '';
    private $allow_conv_ext = array('flv', '3gp', 'mp4');
    private $file = array();
    public function setVideoFile($file){
        $exp = explode("/", $file['type']);
        $this->type = array_shift($exp);
        $this->ext = @end(explode(".", $file['name']));
        if($this->type != 'video'){
            $this->setErrorMessage("Arquivo enviado não é um vídeo!");
            return false;
        }
        $this->file = $file;
        return true;
    }
    
    public function video2mobile(){
        return $this->videoConvert('3gp');
    }
    
    public function video2flash(){
        return $this->videoConvert('flv');
    }
    
    public function video2html5(){
        return $this->videoConvert('mp4');
    }
    
    private function videoConvert($ext){
        
        if(!in_array($ext, $this->allow_conv_ext)){
            $this->setErrorMessage("Não é possível converter para arquivos $ext.");
            return false;
        }
        
        if(empty($this->file)){
            $this->setErrorMessage("O arquivo de vídeo não foi setado na classe videoConverter!");
            return false;
        }

        $name    = str_replace(".$this->ext", ".$ext", $this->file["name"]);
        $outfile = $this->convert($this->file, $name, $ext);
        if($outfile === false) return false;
        $this->download($name, $outfile);
    }
    
    private function convert($file, $name, $ext){
        $outf  = $file["tmp_name"];
        if($this->ext != $ext){
            $outf = $name;
            "ffmpeg -i path/to/input.mov -vcodec videocodec -acodec audiocodec path/to/output.flv 1> block.txt 2>&1";
            $cmd  = ($ext != '3gp')?
             "ffmpeg -i {$file["tmp_name"]} -sameq {$outf} > up.txt 2>&1":
             "ffmpeg -i {$file["tmp_name"]} -s qcif -vcodec h263 -acodec aac -strict experimental -ac 1 -ar 8000 -r 25 -ab 32 -y {$outf} > up.txt 2>&";
            shell_exec($cmd);
        }
        return "/tmp/$outf";
    }
    
    public function download($filename, $outfile){
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($outfile));
        ob_clean();
        flush();
        readfile($outfile);
        unlink($outfile);
        exit;
    }
    
    private function progress(){
        $content = @file_get_contents('../block.txt');
        if($content){
            //get duration of source
            preg_match("/Duration: (.*?), start:/", $content, $matches);

            $rawDuration = $matches[1];

            //rawDuration is in 00:00:00.00 format. This converts it to seconds.
            $ar = array_reverse(explode(":", $rawDuration));
            $duration = floatval($ar[0]);
            if (!empty($ar[1])) $duration += intval($ar[1]) * 60;
            if (!empty($ar[2])) $duration += intval($ar[2]) * 60 * 60;

            //get the time in the file that is already encoded
            preg_match_all("/time=(.*?) bitrate/", $content, $matches);

            $rawTime = array_pop($matches);

            //this is needed if there is more than one match
            if (is_array($rawTime)){$rawTime = array_pop($rawTime);}

            //rawTime is in 00:00:00.00 format. This converts it to seconds.
            $ar = array_reverse(explode(":", $rawTime));
            $time = floatval($ar[0]);
            if (!empty($ar[1])) $time += intval($ar[1]) * 60;
            if (!empty($ar[2])) $time += intval($ar[2]) * 60 * 60;

            //calculate the progress
            $progress = round(($time/$duration) * 100);

            echo "Duration: " . $duration . "<br>";
            echo "Current Time: " . $time . "<br>";
            echo "Progress: " . $progress . "%";

        }
    }
    
}

?>