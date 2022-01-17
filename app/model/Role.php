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
namespace app\model;

use think\Exception;

use think\Model;

class Role extends Model
{
    public static function toClear($default)
    {
        if ($default == 1) {
            $role = self::where('default', 1)->find();
            $role->default    = 0;
            $role->save();
        }
    }
    public static function isDel($id)
    {
        $role = self::find($id);
        if($role['default'] == 1){
            throw new Exception('至少需保留一个默认角色组');
        }
    }
}
