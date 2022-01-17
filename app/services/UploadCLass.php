<?php
// +----------------------------------------------------------------------
// | LightPicture [ 图床 ]
// +----------------------------------------------------------------------
// | 企业团队图片资源管理系统
// +----------------------------------------------------------------------
// | Github: https://github.com/osuuu/LightPicture
// +----------------------------------------------------------------------
// | Copyright © http://picture.h234.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: Team <admin@osuu.net>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace app\services;

use app\model\Storage as StorageModel;
use OSS\OssClient;
use OSS\Core\OssException;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use Qcloud\Cos\Client;

class UploadCLass
{
    public function getPath()
    {
        $year = date("Y");
        $month = date("m");
        return FOLDER . $year . '/' . $month . '/';
    }
    public function getName($name)
    {
        $str_img = explode('.', $name);
        $format = '.' . $str_img[count($str_img) - 1];
        return  substr(md5(date("YmdHis") . rand(1000, 9999)),8,16). $format;
    }


    /**
     * 阿里云OSS上传方法
     * @param  \think\Request  $file
     */
    public function aliyuncs_upload($file, $sid)
    {
        $storage = StorageModel::find($sid);
        $accessKeyId = $storage['AccessKey']; //"云 API 密钥 SecretId";
        $accessKeySecret = $storage['SecretKey']; //"云 API 密钥 SecretKey";
        $endpoint = $storage['region']; //设置一个默认的存储桶地域
        $bucket = $storage['bucket']; // 设置存储空间名称。

        $name = $this->getName($file['name']);
        $path = $this->getPath() . $name;
        $filePath = $file['tmp_name'];


        try {
            // 上传oss
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $ossClient->uploadFile($bucket, $path, $filePath);
            return array(
                'path' => $path,
                'name' => $name,
                'url' => $storage['space_domain'] . '/' . $path,
                'state' => 1,
            );
        } catch (OssException $e) {
            return array(
                'msg' => $e->getMessage(),
                'state' => 0,
            );
        }
    }


    /**
     * 阿里云oss删除单个文件方法
     * @param  \think\Request  $file
     */
    function aliyuncs_delete($path, $sid)
    {
        $storage = StorageModel::find($sid);
        $accessKeyId = $storage['AccessKey']; //"云 API 密钥 SecretId";
        $accessKeySecret = $storage['SecretKey']; //"云 API 密钥 SecretKey";
        $endpoint = $storage['region']; //设置一个默认的存储桶地域
        $bucket = $storage['bucket']; // 设置存储空间名称。

        $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
        $ossClient->deleteObject($bucket, $path);
    }

    /**
     * 七牛云上传方法
     * @param  \think\Request  $file
     */
    function qiniu_upload($file, $sid)
    {
        $storage = StorageModel::find($sid);
        $accessKey = $storage['AccessKey']; //"云 API 密钥 SecretId";
        $secretKey = $storage['SecretKey']; //"云 API 密钥 SecretKey";
        $bucket = $storage['bucket']; // 设置存储空间名称。

        $auth = new Auth($accessKey, $secretKey);
        // 生成上传 Token
        $token = $auth->uploadToken($bucket);
        // 构建 UploadManager 对象
        $uploadMgr = new UploadManager();
        // 要上传文件的本地路径
        $filePath = $file['tmp_name'];
        // 上传到七牛后保存的文件名
        $name = $this->getName($file['name']);
        $path = $this->getPath() . $name;
        list($ret, $err) = $uploadMgr->putFile($token, $path, $filePath);

        if ($err !== null) {
            return array(
                'msg' =>$err,
                'state' => 0,
            );
        } else {
            return array(
                'path' => $path,
                'name' => $name,
                'url' => $storage['space_domain'] . '/' . $path,
                'state' => 1,
            );
        }
    }

    /**
     * 七牛云删除单个文件方法
     * @param  \think\Request  $file
     */
    function qiniu_delete($path,$sid)
    {
        $storage = StorageModel::find($sid);
        $accessKey = $storage['AccessKey']; //"云 API 密钥 SecretId";
        $secretKey = $storage['SecretKey']; //"云 API 密钥 SecretKey";
        $bucket = $storage['bucket']; // 设置存储空间名称。

        $auth = new Auth($accessKey, $secretKey);
        $config = new \Qiniu\Config();
        $bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);
        list($Info, $err) = $bucketManager->delete($bucket, $path);

    }

    /**
     * 腾讯云cos上传方法
     * @param  \think\Request  $file
     */
    function tencent_upload($file, $sid)
    {
        $storage = StorageModel::find($sid);
        $secretId = $storage['AccessKey']; //"云 API 密钥 SecretId";
        $secretKey = $storage['SecretKey']; //"云 API 密钥 SecretKey";
        $region = $storage['region']; //设置一个默认的存储桶地域
        $cosClient = new \Qcloud\Cos\Client(
            array(
                'region' => $region,
                'schema' => 'http', //协议头部，默认为http
                'credentials' => array(
                    'secretId'  => $secretId,
                    'secretKey' => $secretKey
                )
            )
        );
        $local_path = $file['tmp_name'];
        try {
            $name = $this->getName($file['name']);
            $path = $this->getPath() . $name;
            $cosClient->upload(
                $bucket = $storage['bucket'], //格式：BucketName-APPID
                $key = $path,
                $body = fopen($local_path, 'rb')
            );
            return array(
                'path' => $path,
                'name' => $name,
                'url' => $storage['space_domain'] . '/' . $path,
                'state' => 1,
            );
        } catch (\Exception $e) {
            return array(
                'msg' => $e->getMessage(),
                'state' => 0,
            );
        }
    }

    /**
     * 腾讯云cos删除方法
     * @param  \think\Request  $file
     */
    function tencent_delete($path, $sid)
    {
        $storage = StorageModel::find($sid);
        $secretId = $storage['AccessKey']; //"云 API 密钥 SecretId";
        $secretKey = $storage['SecretKey']; //"云 API 密钥 SecretKey";
        $region = $storage['region']; //设置一个默认的存储桶地域

        $cosClient = new \Qcloud\Cos\Client(
            array(
                'region' => $region,
                'schema' => 'http', //协议头部，默认为http
                'credentials' => array(
                    'secretId'  => $secretId,
                    'secretKey' => $secretKey
                )
            )
        );
        $cosClient->deleteObject(array(
            'Bucket' => $storage['bucket'], //格式：BucketName-APPID
            'Key' => $path,
            // 'VersionId' => 'exampleVersionId' //存储桶未开启版本控制时请勿携带此参数
        ));
    }

    /**
     * 本地上传方法
     * @param  \think\Request  $file
     */
    function location_upload($file, $sid)
    {

        // 获取网站协议
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $path = './' . $this->getPath();
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $newName = $this->getName($file['name']);
        // 本地上传
        if (move_uploaded_file($file["tmp_name"], $path . $newName)) {
            $url = $protocol . $_SERVER['HTTP_HOST'] . '/' . $this->getPath() . $newName;
            return array(
                'path' => $this->getPath() . $newName,
                'name' => $newName,
                'url' => $url,
                'state' => 1,
            );
        } else {
            return array(
                'msg' => '上传失败',
                'state' => 0,
            );
        }
    }

    /**
     * 本地删除方法
     * @param  \think\Request  $file
     */
    function location_delete($path)
    {
        unlink($path);
    }
}
