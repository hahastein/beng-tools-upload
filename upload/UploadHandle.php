<?php
/**
 * 52Beng Framework Admin
 *
 * @link http://www.52beng.com
 * @copyright Copyright © 2019 52Beng Framework. All rights reserved.
 * @author hahastein <146119@qq.com>
 * @license http://www.52beng.com/license
 * @date 2018/6/19 23:49
 * @update 2019/4/22 13:50
 */

namespace bengbeng\tools\upload;

/**
 * Class UploadHandle
 * @property array $mimes 允许上传的文件mime类型
 * @property array $exts 允许上传的文件后缀
 * @property integer $maxSize 上传的文件大小限制 (0-不做限制)
 * @property bool $hash
 * @property string $domain
 * @property string $driver 文件上传驱动类型(默认为本地上传类型，目前支持本地及UPyun)，扩展请详见开发说明
 * @property array $driverConfig 按驱动类型的配置文件(根据实际第三方服务器配置进行设置)
 * @package bengbeng\tools\upload
 */

class UploadHandle
{

    const UPLOAD_TYPE_LOCAL = 'Local';
    const UPLOAD_TYPE_UPYUN = 'Upyun';

    private $config = array(
        'mimes' => [],
        'exts' => ['jpg', 'png'],
        'maxSize' => 0,
        'hash' => true,
        'domain' => '',
        'driver' => self::UPLOAD_TYPE_LOCAL,
        'driverConfig' => [
            'rootPath' => '',
            'savePath' => '',
            'folderNameMode' => ['fun' => 'date', 'param' => 'Ymd'],
            'fileNameMode' => false,
            'fileExt' => '',
            'replace' => false,
            'thumbnail' => false,
            'sdkConfig' => []
        ]
    );

    private $_files;
    /**
     * @var \bengbeng\tools\upload\driver\UploadDriverInterface $uploader
     */
    private $uploader;

    /**
     * 错误信息
     * @var string
     */
    private $error = ''; //上传错误信息
    private $success_upload = []; //成功上传后的信息

    public function __construct($config)
    {
        $this->config = \yii\helpers\ArrayHelper::merge($this->config, $config);

        //加载所有上传的文件
        $this->_files = self::loadFiles();
        //设置上传驱动模式
        $this->setDriver();
    }

    public function save($validate = true){

        if(!isset($this->_files) || count($this->_files)==0) {
            if($validate) {
                $this->error = '请选择上传的文件';
                return false;
            }else{
                return [];
            }
        }

        if(!$this->uploader){
            $this->error = "不存在上传驱动：{$this->driver}";
            return false;
        }

        /* 判断如果是本地上传，则检测是否有上传的根目录，一般为(upload) */
        if($this->driver == self::UPLOAD_TYPE_LOCAL){
            if(!$this->uploader->checkRootPath()){
                $this->error = $this->uploader->getError();
                return false;
            }
        }


        /* 检查上传目录 */
        if(!$this->uploader->checkSavePath()){
            $this->error = $this->uploader->getError();
            return false;
        }

        $successFiles = [];

        foreach ($this->_files as $key => $file) {

            $file['name'] = strip_tags($file['name']);
            /* 获取上传文件后缀，允许上传无后缀文件 */
            $file['ext'] = pathinfo($file['name'], PATHINFO_EXTENSION);

            if ($this->uploader->upload($file, false)) {

                if(empty($this->domain)){
                    $successUpload['originPath'] = '/'.$this->uploader->getUploadOriginPath();
                    $successUpload['thumbnailPath'] = '/'.$this->uploader->getUploadThumbnailPath();
                }else{
                    $successUpload['originPath'] = $this->domain.'/'.$this->uploader->getUploadOriginPath();
                    $successUpload['thumbnailPath'] = $this->domain.'/'.$this->uploader->getUploadThumbnailPath();
                }
                $successFiles[] = $successUpload;
            } else {
                $this->error = $this->uploader->getError();
                return false;
            }
        }

        return $successFiles;
    }

    public function getError(){
        return $this->error;
    }

    public function getSuccess(){
        return $this->success_upload;
    }

    public function getUploader(){
        return $this->uploader;
    }

    /**
     * 设置上传驱动
     */
    private function setDriver(){
        $driver = $this->driver;
        $config = $this->driverConfig;
        $class = strpos($driver,'\\')? $driver : '\\bengbeng\\tools\\upload\\driver\\'.ucfirst(strtolower($driver)).'Driver';
        if(class_exists($class)){
            $this->uploader = new $class($config);
            $this->uploader->mimes = $this->mimes;
        }else{
            $this->uploader = false;
        }
    }

    private static function loadFiles(){

        $files = $_FILES;
        $fileArray  = array();
        $n          = 0;
        foreach ($files as $key=>$file){
            if(is_array($file['name'])) {
                $keys       =   array_keys($file);
                $count      =   count($file['name']);
                for ($i=0; $i<$count; $i++) {
                    if(!empty($file['name'][$i])) {
                        $fileArray[$n]['key'] = $key;
                        foreach ($keys as $_key) {
                            $fileArray[$n][$_key] = $file[$_key][$i];
                        }
                        $n++;
                    }
                }
            }else{
                if(!empty($file['name'])) {
                    $fileArray[$key] = $file;
                }
            }
        }
        return $fileArray;
    }

    public function __get($name) {
        return $this->config[$name];
    }

}