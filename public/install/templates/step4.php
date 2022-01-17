<!doctype html>
<html>
<!-- 
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
-->
<head>
  <meta charset="UTF-8" />
  <title><?php echo $Title; ?> - <?php echo $Powered; ?></title>
  <link rel="stylesheet" href="./css/install.css" />
  <script src="./js/jquery.js?v=9.0"></script>
</head>

<body>
  <div class="wrap">
    <?php require './templates/header.php'; ?>
    <section class="section">
      <div class="step">
        <ul>
          <li class="on"><em>1</em>检测环境</li>
          <li class="on"><em>2</em>创建数据</li>
          <li class="current"><em>3</em>完成安装</li>
        </ul>
      </div>
      <div class="install" id="log">
        <?php if ($flag) { ?>
          <div class="success_tip cc"> <a href="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . '/'; ?>" class="f16 b">安装完成，点击进入</a>
            <p>为了您站点的安全，安装完成后即可将网站根目录"public"下的“install”文件夹删除。
            </p>
            <p style="color: red">默认管理员账号：admin</p>
            <p style="color: red">默认管理员密码：123456</p>
          </div>
          <div class="bottom tac">
            <a href="<?php echo 'http://' . $_SERVER['HTTP_HOST']; ?>" class="btn">立即进入</a>
          </div>


        <?php } else { ?>
          <ul id="loginner">
            <?php echo $msg ?>
            <p style="margin-top:30px">可能出现的原因：</p>
            <p>① 数据库非纯净数据库有残留数据</p>
            <p>② 数据库版本过低或过高</p>
            <p>② 数据库类型不匹配</p>
          </ul>
        <?php } ?>
      </div>
      <div class="bottom tac">
      </div>
    </section>

  </div>
  <?php require './templates/footer.php'; ?>
</body>

</html>