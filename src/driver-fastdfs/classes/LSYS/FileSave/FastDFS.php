<?php
/**
 * lsys storage
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\FileSave;
use LSYS\FileSave;
use LSYS\Exception;
use LSYS\Config;
use function LSYS\FileSave\FastDFS\__ as __fastdfs;
class FastDFS implements FileSave{
    use Utils;
	protected $_server;
	protected $_config;
	public function __construct(Config $config){
	    $this->_config=$config;
	}
	public function put($filepath,$filename=null,$clear=true){
	    $this->_checkDir($this->_config->exist("safe_dir",[]),$filepath);
	    if (!is_file($filepath))return null;
	    $space=$this->_config->get("space","group");
	    $attr=array("Content-type"=>mime_content_type($filepath));
	    if ($filename==null){
	        $ext=pathinfo($filepath, PATHINFO_EXTENSION);
	    }else{
	        $attr['filename']=$filename;
	        $ext=pathinfo($filename, PATHINFO_EXTENSION);
	    }
	    $fastdfs=\LSYS\FastDFS\DI::get()->fastdfs();
	    $this->_server=$fastdfs->connect_server_from_tracker();
	    $file_info = $fastdfs->storage_upload_by_filename(
	        $filepath
	        ,$ext
	        ,$attr
	        ,$space
	        );
	    
	    if ($fastdfs->get_last_error_no()==2){
	        $file_info = $fastdfs->storage_upload_by_filename(
	            $filepath
	            ,$ext
	            ,$attr
            );
	        $space=$file_info['group_name'];
	    }
	    if (!$file_info) throw new Exception(
	        __fastdfs("upload file to FastDFS fail file::file [:msg]",array("file"=>$filepath,'msg'=>$fastdfs->get_last_error_info())),
	        $fastdfs->get_last_error_no()
	        );
	    if ($clear)@unlink($filepath);
	    return $space."/".$file_info['filename'];
	}
	protected function _split($file){
	    $p=strpos($file,"/");
	    if ($p===false)return array($this->_config->get("space","group"),$file);
	    else return array(substr($file, 0,$p),substr($file, $p+1));
	}
	public function remove($filename){
	    if (empty($filename))return true;
	    list($space,$filename)=$this->_split($filename);
	    $fastdfs=\LSYS\FastDFS\DI::get()->fastdfs();
	    $this->_server=$fastdfs->connect_server_from_tracker();
	    return $fastdfs->storage_delete_file($space,$filename);
	}
	public function __destruct(){
	    $this->_server&&\LSYS\FastDFS\DI::get()->fastdfs()->disconnect_server($this->_server);
	}
}