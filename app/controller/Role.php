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

use think\Exception;

use think\Request;

class Role extends BaseController
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
        $result = RoleModel::where('name', 'like', '%' . $data['name'] . '%')->order('id desc')->paginate([
            'list_rows' => (int)$data['size'],
            'page' => (int)$data['page'],
        ]);
        foreach ($result as $key => $value) {
            $storage =  StorageModel::find($value['storage_id']);
            $value['storage_name'] = $value['storage_id'] == 0 ? '存储桶不存在' : $storage['name'];
            $value['user_num'] = UserModel::where('role_id', $value['id'])->count();
        }
        return $this->create($result, '查询成功', 200);
    }

    /**
     * 新增
     */
    public function save(Request $request)
    {
        $uid = $request->uid;
        $data = $request->param();
        RoleModel::toClear($data['default']);
        $id = RoleModel::create($data)->getData('id');
        $this->setLog($uid, "新增了角色组", $data['name'], "ID:".$id);
        return $this->create(null, empty($id) ? '添加失败' : '添加成功', empty($id) ? 400 : 200);
    }



    /**
     * 修改
     */
    public function update(Request $request)
    {
        $uid = $request->uid;
        $data = $request->param();
        if ($data['default'] == 1) RoleModel::toClear($data['default']);
        if ($data['default'] == 0){
            return $this->create(null, '至少需保留一个默认角色组', 400);
        }
        $id = RoleModel::update($data)->getData('id');
        $this->setLog($uid, "修改了角色组", $data['name'], "ID:".$id);
        return $this->create(null, empty($id) ? '修改失败' : '修改成功', empty($id) ? 400 : 200);
    }

    /**
     * 删除
     */
    public function delete(Request $request,$id)
    {
        $uid = $request->uid;
        if($id == 1){
            return $this->create(null, '此账号为根管理员分组，无法删除', 400);
        }
        try {
            RoleModel::isDel($id);
            RoleModel::destroy($id);
            $defaultId = RoleModel::where('default', 1)->value('id');
            $userList = UserModel::where("role_id", $id)->select();
            foreach ($userList as $key => $value) {
                $user = UserModel::find($value['id']);
                $user->role_id     = $defaultId;
                $user->save();
            }
            $this->setLog($uid, "删除了角色组", "", "ID:".$id);
            return $this->create(null, '删除成功', 200);
        } catch (Exception $e) {
            return $this->create([], $e->getMessage(), 400);
        }
    }
}
