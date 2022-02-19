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
use app\model\User as UserModel;
use app\model\Role as RoleModel;
use app\model\Log as LogModel;
use app\model\Images as ImagesModel;
use app\model\Storage as StorageModel;

use app\validate\ResetPwd as ResetPwdValidate;
use think\exception\ValidateException;

use think\Request;

class User extends BaseController
{
    // 查询用户信息
    public function info(Request $request)
    {
        $uid = $request->uid;
        $url = $request->host();
        $scheme = $request->scheme();
        $user = UserModel::find($uid);
        $role = RoleModel::find($user['role_id']);
        $user['role'] = array(
            "is_add" => $role['is_add'],
            "is_admin" => $role['is_admin'],
            "is_del_all" => $role['is_del_all'],
            "is_del_own" => $role['is_del_own'],
            "is_read" => $role['is_read'],
            "is_read_all" => $role['is_read_all'],
            "name" => $role['name']
        );
        $user['scheme'] = $scheme;
        $user['url'] = $url;
        $user['capacity'] = (int)$user['capacity'];
        $user['user_size'] = ImagesModel::where('user_id', $uid)->sum('size');
        unset($user['password']);
        unset($user['role_id']);
        return $this->create($user, '查询成功', 200);
    }


    // 修改密码
    public function resetPwd(Request $request)
    {
        $uid = $request->uid;
        $data = $request->param();
        try {
            Validate(ResetPwdValidate::class)->check($data);
        } catch (ValidateException $exception) {
            return $this->create(null, $exception->getError(), 400);
        }
        $user = UserModel::find($uid);
        if (sha1($data['oldPwd']) !== $user['password']) {
            return $this->create([], '当前密码不正确', 400);
        }
        $user->password   = sha1($data['newPwd']);
        $user->save();
        return $this->create([], '修改成功', 200);
    }

    // 重置秘钥
    public function resetKey(Request $request)
    {
        $uid = $request->uid;
        $user = UserModel::find($uid);
        $user->Secret_key     = sha1($user['email'] . mt_rand(1000000, 99999999));
        $user->save();
        return $this->create(null, '重置成功', 200);
    }

    // 修改资料
    public function update(Request $request)
    {
        $uid = $request->uid;
        $data = $request->param();
        $user = UserModel::find($uid);
        $user->avatar   = $data['avatar'];
        if (isset($data['username'])) {
            $user->username   = $data['username'];
        }
        if (isset($data['phone'])) {
            $user->phone   = $data['phone'];
        }
        $user->save();
        return $this->create([], '修改成功', 200);
    }

    // 首页统计
    public function home(Request $request)
    {
        $uid = $request->uid;
        $userCount = UserModel::count();
        $imgCount = ImagesModel::count();
        $imgMyCount = ImagesModel::where('user_id', $uid)->count();
        $imgSize = ImagesModel::sum('size');
        $result = array(
            "userCount" => $userCount,
            "imgSize" => $imgSize,
            "imgCount" => $imgCount,
            "imgMyCount" => $imgMyCount,
        );
        return $this->create($result, '成功', 200);
    }

    // 存储桶使用情况
    public function storage(Request $request)
    {
        $uid = $request->uid;
        $storsge = StorageModel::select();
        $data = array();
        foreach ($storsge as $key => $value) {
            $imgSize =  ImagesModel::where('storage_id', $value['id'])->sum('size');
            $item = array(
                'value' => round($imgSize / 1024 / 1024, 2),
                'name' => $value['name'],
            );
            array_push($data, $item);
        }
        return $this->create($data, '成功', 200);
    }

    // 日志
    public function log(Request $request)
    {
        $data = $request->param();
        $uid = $request->uid;
        $userInfo = UserModel::find($uid);
        $role = RoleModel::find($userInfo['role_id']);

        $query = array();
        $query['type'] = $data['type'];
        if ($data['type'] == 1) unset($query['type']);

        if ($role['is_admin'] == 1) {
            if ($data['read'] == 1) {
                $query['uid'] = $uid;
            } else {
                unset($query['uid']);
            }
        } else {
            $query['uid'] = $uid;
        }

        $result = LogModel::where($query)->order('id desc')->paginate([
            'list_rows' => (int)$data['size'],
            'page' => (int)$data['page'],
        ]);
        foreach ($result as $key => $value) {
            $user = UserModel::find($value['uid']);
            $value['user'] = array(
                'username' => $user['username'],
                'email' => $user['email'],
                'avatar' => $user['avatar'],
            );
        }
        return $this->create($result, '成功', 200);
    }
}
