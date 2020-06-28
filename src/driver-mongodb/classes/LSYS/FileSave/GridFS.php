<?php
/**
 * lsys storage
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\FileSave;
use LSYS\FileSave;
use LSYS\Config;
class GridFS implements FileSave{
    use Utils;
	protected $_gridfs;
	protected $_space;
	protected $_config;
	public function __construct(Config $config,\LSYS\MongoDB $mongodb=NULL){
	    $monggodb=$mongodb?$mongodb:\LSYS\MongoDB\DI::get()->mongodb();
	    $this->_config=$config;
	    $this->_space=$config->get("db");
	    if (!$this->_space){
	        $db=$monggodb->getDatabase();
	        $this->_space=strval($db);
	    }else $db=$monggodb->selectDatabase($this->_space);
	    $this->_gridfs = $db->selectGridFSBucket();
	}
	public function put(?string $filepath,?string $filename=null,bool $clear=true){
	    $this->_checkDir($this->_config->exist("safe_dir",[]),$filepath);
	    if (!is_file($filepath))return null;
	    $attr=array();
	    if ($filename){
	        $attr['filename']=$filename;
	    }
	    $res=fopen($filepath, "rb");
	    $id =$this->_gridfs->uploadFromStream($filename,$res,$attr);
	    fclose($res);
	    if ($clear)@unlink($filepath);
	    return $this->_space."/".strval($id);
	}
	public function remove(?string $filename):bool{
	    if (empty($filename))return true;
	    list($space,$filename)=$this->_split($filename);
	    if ($this->_space!=$space)return false;
	    $id=new \MongoDB\BSON\ObjectID($filename);
	    return (bool)$this->_gridfs->delete($id);
	}
	protected function _split($file){
	    $p=strpos($file,"/");
	    if ($p===false)return array(null,$file);
	    else return array(substr($file, 0,$p),substr($file, $p+1));
	}
}