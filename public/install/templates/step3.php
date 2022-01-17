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
</head>

<body>
  <div class="wrap">
    <?php require './templates/header.php'; ?>
    <section class="section">
      <div class="step">
        <ul>
          <li class="on"><em>1</em>检测环境</li>
          <li class="current"><em>2</em>创建数据</li>
          <li><em>3</em>完成安装</li>
        </ul>
      </div>
      <form id="J_install_form" action="index.php?step=4" method="post">
        <input type="hidden" name="step" value="4" />
        <input type="hidden" name="force" value="0" />
        <div class="server">
          <table width="100%">
            <tr>
              <td class="td1" width="100">MySQL配置</td>
              <td class="td1" width="200">&nbsp;</td>
              <td class="td1">&nbsp;</td>
            </tr>

            <tr>
              <td class="tar">数据库服务器：</td>
              <td><input type="text" name="dbhost" value="127.0.0.1" class="input"></td>
              <td>
                <div id="J_install_tip_dbhost"><span class="gray">数据库服务器地址，一般为127.0.0.1</span></div>
              </td>
            </tr>
            <tr>
              <td class="tar">数据库端口：</td>
              <td><input type="text" name="dbport" value="3306" class="input"></td>
              <td>
                <div id="J_install_tip_dbport"><span class="gray">数据库服务器端口，一般为3306</span></div>
              </td>
            </tr>
            <tr>
              <td class="tar">数据库用户名：</td>
              <td><input type="text" name="dbuser" value="root" class="input"></td>

            </tr>
            <tr>
              <td class="tar">数据库密码：</td>
              <td><input type="password" name="dbpw" value="" class="input" autoComplete="off"></td>

            </tr>
            <tr>
              <td class="tar">数据库名：</td>
              <td><input type="text" name="dbname" value="" class="input"></td>

            </tr>


          </table>

          <div id="J_response_tips" style="display:none;"></div>
        </div>
        <div class="bottom tac"> <a href="./index.php?step=2" class="btn">上一步</a>
            <button type="submit" class="btn btn_submit J_install_btn">创建数据</button>
        </div>
      </form>
    </section>
    <div style="width:0;height:0;overflow:hidden;"> <img src="./images/install/pop_loading.gif"> </div>

  </div>
  <?php require './templates/footer.php'; ?>
</body>

</html>