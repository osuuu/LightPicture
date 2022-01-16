<?php
// +----------------------------------------------------------------------
// | LightPicture [ 图床 ]
// +----------------------------------------------------------------------
// | 企业团队图片资源管理系统
// +----------------------------------------------------------------------
// | Copyright © http://picture.h234.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: Team <admin@osuu.net>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace app\validate;

use think\Validate;

class ResetPwd extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'oldPwd|当前密码'      =>      'require',
        'newPwd|新密码'    =>      'require|/^[a-zA-Z0-9]{6,10}$/',
    ];
    
   
}