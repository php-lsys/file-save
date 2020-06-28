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
class MongoDB implements FileSave{
    use Utils;
	protected $_mcon;
	protected $_config;
	protected $_space;
	public function __construct(Config $config,\LSYS\MongoDB $mongodb=NULL){
	    $monggodb=$mongodb?$mongodb:\LSYS\MongoDB\DI::get()->mongodb();
	    $this->_config=$config;
	    $this->_db=$config->get("db");
	    if (!$this->_db)$db=$monggodb->getDatabase();
	    else $db=$monggodb->selectDatabase($this->_db);
	    $this->_space=$config->get("space","default");
	    $this->_mcon = $db->selectCollection($this->_space);
	}
	public function put(?string $filepath,?string $filename=null,bool $clear=true){
	    $this->_checkDir($this->_config->exist("safe_dir",[]),$filepath);
	    if (!is_file($filepath))return null;
	    $data=array('filedata'=>new \MongoDB\BSON\Binary(file_get_contents($filepath),\MongoDB\BSON\Binary::TYPE_GENERIC),"Content-type"=>mime_content_type($filepath));
	    if ($filename){
	        $data['filename']=$filename;
	    }
	    $id =$this->_mcon->insertOne($data);
	    if ($clear)@unlink($filepath);
	    return $this->_space."/".strval($id->getInsertedId());
	}
	protected function _split($file){
	    $p=strpos($file,"/");
	    if ($p===false)return array($this->_config->get("space","group"),$file);
	    else return array(substr($file, 0,$p),substr($file, $p+1));
	}
	public function remove(?string $filename):bool{
	    if (empty($filename))return true;
	    list($space,$filename)=$this->_split($filename);
	    if ($this->_space!=$space)return false;
	    $id=new \MongoDB\BSON\ObjectID($filename);
		return (bool)$this->_mcon->deleteOne(array(
			'_id'=>$id	
		));
	}
}