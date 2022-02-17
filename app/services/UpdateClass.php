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
use think\Exception;
class UpdateClass
{
    /**
     * 工作区目录
     *
     * @var
     */
    private $workspace;

    /**
     * 系统根目录
     *
     * @var
     */
    private $dir;


    /**
     * 构造方法
     * @throws \Exception
     */
    public function __construct()
    {
        $this->workspace = WORKSPACE;
        $this->dir = DIR;
        if (!class_exists('ZipArchive')) {
            throw new \Exception('无法继续执行, 请确保 ZipArchive 正确安装');
        }
        ob_clean();
    }

    // 下载更新包
    public function download($url)
    {

        $headers = [
            'Accept-Encoding: gzip, deflate',
        ];

        !is_dir($this->workspace) && @mkdir($this->workspace, 0777, true);
        if (!is_dir($this->workspace)) throw new Exception('工作区目录不存在, 请检查是否有写入权限');
        $pathname = $this->workspace . 'update.zip';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip, delete');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 180);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 180);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $contents = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($statusCode !== 200) {
            throw new Exception('安装包下载失败, 请稍后重试!');
        }

        if (!@file_put_contents($pathname, $contents)) {
            throw new Exception('安装包保存失败, 请检查 runtime 目录是否有写入权限');
        }
        return $pathname;
    }

    /**
     * 解压
     *
     * @param string $file 文件
     * @param string $dir 解压到文件夹
     * @return mixed
     * @throws Exception
     */
    public function unzip($file)
    {
        $dir = $this->dir;
        $zip = new \ZipArchive;
        if ($zip->open($file) !== true) {
            throw new Exception('无法打开安装包文件');
        }
        if (!$zip->extractTo($dir)) {
            $zip->close();
            throw new Exception('无法解压安装包文件');
        }

        $zip->close();
        @unlink($file); // 解压成功后删除临时文件
    }

    /**
     * 删除文件夹
     *
     * @param $dir
     * @return bool
     */
    public function rmdir($dir)
    {
        if (!$handle = @opendir($dir)) return false;

        while (false !== ($file = readdir($handle))) {
            if ($file !== "." && $file !== "..") { // 排除当前目录与父级目录
                $file = $dir . '/' . $file;
                if (is_dir($file)) {
                    $this->rmdir($file);
                } else {
                    @unlink($file);
                }
            }
        }

        return @rmdir($dir);
    }

    public function __destruct()
    {
        @$this->rmdir($this->workspace); // 清除临时工作目录
    }
}
