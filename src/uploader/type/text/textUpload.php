<?php 

class textUpload extends Upload{ 
    
    public function Prepare(&$arquivo){
        print_r($arquivo);
        die(__CLASS__);
    }
    
} 

?> 