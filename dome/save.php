<?php
include_once __DIR__."/../vendor/autoload.php";
ini_set("display_errors", "on");
error_reporting(E_ALL);
LSYS\Config\File::dirs(array(
    __DIR__."/config",
));
\LSYS\FileSave\DI::set(function (){
    $di= new \LSYS\FileSave\DI();
    $di->filesave(new \LSYS\DI\ShareCallback(function($config=null){
        return $config;
    },function($config=null){
        $config=\LSYS\Config\DI::get()->config("filesave.fastdfs");
        $save=new \LSYS\FileSave\FastDFS($config);
        return $save;
    }));
    return $di;
});
  
$filesave=\LSYS\FileSave\DI::get()->filesave();
$string="a.png";
$file=$filesave->put($string,"test.png",0);
var_dump($file);
var_dump($filesave->remove($file));