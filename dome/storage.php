<?php
use LSYS\Config\File;
include_once __DIR__."/../vendor/autoload.php";
File::dirs(array(
    __DIR__."/config",
));
\LSYS\FileSave\DI::set(function (){
    $di= new \LSYS\FileSave\DI();
    $di->filesave(new \LSYS\DI\SingletonCallback(function(){
        $config=\LSYS\Config\DI::get()->config("filesave.local_disk");
        $save=new \LSYS\FileSave\LocalDisk($config);
        return $save;
    }));
        return $di;
});
    
$filesave=\LSYS\FileSave\DI::get()->filesave();
$string="a.png";
$file=$filesave->put($string,null,false);//默认会清除文件,测试先别删
//得到$file保存到数据库
//$filesave->remove($file);


exit;
//测试获取,非必须
\LSYS\FileGet\DI::set(function (){
    $di= new \LSYS\FileGet\DI();
    $di->fileget(new \LSYS\DI\SingletonCallback(function(){
        $config=\LSYS\Config\DI::get()->config("fileget.local_disk");
        $save=new \LSYS\FileGet\Disk\LocalDisk($config);
        return $save;
    }));
    return $di;
});

var_dump(\LSYS\FileGet\DI::get()->fileget()->url($file));



