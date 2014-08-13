<?php 

class videoUpload extends Upload{ 
    
    public function Prepare(&$arquivo){
        if(!$this->genThumbnails($arquivo)) return false;
        return true;
    }
    
    private function genThumbnails($arquivo){
        $video = escapeshellcmd($arquivo['tmp_name']);
        $cmd = "ffmpeg -i $video 2>&1";
        $second = 1;
        if (preg_match('/Duration: ((\d+):(\d+):(\d+))/s', `$cmd`, $time)) {
            $total  = ($time[2] * 3600) + ($time[3] * 60) + $time[4];
            $den    = ($total < 300)?1:5;
            $qtd    = ceil($total/60);
            if($qtd > 5) $qtd = 5; 
            $second = rand(1, (($total - 1)/$den));
        }
        
        $i = 0;
        $dir = $this->dir."/thumbnails/";
        $this->LoadResource("files/dir", 'dobj');
        if(!$this->dobj->create($this->dir, 'thumbnails')){
            $this->setMessages($this->dobj->getMessages());
            return false;
        }
        
        while ($i < $qtd){
            $n      = $i + 1;
            $image  = "$dir/$n.jpg";
            $cmd = "ffmpeg -i $video -deinterlace -an -ss $second -t 00:00:01 -r 1 -y -vcodec mjpeg -f mjpeg $image 2>&1";
            $do = `$cmd`;
            $i++;
        }
        return true;
    }
    
} 

?> 