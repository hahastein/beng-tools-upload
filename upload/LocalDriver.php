<?php
/**
 * 52Beng Framework Admin
 *
 * @link http://www.52beng.com
 * @copyright Copyright © 2019 52Beng Framework. All rights reserved.
 * @author hahastein <146119@qq.com>
 * @license http://www.52beng.com/license
 * @date 2018/6/22 12:47
 * @update 2019/4/22
 */

namespace bengbeng\tools\upload;

use yii\imagine\Image;

class LocalDriver extends BaseUploadDriver implements UploadDriverInterface {

    /**
     * 构造函数，用于设置上传根路径
     * @param $config
     */
    public function __construct($config){

        parent::__construct($config);


    }

    public function checkRootPath(){
        if(empty($this->rootPath)){
            $this->error = '根目录不能为空，请创建根目录';
            return false;
        }

        if(!(is_dir($this->rootPath) && is_writable($this->rootPath))){
            $this->error = '上传根目录不存在！请尝试手动创建:' . $this->rootPath;
            return false;
        }
        return true;
    }

    public function checkSavePath(){
        /* 检测并创建目录 */
        if (!$this->mkdir($this->savePath)) {
            return false;
        } else {
            /* 检测目录是否可写 */
            if (!is_writable($this->rootPath .'/'. $this->savePath)) {
                $this->error = '上传目录 ' . $this->savePath . ' 不可写！';
                return false;
            } else {
                return true;
            }
        }
    }

    public function mkdir($savePath){
        $dir = $this->rootPath .'/'. $savePath;
        if(is_dir($dir)){
            return true;
        }

        if(mkdir($dir, 0777, true)){
            return true;
        } else {
            $this->error = "目录 {$savePath} 创建失败！";
            return false;
        }
    }

    public function upload($file, $replace=true)
    {

        if(!$this->check($file)){
            return false;
        }

        $this->uploadOriginPath = '';
        $saveName = $this->getName($file);
        $this->uploadOriginPath = $this->rootPath . '/' . $this->savePath . '/' .$saveName;

        /* 不覆盖同名文件 */
        if (!$replace && is_file($this->uploadOriginPath)) {
            $this->error = '存在同名文件' . $saveName;
            return false;
        }
        /* 移动文件 */
        if (!move_uploaded_file($file['tmp_name'], $this->uploadOriginPath)) {
            $this->error = '文件上传保存错误！';
            return false;
        }

        if($this->thumbnail){

            $this->getImageInfo($imageWidth, $imageHeight);
            $file['width'] = $imageWidth;
            $file['height'] = $imageHeight;
            $file['originName'] = $saveName;

            if (!$this->thumbnail($file)) {
                return false;
            }
        }

        $this->uploadOriginPath = '/' . $this->savePath . '/' .$saveName;

        return true;
    }

    /**
     * 生成所缩略图
     * @param $file
     * @param int $zoom
     * @param int $width
     * @param int $height
     * @return bool
     */
    public function thumbnail($file, $zoom = 0, $width=0, $height=0){
        $this->uploadThumbnailPath = '';
        //计算自动大小
        $zoom = $this->getThumbnailZoom($zoom);

        self::autoSize($file, $zoom, $width, $height);

        try {

            $saveName = $file['originName'];
            $this->uploadThumbnailPath = $this->rootPath . '/' . $this->savePath . '/t'.$width.'_' . $saveName;
            if (Image::thumbnail($this->uploadOriginPath, $width, $height)->save($this->uploadThumbnailPath)) {
                $this->uploadThumbnailPath = '/' . $this->savePath . '/t'.$width.'_' . $saveName;
                return true;
            } else {
                throw new \Exception('生成缩略图失败');
            }
        }catch (\Exception $ex){
            $this->error = $ex->getMessage();
            return false;
        }
    }

    /**
     * 配置缩略图比例
     * @param $zoom
     * @return float
     */
    private function getThumbnailZoom($zoom){
        if($zoom == 0){
            if(isset($this->thumbnail['zoom'])){
                $zoom = $this->thumbnail['zoom'];
            }
        }
        return $zoom;
    }

    /**
     * 配置缩略图的宽度
     * @param $width
     * @return float 返回配置的宽度
     */
    private function getThumbnailWidth($width){
        if($width<=0){
            if(isset($this->thumbnail['width'])){
                $width = $this->thumbnail['width'];
            }
        }
        return $width;
    }

    /**
     * 配置缩略图的高度
     * @param $height
     * @return float 返回配置的高度
     */
    private function getThumbnailHeight($height){
        if($height<=0){
            if(isset($this->thumbnail['height'])){
                $height = $this->thumbnail['height'];
            }
        }
        return $height;
    }

    /**
     * 自动计算缩略图的大小
     * @param array $fileInfo 图片信息
     * @param float $zoom 缩略图比列
     * @param float &$width 缩略图宽度并返回新的宽度
     * @param float &$height 缩略图高度并返回新的高度
     */
    private function autoSize($fileInfo, $zoom, &$width, &$height){

        if($zoom >0){
            $width = round($fileInfo['width'] * $zoom / 100);
//            $height = $fileInfo['height'] * $zoom / 100;
        }

        $width = $this->getThumbnailWidth($width);
        $height = $this->getThumbnailHeight($height);

        if($fileInfo['width']>0 && $fileInfo['height']>0) {
            if ($height == 0) {
                //按宽度自动设置
                if ($fileInfo['width'] >= $width) {
                    $height = ($fileInfo['height'] * $width) / $fileInfo['width'];
                }
            } else if ($width == 0) {
                //按高度自动设置
                if ($fileInfo['height'] >= $height) {
                    $width = ($fileInfo['width'] * $height) / $fileInfo['height'];
                }
            }
        }

    }

}
