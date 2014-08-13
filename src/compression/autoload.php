<?php
spl_autoload_register(function ($nomeClasse) {
    $file = dirname(__FILE__) .DS."$nomeClasse.php";
    if (file_exists($file)) {
        require_once($file);
        return;
    }
});
