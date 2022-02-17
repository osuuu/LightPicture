-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2022-02-17 09:26:05
-- 服务器版本： 5.6.50-log
-- PHP 版本： 7.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `lightpicture`
--

-- --------------------------------------------------------

--
-- 表的结构 `osuu_code`
--

CREATE TABLE `osuu_code` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `osuu_images`
--

CREATE TABLE `osuu_images` (
  `id` int(11) UNSIGNED NOT NULL COMMENT 'ID',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `storage_id` int(11) NOT NULL COMMENT '存储桶ID',
  `name` varchar(500) NOT NULL COMMENT '图片名称',
  `size` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '图片大小(字节：b)',
  `path` varchar(500) NOT NULL COMMENT '保存路径',
  `mime` varchar(32) NOT NULL COMMENT '文件MIME类型',
  `url` varchar(500) NOT NULL COMMENT '保存路径',
  `ip` varchar(128) DEFAULT NULL COMMENT '上传者IP',
  `create_time` int(11) NOT NULL COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='图片表';

-- --------------------------------------------------------

--
-- 表的结构 `osuu_log`
--

CREATE TABLE `osuu_log` (
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL COMMENT '用户id',
  `type` int(11) DEFAULT '2',
  `content` varchar(255) NOT NULL COMMENT '操作内容',
  `operate_id` varchar(255) DEFAULT NULL,
  `operate_cont` varchar(255) DEFAULT NULL,
  `create_time` int(13) NOT NULL COMMENT '时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `osuu_log`
--

INSERT INTO `osuu_log` (`id`, `uid`, `type`, `content`, `operate_id`, `operate_cont`, `create_time`) VALUES
(454, 1, 3, '登录了系统', '陕西省', '111.18.248.91', 1642240224);

-- --------------------------------------------------------

--
-- 表的结构 `osuu_role`
--

CREATE TABLE `osuu_role` (
  `id` int(11) NOT NULL COMMENT 'ID',
  `storage_id` int(32) NOT NULL COMMENT '存储桶ID',
  `name` varchar(32) NOT NULL COMMENT '组名称',
  `is_add` int(11) NOT NULL DEFAULT '0' COMMENT '上传权限',
  `is_del_own` int(11) NOT NULL DEFAULT '0' COMMENT '删除自己上传的图片',
  `is_read` int(11) NOT NULL COMMENT '查看所在存储桶其他人上传的图片',
  `is_del_all` int(11) NOT NULL DEFAULT '0' COMMENT '删除所在存储桶其他人上传的图片',
  `is_read_all` int(11) NOT NULL COMMENT '查看系统全部图片',
  `is_admin` int(11) NOT NULL COMMENT '管理员权限',
  `default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '默认',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `create_time` int(11) DEFAULT NULL COMMENT '添加时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文件夹表';

--
-- 转存表中的数据 `osuu_role`
--

INSERT INTO `osuu_role` (`id`, `storage_id`, `name`, `is_add`, `is_del_own`, `is_read`, `is_del_all`, `is_read_all`, `is_admin`, `default`, `update_time`, `create_time`) VALUES
(1, 1000, '超级管理员', 0, 0, 0, 0, 0, 1, 1, 1642174227, 0);

-- --------------------------------------------------------

--
-- 表的结构 `osuu_storage`
--

CREATE TABLE `osuu_storage` (
  `id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL COMMENT '类型',
  `name` varchar(255) DEFAULT NULL COMMENT '名称',
  `space_domain` varchar(255) DEFAULT NULL COMMENT '空间域名',
  `AccessKey` varchar(255) DEFAULT NULL COMMENT 'AccessKey  secretId',
  `SecretKey` varchar(255) DEFAULT NULL COMMENT 'SecretKey',
  `region` varchar(255) DEFAULT NULL COMMENT '所属地域',
  `bucket` varchar(255) DEFAULT NULL COMMENT '空间名称'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `osuu_storage`
--

INSERT INTO `osuu_storage` (`id`, `type`, `name`, `space_domain`, `AccessKey`, `SecretKey`, `region`, `bucket`) VALUES
(1000, 'local', '本地存储桶', NULL, NULL, NULL, NULL, '空间名称');

-- --------------------------------------------------------

--
-- 表的结构 `osuu_system`
--

CREATE TABLE `osuu_system` (
  `id` int(11) NOT NULL,
  `key` varchar(32) NOT NULL,
  `attr` varchar(32) DEFAULT NULL COMMENT '类型',
  `type` varchar(32) DEFAULT NULL COMMENT '分类',
  `title` varchar(255) DEFAULT NULL COMMENT '标题',
  `des` varchar(255) DEFAULT NULL COMMENT '描述',
  `value` text COMMENT '值',
  `extend` text COMMENT '选项'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `osuu_system`
--

INSERT INTO `osuu_system` (`id`, `key`, `attr`, `type`, `title`, `des`, `value`, `extend`) VALUES
(1, 'email_smtp', 'input', 'email', 'SMTP服务器', NULL, '', NULL),
(2, 'email_port', 'input', 'email', 'SMTP端口', NULL, '', NULL),
(3, 'email_secure', 'radio', 'email', 'SMTP协议', NULL, 'ssl', '{\"0\":\"ssl\",\"1\":\"tls\"}'),
(4, 'email_usr', 'input', 'email', '邮箱账号', NULL, '', NULL),
(5, 'email_pwd', 'input', 'email', '邮箱密码', NULL, '', NULL),
(6, 'email_template', 'text', 'email_template', '发件模板', NULL, '<html lang=\"zh\"><head><meta http-equiv=\"Content-Type\"content=\"text/html;charset=utf-8\"/><style>.open_email{background:url(http:width:760px;padding:10px;font-family:Tahoma,\"宋体\";margin:0 auto;margin-bottom:20px;text-align:left;margin-left:8px;margin-top:8px;margin-bottom:8px;margin-right:8px}.open_email a:link,.open_email a:visited{color:#295394;text-decoration:none!important}.open_email a:active,.open_email a:hover{color:#000;text-decoration:underline!important}.open_email h5,.open_email h6{font-size:14px;margin:0;padding-top:2px;line-height:21px}.open_email h5{color:#df0202;padding-bottom:10px}.open_email h6{padding-bottom:2px}.open_email h5 span,.open_email p{font-size:12px;color:#808080;font-weight:normal;margin:0;padding:0;line-height:21px}</style><title></title></head><body><div align=\"center\"><div class=\"open_email\"><div style=\"box-sizing:border-box;text-align:center;min-width:320px; max-width:660px; border:1px solid #f6f6f6; background-color:#f7f8fa; margin:auto; padding:20px 0 30px;\"><table style=\"width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse\"><tbody><tr style=\"font-weight:300\"><td style=\"width:3%;max-width:30px;\"></td><td style=\"max-width:600px;\"><p style=\"height:2px;background-color: #00a4ff;border: 0;font-size:0;padding:0;width:100%;margin-top:20px;\"></p><div id=\"cTMail-inner\"style=\"background-color:#fff; padding:23px 0 20px;box-shadow: 0px 1px 1px 0px rgba(122, 55, 55, 0.2);text-align:left;\"><table style=\"width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse;text-align:left;\"><tbody><tr style=\"font-weight:300\"><td style=\"width:3.2%;max-width:30px;\"></td><td style=\"max-width:480px;text-align:left;\"><h1 style=\"font-weight:bold;font-size:20px; line-height:36px; margin:0 0 16px;\">[标题]</h1><p class=\"cTMail-content\"style=\"font-size: 14px; color: rgb(51, 51, 51); line-height: 24px; margin: 0px 0px 36px; word-wrap: break-word; word-break: break-all;\">[内容]</p><dl style=\"font-size:14px;color:#333; line-height:18px;\"></dl><p id=\"cTMail-sender\"style=\"color:#333;font-size:14px; line-height:26px; word-wrap:break-word; word-break:break-all;margin-top:32px;\">此致<br/><strong>[网站名称]团队</strong><a href=\"[网站地址]\">查看更多</a></p></td><td style=\"width:3.2%;max-width:30px;\"></td></tr></tbody></table></div><div id=\"cTMail-copy\"style=\"text-align:center; font-size:12px; line-height:18px; color:#999\"><table style=\"width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse\"><tbody><tr style=\"font-weight:300\"><td style=\"width:3.2%;max-width:30px;\"></td><td style=\"max-width:540px;\"><p style=\"text-align:center; margin:20px auto 14px auto;font-size:12px;color:#999;\">此为系统邮件，请勿回复。</p></td><td style=\"width:3.2%;max-width:30px;\"></td></tr></tbody></table></div></td><td style=\"width:3%;max-width:30px;\"></td></tr></tbody></table></div></div></div></body></html>', NULL),
(7, 'is_reg', 'switch', 'basics', '开放注册', '是否开启网站前台注册', '1', NULL),
(9, 'init_quota', 'number', 'basics', '用户初始配额/GB', NULL, '10', NULL),
(10, 'upload_max', 'number', 'basics', '上传最大尺寸/MB', NULL, '50', NULL),
(12, 'upload_rule', 'input', 'basics', '允许上传后缀', NULL, 'jpg,jpeg,gif,png,ico,svg', NULL),
(13, 'is_show_storage', 'switch', 'basics', '展示存储桶', '向非管理员用户展示存储桶列表', '1', NULL),
(14, 'is_show_role', 'switch', 'basics', '展示角色组', '向非管理员用户展示角色组列表', '1', NULL),
(15, 'is_show_member', 'switch', 'basics', '展示团队成员', '向非管理员用户展示团队成员列表', '1', NULL);

-- --------------------------------------------------------

--
-- 表的结构 `osuu_user`
--

CREATE TABLE `osuu_user` (
  `id` int(11) UNSIGNED NOT NULL,
  `role_id` int(11) NOT NULL DEFAULT '0' COMMENT '角色ID',
  `username` varchar(32) NOT NULL COMMENT '用户名',
  `phone` varchar(100) DEFAULT NULL COMMENT '联系电话',
  `email` varchar(100) NOT NULL COMMENT '邮箱',
  `password` varchar(100) NOT NULL COMMENT '密码',
  `avatar` varchar(500) DEFAULT NULL COMMENT '头像',
  `capacity` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '可用配额容量(字节：b)',
  `state` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0停用 1启用 2待审核',
  `Secret_key` varchar(32) DEFAULT NULL COMMENT 'API秘钥',
  `reg_ip` varchar(128) DEFAULT NULL COMMENT '注册IP',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  `create_time` int(11) NOT NULL COMMENT '添加时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户表';

--
-- 转存表中的数据 `osuu_user`
--

INSERT INTO `osuu_user` (`id`, `role_id`, `username`, `phone`, `email`, `password`, `avatar`, `capacity`, `state`, `Secret_key`, `reg_ip`, `delete_time`, `update_time`, `create_time`) VALUES
(1, 1, '管理员', '', 'admin', '7c4a8d09ca3762af61e59520943dc26494f8941b', 'https://oss.aliyuncs.com/aliyun_id_photo_bucket/default_trade.jpg', '1073741824.00', 1, '7c63b59c638aa4a9e98144d9d929c18e', '', NULL, 1642174012, 1639712987);

--
-- 转储表的索引
--

--
-- 表的索引 `osuu_code`
--
ALTER TABLE `osuu_code`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `osuu_images`
--
ALTER TABLE `osuu_images`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `osuu_log`
--
ALTER TABLE `osuu_log`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `osuu_role`
--
ALTER TABLE `osuu_role`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `osuu_storage`
--
ALTER TABLE `osuu_storage`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `osuu_system`
--
ALTER TABLE `osuu_system`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `key` (`key`) USING BTREE;

--
-- 表的索引 `osuu_user`
--
ALTER TABLE `osuu_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `osuu_code`
--
ALTER TABLE `osuu_code`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- 使用表AUTO_INCREMENT `osuu_images`
--
ALTER TABLE `osuu_images`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID', AUTO_INCREMENT=1284;

--
-- 使用表AUTO_INCREMENT `osuu_log`
--
ALTER TABLE `osuu_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=455;

--
-- 使用表AUTO_INCREMENT `osuu_role`
--
ALTER TABLE `osuu_role`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID', AUTO_INCREMENT=32;

--
-- 使用表AUTO_INCREMENT `osuu_storage`
--
ALTER TABLE `osuu_storage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1043;

--
-- 使用表AUTO_INCREMENT `osuu_system`
--
ALTER TABLE `osuu_system`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- 使用表AUTO_INCREMENT `osuu_user`
--
ALTER TABLE `osuu_user`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
