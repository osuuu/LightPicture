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
use app\model\Images as ImagesModel;
use app\model\User as UserModel;
use app\model\Role as RoleModel;
use think\Exception;
use app\services\UploadCLass;

use think\exception\ValidateException;
use app\validate\Page as PageValidate;


use think\Request;

class Images extends BaseController
{
    /**
     * 查询
     * @param  page  页码
     * @param  size  数量
     * @param  name  模糊查询名称
     */
    public function index(Request $request)
    {
        $data = $request->param();
        $uid = $request->uid;
        $data['uid'] = $uid;
        try {
            Validate(PageValidate::class)->check($data);
        } catch (ValidateException $exception) {
            return $this->create(null, $exception->getError(), 400);
        }
        if ($data['type'] == 1) {
            $result = ImagesModel::where('user_id', $uid)->where('name', 'like', '%' . $data['name'] . '%')->order('id desc')->paginate([
                'list_rows' => (int)$data['size'],
                'page' => (int)$data['page'],
            ]);
        } else {
            $userInfo =  UserModel::find($uid);
            $role = RoleModel::find($userInfo['role_id']);
            $result = ImagesModel::getImgs($role, $data);
        }

        foreach ($result as $key => $value) {
            $user =  UserModel::find($value['user_id']);
            $value['user_email'] = $user['email'];
            $value['user_name'] = $user['username'];
        }
        return $this->create($result, '查询成功', 200);
    }


    /**
     * 删除
     */
    public function delete(Request $request, $id)
    {
        $uid = $request->uid;
        $userInfo =  UserModel::find($uid);
        $role = RoleModel::find($userInfo['role_id']);
        $imgs =  ImagesModel::find($id);
        $UploadCLass = new UploadCLass;

        if ($role['is_admin'] == 1) {
            $UploadCLass->delete($imgs["path"], $imgs['storage_id']);
            $name = $imgs['name'];
            $imgs->delete();
            $this->setLog($uid, "删除了图片", "ID:".$id, $name,2);
            return $this->create($name, '删除成功', 200);
        } else  if ($role['is_del_own'] == 1 && $imgs['user_id'] == $uid) {
            $UploadCLass->delete($imgs["path"], $imgs['storage_id']);
            $name = $imgs['name'];
            $imgs->delete();
            $this->setLog($uid, "删除了图片", "ID:".$id, $name,2);
            return $this->create($name, '删除成功', 200);
        } else  if ($role['is_del_all'] == 1 && $imgs['storage_id'] == $role['storage_id']) {
            $UploadCLass->delete($imgs["path"], $imgs['storage_id']);
            $name = $imgs['name'];
            $imgs->delete();
            $this->setLog($uid, "删除了图片", "ID:".$id, $name,2);
            return $this->create($name, '删除成功', 200);
        } else {
            return $this->create('当前角色组没有删除权限', '删除失败', 400);
        }
    }
}
