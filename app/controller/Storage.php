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
use app\model\Images as ImagesModel;

use think\exception\ValidateException;
use app\validate\Page as PageValidate;

use think\Request;

class Storage extends BaseController
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
        $result = StorageModel::order('id desc')->paginate([
            'list_rows' => (int)$data['size'],
            'page' => (int)$data['page'],
        ]);

        foreach ($result as $key => $value) {
            $value['imgCount'] =  ImagesModel::where('storage_id', $value['id'])->count();
            $value['imgSize'] = ImagesModel::where('storage_id', $value['id'])->sum('size');
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
        $storage = StorageModel::where("name", $data['name'])->find();
        if (isset($storage)) return $this->create([], '桶名称已存在', 400);
        if($data['type'] == 'local'){
            $count = StorageModel::where("type", "local")->count();
            if($count > 1){
                return $this->create([], '本地桶仅支持添加一个', 400);
            }
        }
        $id = StorageModel::create($data)->getData('id');
        $this->setLog($uid, "新增了存储桶", $data['name'], "ID:".$id);
        return $this->create(null, empty($id) ? '添加失败' : '添加成功', empty($id) ? 400 : 200);
    }


    /**
     * 修改
     */
    public function update(Request $request)
    {
        $uid = $request->uid;
        $data = $request->param();
        $id = StorageModel::update($data)->getData('id');
        $this->setLog($uid, "修改了存储桶", $data['name'], "ID:".$id);
        return $this->create(null, empty($id) ? '修改失败' : '修改成功', empty($id) ? 400 : 200);
    }

    /**
     * 删除
     */
    public function delete(Request $request,$id)
    {
        $uid = $request->uid;
        try {
            StorageModel::destroy($id);
            $role = RoleModel::where("storage_id", $id)->select();
            foreach ($role as $key => $value) {
                $value['storage_id'] = 0;
            }
            $this->setLog($uid, "删除了存储桶", "", "ID:".$id);
            return $this->create(null, '删除成功', 200);
        } catch (\Error $e) {
            return $this->create([], $e->getMessage(), 400);
        }
    }

    /**
     * 类型
     */
    public function type()
    {
        $result = array(
            'local' => '本地存储',
            'cos' => '腾讯云 COS',
            'oss' => '阿里云 OSS',
            'kodo' => '七牛云 KODO',
            'uss' => '又拍云 USS',
            'obs' => '华为云 OBS',
        );
        return $this->create($result, '成功', 200);
    }
}
