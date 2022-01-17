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
use app\model\System as SystemModel;
use app\model\User as UserModel;
use think\Exception;
use app\services\EmailClass;

use think\Request;

class Setup extends BaseController
{
    // 查询设置信息
    public function index(Request $request, $type = "")
    {
        $uid = $request->uid;
        $system = SystemModel::where('type', $type)->select();
        foreach ($system as $key => $value) {
            if ($value['extend']) {
                $value['extend'] = json_decode($value['extend']);
            }
            if($value['attr'] == 'number'){
                $value['value'] = (int)$value['value'];
            }
        }
        return $this->create($system, '查询成功', 200);
    }



    // 保存更新设置
    public function update(Request $request)
    {
        $uid = $request->uid;
        $data = $request->param();
        foreach ($data['createData'] as $key => $value) {
            $system = SystemModel::find($value['id']);
            $system->value     = $value['value'];
            $system->save();
        }
        $this->setLog($uid,"修改了设置","","");
        return $this->create([], '成功', 200);
    }

    // 发送测试邮件
    public function sendTest(Request $request)
    {
        $uid = $request->uid;
        $email = UserModel::where('id', $uid)->value('email');
        $subject = "邮箱对接成功";
        $content = "您的邮箱对接成功";
        $EmailClass = new EmailClass;
        try {
            $EmailClass->send_mail($email, $subject, $content);
            return $this->create([], '发送成功', 200);
        } catch (Exception $e) {
            return $this->create([], $e->getMessage(), 400);
        }
    }
}
