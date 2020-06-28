<?php
namespace LSYS;
interface FileSave{
    /**
     * 保存文件
     * @param string $filepath
     * @param string
     */
    public function put(?string $filepath,?string $filename=null,bool $clear=true);
    /**
     * 移除文件[业务逻辑注意:这个路径不能直接用用户传入的数据,注意提前检查和过滤]
     * @param string $filename
     * @return boolean
     */
    public function remove(?string $filename):bool;
}