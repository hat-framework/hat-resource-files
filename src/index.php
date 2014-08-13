<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data"> 
Source File <input type="file" name="fupload" /> <input type="submit" name="upload" value="convert" /> 
</form> 
<hr /> 

<?php 

if(isset($_POST['upload'])) { 
    require_once '../../init.php';
    require_once 'uploader/type/video/videoConverter.php';
    $uploader = new VideoConverter(); 

    $uploaded_file = $_FILES['fupload'];
    $uploader->setVideoFile($uploaded_file);
    //$uploader->video2mobile();
    //$uploader->video2flash(); 
    $uploader->video2html5(); 

    /*require_once 'uploader/Uploader.php';
    $uploader = new Uploader();
    $uploader->Upload($_FILES['fupload']);
    */
    print_r($uploader->getMessages());
} 


?>