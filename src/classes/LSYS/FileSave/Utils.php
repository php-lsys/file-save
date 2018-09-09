<?php
namespace LSYS\FileSave;
use LSYS\Exception;
trait Utils{
    protected function _check_dir($safe_dir,&$filepath){
        if (empty($safe_dir))return;
        $filepath=realpath($filepath);
        if(!$filepath)return ;
        $safe=false;
        foreach ($safe_dir as $v){
            $v=realpath($v);
            if (strncmp($filepath, $v,strlen($v))==0){
                $safe=true;
                break;
            }
        }
        if(!$safe)throw new Exception(__("file can't access[:path]",array("path"=>$filepath)));
    }
}