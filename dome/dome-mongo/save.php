<?php
include_once __DIR__."/../vendor/autoload.php";
LSYS\Config\File::dirs(array(
    __DIR__."/config",
));
\LSYS\FileSave\DI::set(function (){
    $di= new \LSYS\FileSave\DI();
    $di->filesave(new \LSYS\DI\ShareCallback(function($config=null){
        return $config;
    },function($config=null){
        $config=\LSYS\Config\DI::get()->config("filesave.gridfs");
        $save=new \LSYS\FileSave\MongoDB($config);
        $save=new \LSYS\FileSave\GridFS($config);
        return $save;
    }));
    return $di;
});

$file=$save->put("a.png","test.png",0);
var_dump($file);
var_dump($save->remove($file));