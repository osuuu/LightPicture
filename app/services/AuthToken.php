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

namespace app\services;
use Firebase\JWT\JWT;

class AuthToken
{
    /**
     * 获取token
     * @param string $username 自定义参数
     */
    public function createToken($username)
    {
        try {
            $key = TokenKey;
            $time = time(); //当前时间
            //$token['iss']=''; //签发者 可选
            //$token['aud']=''; //接收该JWT的一方，可选
            $token['iat'] = $time; //签发时间
            $token['nbf'] = $time; //(Not Before)：某个时间点后才能访问，比如设置time+30，表示当前时间30秒后才能使用

            $token['exp'] = $time + 85400*14; //token过期时间
            $token['username'] = $username; //自定义参数

            $json = JWT::encode($token, $key);

            return $json;
        } catch (\Firebase\JWT\ExpiredException $e) { //签名不正确
            // return $this->create([], $e->getMessage(), 104);
            return "";
        } catch (\Exception $e) { //其他错误
            return "";
        }
    }

    
}
