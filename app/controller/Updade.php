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

namespace app\controller;

use app\BaseController;

use think\facade\Db;
use app\services\UpdateClass;
use app\services\AttachClass;
use think\Exception;

use think\Request;

class Updade extends BaseController
{
    // 获取更新版本信息
    public function index(Request $request)
    {
        $data = file_get_contents(UPDATE_URL);
        $data = json_decode($data, true);
        if ((string)$data['version'] == (string)VERSION) {
            return $this->create([], '暂时没有更新', 1001);
        } else {
            return $this->create($data, '发现新版本', 1002);
        }
    }




    // 更新
    public function update(Request $request)
    {
        $data = file_get_contents(UPDATE_URL);
        $data = json_decode($data, true);
        if ((string)$data['version'] == (string)VERSION) {
            return $this->create([], '暂时没有更新', 200);
        }

        $upgrade = new UpdateClass;
        try {
            // 判断是否存在安装包
            $upgradeFile = WORKSPACE . 'update.zip';
            $file = file_exists($upgradeFile) ? $upgradeFile : $upgrade->download($data['url']);

            // 校验 MD5
            if (strtolower(md5_file($file)) !== strtolower($data['md5'])) {
                return $this->create([], '安装包已损坏，请稍后再试', 400);
            }

            // 解压安装包到工作区目录
            $upgrade->unzip($file);
            // 更新数据库结构 sql 文件路径
            if (!@file_get_contents(DIR . $data['sql'])) {
                return $this->create([], 'SQL 文件获取失败', 400);
            }

            // 启动事务
            Db::startTrans();

            // 检测并新增表字段
            if ($tableFields = @include(DIR . $data['field'])) {
                foreach ($tableFields as $table => $fields) {
                    foreach ($fields as $field => $sql) {
                        $fetchFields = array_column(Db::query("DESCRIBE `{$table}`;"), 'Field');
                        if (!in_array($field, $fetchFields)) {
                            Db::execute($sql);
                        }
                    }
                }
            }

            // 执行 sql 导入
            if (is_file(DIR . $data['sql'])) {
                $lines = file(DIR . $data['sql']);
                $temp = '';
                foreach ($lines as &$line) {
                    $line = trim($line);
                    if (substr($line, 0, 2) == '--' || $line == '' || substr($line, 0, 2) == '/*') continue;
                    $temp .= $line;
                    if (substr($line, -1, 1) == ';') {
                        Db::execute($temp);
                        $temp = '';
                    }
                }
            }

            // 检测需要删除的文件或文件夹
            foreach ($data['removes'] as $key => $items) {
                foreach ($items as $item) {
                    $pathname = DIR . trim($item, '/');
                    if ($key === 'folders') {
                        $upgrade->rmdir($pathname);
                    } else {
                        @unlink($pathname);
                    }
                }
            }

            // 提交事务
            Db::commit();
            $AttachClass = new AttachClass;
            $AttachClass->attach();
            return $this->create([], '更新完成，请刷新并清除浏览器缓存', 200);
        } catch (Exception $e) {
            Db::rollback();
            return $this->create([], $e->getMessage(), 400);
        } 
    }
}
