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
use think\exception\ValidateException;
use app\validate\Page as PageValidate;
use app\validate\User as UserValidate;
use app\model\Images as ImagesModel;


use think\Request;

class Member extends BaseController
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
        try {
            Validate(PageValidate::class)->check($data);
        } catch (ValidateException $exception) {
            return $this->create(null, $exception->getError(), 400);
        }
        $result = UserModel::where('email', 'like', '%' . $data['name'] . '%')->order('id desc')->paginate([
            'list_rows' => (int)$data['size'],
            'page' => (int)$data['page'],
        ]);
        foreach ($result as $key => $value) {
            $role =  RoleModel::find($value['role_id']);
            $value['role_name'] = $role['name'];
            $value['user_size'] = ImagesModel::where('user_id', $value['id'])->sum('size');
            unset($value->password);
            unset($value->Secret_key);
        }
        return $this->create($result, '查询成功', 200);
    }

    /**
     * 新增
     */
    public function save(Request $request)
    {
        $data = $request->param();
        $ip = $request->ip();
        $uid = $request->uid;
        try {
            Validate(UserValidate::class)->check($data);
        } catch (ValidateException $exception) {
            return $this->create(null, $exception->getError(), 400);
        }
        $user = UserModel::where("email", $data['email'])->find();
        if (isset($user)) return $this->create(null, '用户已存在', 400);

        $userold = UserModel::onlyTrashed()->where("email", $data['email'])->find();
        if (isset($userold)) {
            $userold->restore();
            $userold = UserModel::where("email", $data['email'])->find();
            $userold->role_id     = $data['role_id'];
            $userold->username     = $data['username'];
            $userold->phone     = $data['phone'];
            $userold->password     = sha1("123456");
            $userold->capacity     = $data['capacity'];
            $userold->state     = $data['state'];
            $userold->save();
            $this->setLog($uid, "新增了成员", $data['email'], "ID:".$user['id']);
            return $this->create("", '添加成功，默认密码为：123456', 200);
        }

        $user = new UserModel;
        $user->save([
            'role_id'  =>  $data['role_id'],
            'password' =>  sha1("123456"),
            'username' =>  $data['username'],
            'phone' =>  $data['phone'],
            'email' =>  $data['email'],
            'capacity' =>  $data['capacity'],
            'Secret_key' =>  md5($data['email'] . mt_rand(1000, 9999)),
            'avatar' =>  $data['avatar'],
            'state' =>  $data['state'],
            'reg_ip' =>  $ip,
        ]);
        $this->setLog($uid, "新增了成员", $data['email'], "ID:".$user['id']);
        return $this->create("", '添加成功，默认密码为：123456', 200);
    }



    /**
     * 修改
     */
    public function update(Request $request)
    {
        $data = $request->param();
        $uid = $request->uid;
        try {
            Validate(UserValidate::class)->check($data);
        } catch (ValidateException $exception) {
            return $this->create(null, $exception->getError(), 400);
        }
        $user = UserModel::find($data['id']);

        $user2 = UserModel::where("email", $data['email'])->find();
        if (isset($user2) && $user2['id'] != $user['id']) {
            return $this->create(null, '此邮箱已存在', 400);
        }
        
        $role_id = $user['id'] == 1?$user['role_id']:$data['role_id'];
        if($data['pwd'] == 1){
            $user->password     = sha1("123456");
        }
        $user->role_id     = $role_id;
        $user->username     = $data['username'];
        $user->phone     = $data['phone'];
        $user->email     = $data['email'];
        $user->capacity     = $data['capacity'];
        $user->state     = $data['state'];
        $user->save();
        $this->setLog($uid, "修改了成员信息", "", "被修改成员ID:".$user['id']);
        return $this->create(null, '修改成功', 200);
    }

    /**
     * 删除
     */
    public function delete(Request $request,$id)
    {
        $uid = $request->uid;
        if($id == 1){
            return $this->create(null, '此账号为根管理员账号，无法删除', 400);
        }
        try {
            UserModel::destroy($id);
            $this->setLog($uid, "删除了成员", "", "ID:".$id);
            return $this->create(null, '删除成功', 200);
        } catch (\Error $e) {
            return $this->create([], $e->getMessage(), 400);
        }
    }
}
