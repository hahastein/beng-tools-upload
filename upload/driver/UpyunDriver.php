<?php
/**
 * 52Beng Framework Admin
 *
 * @link http://www.52beng.com
 * @copyright Copyright © 2019 52Beng Framework. All rights reserved.
 * @author hahastein <146119@qq.com>
 * @license http://www.52beng.com/license
 * @date 2018/6/22 13:47
 * @update 2019/4/22
 */

namespace bengbeng\tools\upload\driver;

use Upyun\Config;
use Upyun\Upyun;

class UpyunDriver extends BaseUploadDriver implements UploadDriverInterface {

    private $selector;

    public function __construct($config = [])
    {
        parent::__construct($config);

        $upyunConfig = new Config($this->sdkConfig['service'], $this->sdkConfig['user'], $this->sdkConfig['pwd']);
        $this->selector = new Upyun($upyunConfig);
    }

    public function checkRootPath($rootPath = '')
    {
        return true;
    }

    public function checkSavePath()
    {
        try{
            return $this->selector->info($this->savePath);
        }catch (\Exception $ex){
            if($this->mkdir($this->savePath)){
                $this->error = '文件夹不存在，已创建成功';
                return true;
            }else{
                $this->error = $ex->getMessage();
                return false;
            }
        }
    }

    public function upload($file, $replace = true)
    {
        $this->check($file);

        $fileStream = fopen($file['tmp_name'], 'r');
        $saveName = $this->getName($file);
        $this->uploadOriginPath = $this->savePath . '/' .$saveName;

        try{
            return $this->selector->write($this->uploadOriginPath, $fileStream);
        }catch (\Exception $ex){
            $this->error = $ex->getMessage();
            return false;
        }
    }

    public function mkdir($savePath)
    {
        return $this->selector->createDir($savePath);
    }
}