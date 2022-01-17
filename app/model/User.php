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
use think\model\concern\SoftDelete;
use think\Model;

class User extends Model
{
    use SoftDelete;

    public static function login($username, $password)
    {
        if (!$username) {
            throw new Exception('请输入邮箱');
        }

        if (!$password) {
            throw new Exception('请输入密码');
        }

        if ($user = self::where('email', $username)->find()) {
            if ($user->password !== sha1($password)) {
                throw new Exception('密码不正确');
            }
            if (0 === $user->state) {
                throw new Exception('你的账户已被停用，请联系管理员！');
            }
         
           
            return $user;

        } else {
            throw new Exception('用户不存在');
        }
    }
}
