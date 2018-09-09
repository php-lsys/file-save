<?php
namespace LSYS\FileSave;
/**
 * @method \LSYS\FileSave filesave($config=null)
 */
class DI extends \LSYS\DI{
    public static $config = 'filesave.local_disk';
    /**
     * @return static
     */
    public static function get(){
        $di=parent::get();
        !isset($di->filesave)&&$di->filesave(new \LSYS\DI\ShareCallback(function($config=null){
            return $config?$config:self::$config;
        },function($config=null){
            $config=\LSYS\Config\DI::get()->config($config?$config:self::$config);
            return new \LSYS\FileSave\LocalDisk($config);
        }));
        return $di;
    }
}