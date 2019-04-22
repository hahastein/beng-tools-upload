<?php
/**
 * 52Beng Framework Admin
 *
 * @link http://www.52beng.com
 * @copyright Copyright © 2019 52Beng Framework. All rights reserved.
 * @author hahastein <146119@qq.com>
 * @license http://www.52beng.com/license
 * @date 2019/4/19 0:21
 */

namespace bengbeng\tools\upload;

/**
 * Interface UploadDriverInterface
 * @property array $mimes
 * @package bengbeng\tools\upload
 */
interface UploadDriverInterface
{

    /**
     * 检查根目录是否存在
     * @return boolean
     */
    function checkRootPath();

    /**
     * 检查创建路径
     * @return boolean
     */
    function checkSavePath();
    /**
     * 创建目录
     * @param  string $savePath 要创建的路径
     * @return boolean 是否创建成功
     */
    function mkdir($savePath);

    /**
     * 上传类
     * @param array $file 上传的文件
     * @param boolean $replace 是否自动替换存在的文件
     * @return boolean|string 是否上传成功
     */
    function upload($file, $replace=true);

    /**
     * 获取最后一次上传错误信息
     * @return string 错误信息
     */
    function getError();

    /**
     * 获取上传成功后的文件地址
     * @return string
     */
    function getUploadOriginPath();

    /**
     * 获取上传成功后的缩略图地址
     * @return string
     */
    function getUploadThumbnailPath();


}