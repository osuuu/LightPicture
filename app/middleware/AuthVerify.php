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

use app\BaseController;
use think\facade\Request;
use app\model\Role as RoleModel;
use app\model\User as UserModel;

class AuthVerify extends BaseController
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
        $uid = $request->uid;
        
        $role_id = UserModel::where('id', $uid)->value('role_id');
        $role = RoleModel::find($role_id);
        if($role['is_admin'] == 0){
            return $this->create([], '您没有操作权限', 400);
        }
        return $next($request);
    }
}
