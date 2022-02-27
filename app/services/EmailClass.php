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

use app\BaseController;
use app\model\System as SystemModel;
use think\Exception;

use PHPMailer\PHPMailer\PHPMailer;

class EmailClass
{
    /**
     * 系统邮件发送函数
     * @param string $tomail 接收邮件者邮箱
     * @param string $name 接收邮件者名称
     * @param string $subject 邮件主题
     * @param string $body 邮件内容
     */
    public function send_mail($tomail,  $subject = '', $content = '')
    {
        $data = SystemModel::where('type', 'email')->column('value', 'key');
        $email_template = SystemModel::where('type','email_template')->value('value');
        $mail = new PHPMailer();           //实例化PHPMailer对象
        $mail->CharSet = 'UTF-8';           //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
        $mail->IsSMTP();                    // 设定使用SMTP服务
        $mail->SMTPDebug = 0;               // SMTP调试功能 0=关闭 1 = 错误和消息 2 = 消息
        $mail->SMTPAuth = true;             // 启用 SMTP 验证功能
        $mail->SMTPSecure = $data['email_secure'];          // 使用安全协议
        $mail->Host = $data['email_smtp']; // SMTP 服务器
        $mail->Port = $data['email_port'];    // SMTP服务器的端口号
        $mail->Username = $data['email_usr'];    // SMTP服务器用户名
        $mail->Password = $data['email_pwd'];     // SMTP服务器密码
        $mail->SetFrom($data['email_usr'], 'LightPicture');
        $replyEmail = '';                   //留空则为发件人EMAIL
        $replyName = '';                    //回复名称（留空则为发件人名称）
        $mail->AddReplyTo($replyEmail, $replyName);
        $mail->Subject = $subject;

        $body = str_replace('[网站名称]', 'LightPicture', $email_template);
        $body = str_replace('[网站地址]', '', $body);
        $body = str_replace('[标题]', $subject, $body);
        $body = str_replace('[内容]', $content, $body);

        $mail->MsgHTML($body);
        $mail->AddAddress($tomail); //(收件地址,收件人名字)

        if($mail->Send()){
            // return $this->create([], '发送成功', 200);
        }else{
            throw new Exception($mail->ErrorInfo);
        }
    }
}
