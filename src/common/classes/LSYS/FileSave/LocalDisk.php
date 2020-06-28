<?php
namespace LSYS\FileSave;
use function LSYS\FileSave\__;
use LSYS\FileSave;
use LSYS\Config;
use LSYS\Exception;
class LocalDisk implements FileSave{
    use Utils;
    protected $_config;
    public function __construct(Config $config){
        $this->_config=$config;
	}
	public function put(?string $filepath,?string $filename=null,bool $clear=true){
	    $this->_checkDir($this->_config->exist("safe_dir",[]),$filepath);
	    if (!is_file($filepath))return null;
	    $savedir=rtrim($this->_config->get("dir"),'\\/')."/";
	    $sdir=$this->_config->get("space","")."/";
	    $fdir=date("Y-m-d")."/";
	    if ($filename==null){
	        $ext=pathinfo($filepath, PATHINFO_EXTENSION);
	    }else{
	        $ext=pathinfo($filename, PATHINFO_EXTENSION);
	    }
	    $_filename=uniqid().".".$ext;
	    $this->_makeDir($savedir.$sdir.$fdir);
	    $_filepath=$savedir.$sdir.$fdir.$_filename;
	    if ($clear)$status=@rename($filepath,$_filepath);
	    else $status=@copy($filepath,$_filepath);
	    if(!$status)throw new Exception(__(":type file to :dir is fail",array(":type"=>$clear?"move":"copy",":dir"=>$_filepath)));
	    return $sdir.$fdir.$_filename;
	}
	public function remove(?string $filename):bool{
	    if (empty($filename))return null;
	    $savedir=rtrim($this->_config->get("dir"),'\\/')."/";
	    $filepath=$savedir.$filename;
	    $this->_checkDir($this->_config->exist("safe_dir",[]),$filepath);
	    if (!is_file($filepath))return true;
	    return !!@unlink($filepath);
	}
	/**
	 * 创建目录
	 * @param string $path
	 * @throws Exception
	 */
	protected function _makeDir(?string $dir){
	    $is_linux=false;
	    $dir=rtrim($dir,"\\/")."/";
	    if(substr($dir, 0,1)=='/')$is_linux=true;
	    $dir=explode("/",$dir);
	    if(empty($dir)) return ;
	    $t_dir='';
	    $one=true;
	    $sdir=ini_get("open_basedir");
	    if ($sdir){
	        $sdir=explode(":", $sdir);
	        $sdir=array_map('realpath', $sdir);
	    }
	    foreach($dir as $v){
	        if(empty($v)) continue;
	        if($one&&!$is_linux){
	            $t_dir.=$v;
	            $one=false;
	        }else $t_dir.=DIRECTORY_SEPARATOR.$v;
	        $t_dir=str_replace("\\", "/", $t_dir);
	        if(is_array($sdir))foreach ($sdir as $vv){
	            if (strpos($vv,$t_dir)===0)continue 2;
	        }
	        
	        if(is_dir($t_dir))continue;
	        if(!@mkdir($t_dir,0777)){
	            throw new Exception(__("can't create directory :dir",array(":dir"=>$t_dir)));
	        }
	        @chmod($t_dir, 0777);
	    }
	    return true;
	}
}