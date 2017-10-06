<?php
include "config.php";

spl_autoload_register(function($className){
    $file = "{$className}.php";
    if(file_exists($file))
        require_once ($file);
    elseif(file_exists("model/".$file))
        require_once ("model/".$file);    
    else
        throw new Exception("{$file} not found");
});

try{
    ini_set("soap.wsdl_cache_enabled", "0"); 
    $server = new SoapServer("http://192.168.0.15/~user15/soap/shop_soap/server/carservice1.wsdl");
    $server->setClass("SoapCarClass");
    $server->handle();

}catch(SoapFault $ex){
    file_put_contents('errors.txt', $err->getMessage(), FILE_APPEND); 
}catch(PDOException $err){
    file_put_contents('errors.txt', $err->getMessage(), FILE_APPEND); 
}catch(Exception $ex){
    file_put_contents('errors.txt', $err->getMessage(), FILE_APPEND); 
}


