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

namespace app\middleware;

use \Firebase\JWT\JWT;
use app\BaseController;
use think\facade\Request;

class TokenVerify extends BaseController
{
    /**
     * 处理请求
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {

        $header = $request->header();
        if (!isset($header['token'])) {
            // 未携带token
            return $this->create([], '登录失效', -1);
        }
        $token = $header['token'];
        $key = TokenKey;

        try {
            JWT::$leeway = 60; //当前时间减去60，把时间留点余地
            $decoded = JWT::decode($token, $key, ['HS256']); //HS256方式，这里要和签发的时候对应
            $arr = (array)$decoded;
            $request->uid = $arr['username'];

        } catch (\Firebase\JWT\SignatureInvalidException $e) { //签名不正确
            return $this->create([], $e->getMessage(), -1);
        } catch (\Firebase\JWT\BeforeValidException $e) { // 签名在某个时间点之后才能用
            return $this->create([], $e->getMessage(), -1);
        } catch (\Firebase\JWT\ExpiredException $e) { // token过期
            return $this->create(null, '登录失效', -1);
        } catch (\Exception $e) { //其他错误
            return $this->create(null, $e->getMessage(), -1);
        }

        return $next($request);
    }
}
