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
use app\model\User as UserModel;
use app\model\Code as CodeModel;
use app\model\System as SystemModel;
use app\model\Role as RoleModel;

use think\Exception;
use app\services\AuthToken;
use think\Request;
use app\services\EmailClass;
use think\facade\Validate;
use app\validate\Register as RegisterValidate;
use think\exception\ValidateException;


class Account extends BaseController
{
    // 登录
    public function login(Request $request)
    {
        $data = $request->param();
        $ip = $request->ip();
        $tokenClass = new AuthToken;

        try {
            $user = UserModel::login($data['username'], $data['password']);
            $token = $tokenClass->createToken($user['id']);
            $this->setLog($user['id'], "登录了系统", $this->city($ip), $ip);
            return $this->create($token, '登录成功', 200);
        } catch (Exception $e) {
            return $this->create([], $e->getMessage(), 400);
        }
    }



    // 注册
    public function register(Request $request)
    {
        $data = $request->param();
        $ip = $request->ip();
        $system = SystemModel::where('type', 'basics')->column('value', 'key');
        if ($system['is_reg'] != 1) {
            return $this->create(null, '管理员已关闭用户注册', 400);
        }

        try {
            Validate(RegisterValidate::class)->check($data);
        } catch (ValidateException $exception) {
            return $this->create(null, $exception->getError(), 400);
        }

        $res = CodeModel::where('email', $data['email'])
            ->where('code', $data['code'])
            ->where('create_time', '>', time() - 600)
            ->find();
        if (!$res) {
            return $this->create(null, '验证码错误', 400);
        }

        $users = UserModel::where("email", $data['email'])->find();
        if (isset($users)) return $this->create(null, '用户已存在', 400);



        $role = RoleModel::where('default', 1)->find();
        $userold = UserModel::onlyTrashed()->where("email", $data['email'])->find();
        if (isset($userold)) {
            $userold->restore();
            $userold = UserModel::where("email", $data['email'])->find();
            $userold->role_id     =  $role['id'];
            $userold->password     = sha1($data['password']);
            $userold->username     = $data['username'];
            $userold->email     = $data['email'];
            $userold->Secret_key     =  md5($data['email'] . $data['password']);
            $userold->avatar     = $data['avatar'];
            $userold->capacity     = $system['init_quota'] * 1024 * 1024 * 1024;
            $userold->state     = 1;
            $userold->reg_ip     = $ip;
            $userold->save();
            $this->setLog($userold['id'], "(注册)加入了系统", $this->city($ip), $ip);
        }else{
            $user = new UserModel;
            $user->save([
                'role_id'  =>  $role['id'],
                'password' =>  sha1($data['password']),
                'username' =>  $data['username'],
                'email' =>  $data['email'],
                'Secret_key' =>  md5($data['email'] . $data['password']),
                'avatar' =>  $data['avatar'],
                'capacity' =>  $system['init_quota'] * 1024 * 1024 * 1024,
                'state' =>  1,
                'reg_ip' =>  $ip,
            ]);
            $this->setLog($user['id'], "(注册)加入了系统", $this->city($ip), $ip);
            
        }
        return $this->create(null, '注册成功', 200);
    }

    // 重置密码
    public function forget(Request $request)
    {
        $data = $request->param();
        $ip = $request->ip();

        $res = CodeModel::where('email', $data['email'])
            ->where('code', $data['code'])
            ->where('create_time', '>', time() - 600)
            ->find();
        if (!$res) {
            return $this->create(null, '验证码错误', 400);
        }

        if ($this->cellemail($data['email'])) {
            $newpwd = (string)mt_rand(100000, 999999);
            $user = UserModel::where('email', $data['email'])->find();
            $user->password     = sha1($newpwd);
            $user->save();
            $EmailClass = new EmailClass;
            $subject = "LightPicture重置密码";
            $EmailClass->send_mail($data['email'], $subject, '您的密码已被重置为：' . $newpwd . '  请及时登录修改！');

            return $this->create(null, '重置成功！新密码已发送至邮箱', 200);
        } else {
            return $this->create(null, '用户不存在', 400);
        }
    }

    // 验证用户是否存在
    public function cellemail($email = "")
    {

        $request = Validate::rule([
            'email'   =>  'unique:user,email'
        ])->check([
            'email'  =>  $email,
        ]);

        if ($request) {
            return false;
        } else {
            return true;
        }
    }

    // 发送验证码
    public function sendCode(Request $request)
    {
        $data = $request->param();
        $checku = '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/';
        $ip = $request->ip();
        if (preg_match($checku, $data['email'])) {
            $code_q = CodeModel::where('email', $data['email'])->order('id desc')->find();
            if ($code_q) {
                if ((time() - (int)$code_q['create_time']) < 60) {
                    return $this->create(null, '发送频繁', 400);
                }
            }

            $EmailClass = new EmailClass;
            $code = mt_rand(1000, 9999);
            $subject = "LightPicture验证";

            try {
                $EmailClass->send_mail($data['email'], $subject, '您的验证码为：' . $code);
                $CodeModel = new CodeModel;
                $CodeModel->save([
                    'email'  =>  $data['email'],
                    'code' =>  $code,
                    'ip' => $ip
                ]);
                return $this->create([], '发送成功', 200);
            } catch (Exception $e) {
                return $this->create([], $e->getMessage(), 400);
            }
        } else {
            return $this->create(null, '请输入正确的邮箱', 400);
        }
    }
}
