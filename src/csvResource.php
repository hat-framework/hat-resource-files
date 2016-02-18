<?php

require_once 'csv/Csv.php';
class csvResource extends \classes\Interfaces\resource{

    private static $instance = NULL;
    public static function getInstanceOf(){
        $class_name = __CLASS__;
        if (!is_object(self::$instance))self::$instance = new $class_name();
        return self::$instance;
    }
    
    public function getCsvResource($filename, $separator = ";", $colum_key = "", $hasHeader = true, $reversed = false){
        return $this->setSeparator($separator)
                    ->setColumKey($colum_key)
                    ->setHeader($hasHeader)
                    ->setReversed($reversed)
                    ->execute($filename);
    }
    
    private $separator = ";";
    public function setSeparator($separator){
        $this->separator = $separator;
        return $this;
    }
    
    private $colkey = "";
    public function setColumKey($colkey){
        $this->colkey = $colkey;
        return $this;
    }
    
    private $header = true;
    public function setHeader($header){
        $this->header = $header;
        return $this;
    }
    
    private $reversed = true;
    public function setReversed($reversed){
        $this->reversed = $reversed;
        return $this;
    }
    
    public function execute($filename){
        if(!file_exists($filename)){
            $this->LoadResource('files/file', 'fobj');
            if(!$this->fobj->savefile($filename, "")){
                throw new \classes\Exceptions\resourceException("O arquivo $filename não existe e não pode ser criado");
            }
        }
        $obj = new Csv();
        $obj->setSeparator($this->separator)
            ->setColumKey($this->colkey)
            ->setHeader($this->header)
            ->setReversed($this->reversed)
            ->execute($filename);
        return $obj;
    }
    
    public function exportCSV($rows){
        $headings = array_keys(end($rows));
        # Ensure that we have data to be able to export the CSV
        if ((empty($headings)) AND (empty($rows))){return;}
        # modify the name somewhat
        $name = "export.csv";

        # Set the headers we need for this to work
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $name);

        # Start the ouput
        $output = fopen('php://output', 'w');

        # Create the headers
        fputcsv($output, $headings);

        # Then loop through the rows
        foreach($rows as $row)
        {
            # Add the rows to the body
            fputcsv($output, $row);
        }

        # Exit to close the stream off
        exit();
    }
    
    public function array2csv($array, $filename = ""){
       if (count($array) == 0) {
         return false;
       }
       ob_start();
       $df = fopen("php://output", 'w');
       fputcsv($df, array_keys(reset($array)));
       foreach ($array as $row) {
          fputcsv($df, $row);
       }
       fclose($df);
       $result = ob_get_clean();
       if($filename == ""){$this->download_send_headers($filename, $result);}
       return $result;
    }
    
    
}