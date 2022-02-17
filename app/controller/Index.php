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
namespace app\controller;

use app\BaseController;
use app\model\System as SystemModel;

class Index extends BaseController
{
    public function index()
    {
        $system = SystemModel::where('type', "basics")->column('value', 'key');
        $result = array(
            "version" => VERSION,
            "time" => RELRAASE_TIME,
            "is_reg" => (int)$system['is_reg'],
            "upload_max" => (int)$system['upload_max']*1024,
            "upload_rule" => $system['upload_rule'],
            "is_show_storage" => (int)$system['is_show_storage'],
            "is_show_role" => (int)$system['is_show_role'],
            "is_show_member" => (int)$system['is_show_member'],
        );
        return $this->create($result, '成功', 200);
    }
}
