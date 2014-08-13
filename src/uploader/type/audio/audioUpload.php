<?php 

class audioUpload extends Upload{ 
    
    public function Prepare(&$arquivo){
        print_r($arquivo);
        die(__CLASS__);
    }
    
} 

?> 