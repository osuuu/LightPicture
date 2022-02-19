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
use Upyun\Upyun;
use Upyun\Config;
use Obs\ObsClient;

class UploadCLass
{
    /**
     * 当前储存策略参数
     *
     * @var array
     */
    protected $storage = [];

    /**
     * 生成路径
     *
     * @var
     */
    private $getPath;


    public function __construct()
    {
        $year = date("Y");
        $month = date("m");
        $this->getPath = FOLDER . $year . '/' . $month . '/';
    }

    //  生成新名称
    public function getName($name)
    {
        $str_img = explode('.', $name);
        $format = '.' . $str_img[count($str_img) - 1];
        return  substr(md5(date("YmdHis") . rand(1000, 9999)), 8, 16) . $format;
    }

    /**
     * 创建文件
     *
     * @param $file
     * @param $sid
     */
    public function create($file, $sid)
    {
        $this->storage = StorageModel::find($sid);
        switch ($this->storage['type']) {
            case 'local':
                return $this->location_upload($file);
                break;
            case 'cos':
                return $this->tencent_upload($file);
                break;
            case 'oss':
                return $this->aliyuncs_upload($file);
                break;
            case 'uss':
                return $this->upyun_upload($file, $sid);
                break;
            case 'obs':
                return $this->hwyun_upload($file, $sid);
                break;
            case 'kodo':
                return $this->qiniu_upload($file);
                break;
            default:
        }
    }

    /**
     * 删除文件
     *
     * @param $path
     * @param $sid
     */
    public function delete($path, $sid)
    {
        $this->storage = StorageModel::find($sid);
        switch ($this->storage['type']) {
            case 'local': // 本地
                unlink($path);
                break;
            case 'cos': // 腾讯云
                return $this->tencent_delete($path);
                break;
            case 'obs': // 华为云
                return $this->hwyun_delete($path);
                break;
            case 'oss': // 阿里云
                $ossClient = new OssClient($this->storage['AccessKey'], $this->storage['SecretKey'], $this->storage['region']);
                $ossClient->deleteObject($this->storage['bucket'], $path);
                break;
            case 'uss': // 又拍云
                $serviceConfig = new Config($this->storage['bucket'], $this->storage['AccessKey'], $this->storage['SecretKey']);
                $client = new Upyun($serviceConfig);
                $client->delete($path);
                break;
            case 'kodo': // 七牛云
                $auth = new Auth($this->storage['AccessKey'], $this->storage['SecretKey']);
                $config = new \Qiniu\Config();
                $bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);
                list($Info, $err) = $bucketManager->delete($this->storage['bucket'], $path);
                break;
            default:
        }
    }

    /**
     * 本地上传方法
     * @param  \think\Request  $file
     */
    function location_upload($file)
    {

        // 获取网站协议
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $path = './' . $this->getPath;
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $newName = $this->getName($file['name']);
        // 本地上传
        if (move_uploaded_file($file["tmp_name"], $path . $newName)) {
            $url = $protocol . $_SERVER['HTTP_HOST'] . '/' . $this->getPath . $newName;
            return array(
                'path' => $this->getPath . $newName,
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
     * 阿里云OSS上传方法
     * @param  \think\Request  $file
     */
    public function aliyuncs_upload($file)
    {
        $name = $this->getName($file['name']);
        $path = $this->getPath . $name;
        $filePath = $file['tmp_name'];
        try {
            $ossClient = new OssClient($this->storage['AccessKey'], $this->storage['SecretKey'], $this->storage['region']);
            $ossClient->uploadFile($this->storage['bucket'], $path, $filePath);
            return array(
                'path' => $path,
                'name' => $name,
                'url' => $this->storage['space_domain'] . '/' . $path,
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
     * 腾讯云cos上传方法
     * @param  \think\Request  $file
     */
    function tencent_upload($file)
    {
        $cosClient = new \Qcloud\Cos\Client(
            array(
                'region' => $this->storage['region'],
                'schema' => 'http', //协议头部，默认为http
                'credentials' => array(
                    'secretId'  => $this->storage['AccessKey'],
                    'secretKey' => $this->storage['SecretKey']
                )
            )
        );
        $local_path = $file['tmp_name'];
        try {
            $name = $this->getName($file['name']);
            $path = $this->getPath . $name;
            $cosClient->upload(
                $bucket = $this->storage['bucket'], //格式：BucketName-APPID
                $key = $path,
                $body = fopen($local_path, 'rb')
            );
            return array(
                'path' => $path,
                'name' => $name,
                'url' => $this->storage['space_domain'] . '/' . $path,
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
    function tencent_delete($path)
    {
        $cosClient = new \Qcloud\Cos\Client(
            array(
                'region' => $this->storage['region'],
                'schema' => 'http', //协议头部，默认为http
                'credentials' => array(
                    'secretId'  => $this->storage['AccessKey'],
                    'secretKey' => $this->storage['SecretKey']
                )
            )
        );
        $cosClient->deleteObject(array(
            'Bucket' => $this->storage['bucket'], //格式：BucketName-APPID
            'Key' => $path,
            // 'VersionId' => 'exampleVersionId' //存储桶未开启版本控制时请勿携带此参数
        ));
    }



    /**
     * 七牛云上传方法
     * @param  \think\Request  $file
     */
    function qiniu_upload($file)
    {
        $auth = new Auth($this->storage['AccessKey'], $this->storage['SecretKey']);
        // 生成上传 Token
        $token = $auth->uploadToken($this->storage['bucket']);
        // 构建 UploadManager 对象
        $uploadMgr = new UploadManager();
        // 要上传文件的本地路径
        $filePath = $file['tmp_name'];
        // 上传到七牛后保存的文件名
        $name = $this->getName($file['name']);
        $path = $this->getPath . $name;
        list($ret, $err) = $uploadMgr->putFile($token, $path, $filePath);

        if ($err !== null) {
            return array(
                'msg' => $err,
                'state' => 0,
            );
        } else {
            return array(
                'path' => $path,
                'name' => $name,
                'url' => $this->storage['space_domain'] . '/' . $path,
                'state' => 1,
            );
        }
    }


    /**
     * 又拍云上传方法
     * @param  \think\Request  $file
     */
    function upyun_upload($file)
    {
        $serviceConfig = new Config($this->storage['bucket'], $this->storage['AccessKey'], $this->storage['SecretKey']);
        $client = new Upyun($serviceConfig);
        $filePath = $file['tmp_name'];
        // 上传后保存的文件名
        $name = $this->getName($file['name']);
        $path = $this->getPath . $name;
        try {
            $client->write($path, fopen($filePath, 'r'));
            return array(
                'path' => $path,
                'name' => $name,
                'url' => $this->storage['space_domain'] . '/' . $path,
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
     * 华为云上传方法
     * @param  \think\Request  $file
     */
    function hwyun_upload($file)
    {
        $obsClient = new ObsClient([
            'key' => $this->storage['AccessKey'],
            'secret' => $this->storage['SecretKey'],
            'endpoint' => $this->storage['region']
        ]);
        $filePath = $file['tmp_name'];
        // 上传后保存的文件名
        $name = $this->getName($file['name']);
        $path = $this->getPath . $name;
        try {
            $obsClient->putObject([
                'Bucket' => $this->storage['bucket'],
                'Key' => $path,
                'SourceFile' => $filePath  // localfile为待上传的本地文件路径，需要指定到具体的文件名
            ]);
            return array(
                'path' => $path,
                'name' => $name,
                'url' => $this->storage['space_domain'] . '/' . $path,
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
     * 华为云删除方法
     * @param  \think\Request  $file
     */
    function hwyun_delete($path)
    {
        $obsClient = new ObsClient([
            'key' => $this->storage['AccessKey'],
            'secret' => $this->storage['SecretKey'],
            'endpoint' => $this->storage['region']
        ]);

        $obsClient->deleteObject([
            'Bucket' => $this->storage['bucket'],
            'Key' => $path,
        ]);
    }
}
