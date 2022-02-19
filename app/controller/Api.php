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
use app\model\Storage as StorageModel;
use app\model\Role as RoleModel;
use app\model\User as UserModel;
use app\model\Images as ImagesModel;
use think\exception\ValidateException;
use app\validate\Page as PageValidate;
use app\services\UploadCLass;
use app\model\System as SystemModel;


use think\Request;

class Api extends BaseController
{
    // 上传
    public function upload(Request $request)
    {
        $key = $request->param("key");
        if (!$key || $key == 'undefined' || $key == null) {
            return $this->create([], '未登陆或密钥key为空', 400);
        }
        if ($_FILES["file"]["error"] > 0) return $this->create([], '上传出错', 400);
        $max_size = SystemModel::where('key', "upload_max")->value("value");
        if ($_FILES["file"]['size'] > $max_size * 1024 * 1024) {
            return $this->create(null, '图片大小超出限制', 400);
        }
        $user = UserModel::where("Secret_key", $key)->find();
        if (!isset($user) || $user['state'] == 0) return $this->create(null, '用户不存在或被停用', 400);

        $allSize = ImagesModel::where('user_id', $user['id'])->sum('size');
        if ($allSize + $_FILES["file"]['size'] > $user['capacity']) {
            return $this->create(null, '您的存储配额不足', 400);
        }

        $role = RoleModel::find($user['role_id']);
        $UploadCLass = new UploadCLass;
        $result = $UploadCLass->create($_FILES["file"], $role['storage_id']);
        if ($result['state'] == 1) {
            $img = new ImagesModel;
            $img->save([
                'user_id'  =>  $user['id'],
                'storage_id' =>  $role['storage_id'],
                'name' =>  $result['name'],
                'size' =>  $_FILES["file"]['size'],
                'path' =>  $result['path'],
                'mime' =>  $_FILES["file"]['type'],
                'url' =>  $result['url'],
                'ip' =>  $request->ip(),
            ]);
            $this->setLog($user['id'], "上传了图片", "ID:" . $img['id'], $img['name'], 2);
            return $this->create($img, '成功', 200);
        } else {
            return $this->create(null, $result['msg'], 400);
        }
    }

  

    // 删除
    public function delete(Request $request)
    {
        $id = $request->param("id");
        $key = $request->param("key");
        if (!$key || $key == 'undefined' || $key == null) {
            return $this->create([], '密钥key为空', 400);
        }
        if (!$id) return $this->create([], '图片id为空', 400);
        $user = UserModel::where("Secret_key", $key)->find();
        if (!isset($user) || $user['state'] == 0) return $this->create(null, '用户不存在或被停用', 400);
        $role = RoleModel::find($user['role_id']);
        $imgs =  ImagesModel::find($id);
        $uid = $user['id'];
        $UploadCLass = new UploadCLass;

        if ($role['is_admin'] == 1) {
            $UploadCLass->delete($imgs["path"], $imgs['storage_id']);
            $name = $imgs['name'];
            $imgs->delete();
            $this->setLog($uid, "删除了图片", "ID:" . $id, $name, 2);
            return $this->create($name, '删除成功', 200);
        } else  if ($role['is_del_own'] == 1 && $imgs['user_id'] == $uid) {
            $UploadCLass->delete($imgs["path"], $imgs['storage_id']);
            $name = $imgs['name'];
            $imgs->delete();
            $this->setLog($uid, "删除了图片", "ID:" . $id, $name, 2);
            return $this->create($name, '删除成功', 200);
        } else  if ($role['is_del_all'] == 1 && $imgs['storage_id'] == $role['storage_id']) {
            $UploadCLass->delete($imgs["path"], $imgs['storage_id']);
            $name = $imgs['name'];
            $imgs->delete();
            $this->setLog($uid, "删除了图片", "ID:" . $id, $name, 2);
            return $this->create($name, '删除成功', 200);
        } else {
            return $this->create('当前角色组没有删除权限', '删除失败', 400);
        }
    }

    
}
