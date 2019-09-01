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
class AliOSS implements FileSave{
    use Utils;
    /**
     * @var \OssClient
     */
    private $_oss;
    private $_bucket;
    protected $_config;
    public function __construct(Config $config){
        $this->_config=$config;
        $oss_access_id=$this->_config->get("oss_access_id");
        $oss_access_key=$this->_config->get("oss_access_key");
        $endpoint=$this->_config->get("oss_endpoint");
        $this->_bucket=$this->_config->get("bucket");
        $ossClient = new \OssClient($oss_access_id, $oss_access_key, $endpoint, false);
        if (!$this->_oss->doesBucketExist($this->_bucket)){
            $this->_oss->createBucket($this->_bucket, \OssClient::OSS_ACL_TYPE_PUBLIC_READ_WRITE);
        }
        $this->_oss=$ossClient;
    }
    public function put($filepath,$filename=null,$clear=true){
        $this->_checkDir($this->_config->exist("safe_dir",[]),$filepath);
        if (!is_file($filepath))return null;
        $space=$this->_config->get("space","__");
        $ossheader=array(
            "Content-type"=>mime_content_type($filepath)
        );
        if ($filename==null){
            $ext=pathinfo($filepath, PATHINFO_EXTENSION);
        }else{
            $ext=pathinfo($filename, PATHINFO_EXTENSION);
            $ossheader['filename'] =$filename;
        }
        $_filename=$space."-".uniqid().".".$ext;
        $data=$this->_oss->uploadFile($this->_bucket,$_filename,$filepath,array(
            \OssClient::OSS_HEADERS => $ossheader
        ));
        if ($clear)unlink($filepath);
        return $_filename;
    }
    public function remove($filename){
        if (empty($filename))return true;
        $this->_oss->deleteObject($this->_bucket,$filename);
        return !$this->_oss->doesObjectExist($this->_bucket, $filename);
    }
}