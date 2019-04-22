#BengBeng Framework - Upload组件使用说明
======================================
* Composer
    > composer require bengbeng\tools-upload (未开放)
* 命名空间
    > use bengbeng\tools\upload\UploadHandle;
* 初始化
    ```
        $update = new UploadHandle([
            'driver' => UploadHandle::UPLOAD_TYPE_LOCAL,
            'driverConfig' => [
              'rootPath' => \Yii::getAlias('@res'),
              'savePath' => 'img'
            ]
          ]);
    ```
* 枚举说明
    > UploadHandle::UPLOAD_TYPE_LOCAL 为本地图片上传
    
    > UploadHandle::UPLOAD_TYPE_UPYUN 为又拍云上传
* 配置文件说明
    * mimes (array|string) 验证上传文件的mime类型，一个可以使用字符串或者数组形式，多个请用数组形式
        > mimes = 'application' 或者 mimes = ['application', 'image']
    * exts (array) 验证上传文件的后缀名
        > exts = ['jpg', 'png']
    * maxSize (int) 限制的最大文件大小 （未实现）
    * hash (bool)  (未实现)
    * domain (string) 返回拼接时的域名，不填写则按根目录级别返回
        > domain = 'http://img.52beng.com'
    * driver (string) 上传驱动类型(默认提供两种，参看枚举说明)(支持自定义方式，请填写自己创建的命名空间+类名)
        > 默认方式： UploadHandle::UPLOAD_TYPE_LOCAL 或者 UploadHandle::UPLOAD_TYPE_UPYUN
        
        > 自定义方式：\\bengbeng\\framework\\components\\handles\\UploadHandle
    * driverConfig (array) 驱动配置
        * rootPath (string) 上传的根目录 (当driver方式不为UPLOAD_TYPE_LOCAL时，此方法有效)
        * savePath (string) 上传的存储路径
            > user 或者 user/store
        * folderNameMode (array) 文件夹的命名方式(设置后则在savePath后在创建folderNameMode的方式生成的文件夹)
            > 使用后，则会在savePath后跟上这个参数生成的文件夹
            ```
            ['fun' => 'date', 'param' => 'Ymd']
            ```
            > 使用后效果
            ```
            user/store/20190422
            ```
            > 如果想改变其结构，调整参数即可，例如：['fun' => 'date', 'param' => 'Y/m/d']，修改后为：
            ```
            user/store/2019/04/22
            
            ```
        * fileNameMode (string) 文件的命名方式，默认为 md5(uniqid(rand()))
        * fileExt (string) 文件后缀名配置，默认为 '' (空则表示使用原有后缀名)
        * replace (bool) 是否开启重名自动替换，默认为false
        * thumbnail (array) 缩略图配置项，默认为false (以下设置项都是单一的，设置一个，其他两个无效)
            * zoom 按缩放比例
            * width 按宽度自适应
            * height 按高度自适应
        * sdkConfig (array) 第三方平台服务配置 (当driver方式不为UPLOAD_TYPE_LOCAL时，此方法有效。配置按照第三方平台提供的服务器配置进行)
            > 以又拍云为例，此参数下配置为：
            ```
            [
                'service' => '服务名', 
                'user' => '操作员名', 
                'pwd' => '密码'
            ]
            ```
            
* 调用及错误说明
    * 调用上传：(返回 True 和 False)
        > $upload->save(); 
    * 调用错误信息：
        > $upload->getError();
    * 调用Demo
        ```
        $update = new UploadHandle([
            'mimes' => ['app', 'ccc'],
            'driver' => UploadHandle::UPLOAD_TYPE_LOCAL,
            'driverConfig' => [
                'rootPath' => \Yii::getAlias('@res'),
                'savePath' => 'img'
            ]
        ]);
        
        if($result = $update->save()){
            \Yii::$app->Beng->outHtml('上传成功');
        }else{
            \Yii::$app->Beng->outHtml($update->getError());
        }
        ```