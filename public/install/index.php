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
define('PHP_EDITION', '7.2.0');
define('APP_DIR', _dir_path(substr(dirname(__FILE__), 0, -15))); //项目目录
define('SITE_DIR', _dir_path(substr(dirname(__FILE__), 0, -8))); //入口文件目录

if (file_exists('./install.lock')) {
    echo '
		<html>
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        </head>
        <body>
        	你已经安装过该系统，如果想重新安装，请先删除install目录下的 install.lock 文件，然后再安装。
        </body>
        </html>';
    exit;
}
@set_time_limit(1000);
$sqlFile = 'lp.sql';
$configFile = '.env';
if (!file_exists(SITE_DIR . 'install/' . $sqlFile) || !file_exists(SITE_DIR . 'install/' . $configFile)) {
    echo '缺少必要的安装文件!';
    exit;
}
if (PHP_EDITION > phpversion()) {
    header("Content-type:text/html;charset=utf-8");
    exit('您的php版本过低，不能安装本软件，请升级到' . PHP_EDITION . '或更高版本再安装，谢谢！');
}
if (phpversion() > '7.6') {
    header("Content-type:text/html;charset=utf-8");
    exit('您的php版本太高，不能安装本软件，兼容php版本7.2~7.4，谢谢！');
}
date_default_timezone_set('PRC');
error_reporting(E_ALL & ~E_NOTICE);
header('Content-Type: text/html; charset=UTF-8');

$Title = "LightPicture安装向导";
$Powered = "LightPicture";
$steps = array(
    '2' => '运行环境检测',
    '3' => '安装参数设置',
    '4' => '安装详细过程',
    '5' => '安装完成',
);
$step = isset($_GET['step']) ? $_GET['step'] : 2;
switch ($step) {

   
    case '2':

        if (phpversion() <= PHP_EDITION) {
            die('本系统需要PHP版本 >= ' . PHP_EDITION . '环境，当前PHP版本为：' . phpversion());
        }

        $phpv = @phpversion();
        $os = PHP_OS;
        //$os = php_uname();
        $tmp = function_exists('gd_info') ? gd_info() : array();
        $server = $_SERVER["SERVER_SOFTWARE"];
        $host = (empty($_SERVER["SERVER_ADDR"]) ? $_SERVER["SERVER_HOST"] : $_SERVER["SERVER_ADDR"]);
        $name = $_SERVER["SERVER_NAME"];
        $max_execution_time = ini_get('max_execution_time');
        $allow_reference = (ini_get('allow_call_time_pass_reference') ? '<font color=green>[√]On</font>' : '<font color=red>[×]Off</font>');
        $allow_url_fopen = (ini_get('allow_url_fopen') ? '<font color=green>[√]On</font>' : '<font color=red>[×]Off</font>');
        $safe_mode = (ini_get('safe_mode') ? '<font color=red>[×]On</font>' : '<font color=green>[√]Off</font>');

        $err = 0;
        if (empty($tmp['GD Version'])) {
            $gd = '<font color=red>[×]Off</font>';
            $err++;
        } else {
            $gd = '<font color=green>[√]On</font> ' . $tmp['GD Version'];
        }



        if (function_exists('mysqli_connect')) {
            $mysql = '<span class="correct_span">&radic;</span> 已安装';
        } else {
            $mysql = '<span class="correct_span error_span">&radic;</span> 请安装mysqli扩展';
            $err++;
        }
        if (ini_get('file_uploads')) {
            $uploadSize = '<span class="correct_span">&radic;</span> ' . ini_get('upload_max_filesize');
        } else {
            $uploadSize = '<span class="correct_span error_span">&radic;</span>禁止上传';
        }
        if (function_exists('session_start')) {
            $session = '<span class="correct_span">&radic;</span> 支持';
        } else {
            $session = '<span class="correct_span error_span">&radic;</span> 不支持';
            $err++;
        }
        if (function_exists('curl_init')) {
            $curl = '<font color=green>[√]支持</font> ';
        } else {
            $curl = '<font color=red>[×]不支持</font>';
            $err++;
        }

        if (function_exists('bcadd')) {
            $bcmath = '<font color=green>[√]支持</font> ';
        } else {
            $bcmath = '<font color=red>[×]不支持</font>';
            $err++;
        }
        if (function_exists('openssl_encrypt')) {
            $openssl = '<font color=green>[√]支持</font> ';
        } else {
            $openssl = '<font color=red>[×]不支持</font>';
            $err++;
        }



        $folder = array(
            'public/install',
            'public/LightPicture',
            'public/uploads',
            'runtime',
        );
        $file = array(
            '.env'
        );
        //必须开启函数
        if (function_exists('file_put_contents')) {
            $file_put_contents = '<font color=green>[√]开启</font> ';
        } else {
            $file_put_contents = '<font color=red>[×]关闭</font>';
            $err++;
        }
        if (function_exists('imagettftext')) {
            $imagettftext = '<font color=green>[√]开启</font> ';
        } else {
            $imagettftext = '<font color=red>[×]关闭</font>';
            $err++;
        }

        include_once("./templates/step2.php");
        exit();

    case '3':
        include_once("./templates/step3.php");
        exit();

    case '4':
        $dbHost = trim($_POST['dbhost']);
        $_POST['dbport'] = $_POST['dbport'] ? $_POST['dbport'] : '3306';
        $dbName = strtolower(trim($_POST['dbname']));
        $dbUser = trim($_POST['dbuser']);
        $dbPwd = trim($_POST['dbpw']);


        if (!function_exists('mysqli_connect')) {
            echo '<script>alert("请安装 mysqli 扩展!");history.go(-1)</script>';
            exit;
        }
        $conn = @mysqli_connect($dbHost, $dbUser, $dbPwd, NULL, $_POST['dbport']);
        if (mysqli_connect_errno($conn)) {
            echo '<script>alert("连接数据库失败");history.go(-1)</script>';
            exit();
        }
        mysqli_set_charset($conn, "utf8mb4"); //,character_set_client=binary,sql_mode='';


        if (!mysqli_select_db($conn, $dbName)) {
            //创建数据时同时设置编码
            if (!mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `" . $dbName . "` DEFAULT CHARACTER SET utf8mb4;")) {
                echo '<script>alert("数据库不存在");history.go(-1)</script>';
                exit;
            }
            mysqli_select_db($conn, $dbName);
        }
        //读取数据文件
        $sqldata = file_get_contents(SITE_DIR . 'install/' . $sqlFile);
        $sqlFormat = sql_split($sqldata, $dbPrefix);
        //创建写入sql数据库文件到库中 结束

        /**
         * 执行SQL语句
         */
        $counts = count($sqlFormat);
        $t = 0;
        $e = 0;
        for ($i = $n; $i < $counts; $i++) {
            $sql = trim($sqlFormat[$i]);
            if (trim($sql) == '')
                continue;
            if (mysqli_query($conn, $sql)) {
                ++$t;
            } else {
                $e++;
            }
        }
        if ($e == 0) {
            $flag = true;
            $msg = ('<div style="font-size:20px">安装成功！SQL成功' . $t . '句</div>');
        } else {
            $flag = false;
            $msg = ('<div style="font-size:20px;color:red">安装失败,建议参照教程手动安装<br/>SQL成功' . $t . '句/失败' . $e . '句</div>');
        }

        //读取配置文件，并替换真实配置数据1
        $strConfig = file_get_contents(SITE_DIR . 'install/' . $configFile);
        $strConfig = str_replace('#DB_HOST#', $dbHost, $strConfig);
        $strConfig = str_replace('#DB_NAME#', $dbName, $strConfig);
        $strConfig = str_replace('#DB_USER#', $dbUser, $strConfig);
        $strConfig = str_replace('#DB_PWD#', $dbPwd, $strConfig);
        $strConfig = str_replace('#DB_PORT#', $_POST['dbport'], $strConfig);
        $strConfig = str_replace('#DB_CHARSET#', 'utf8mb4', $strConfig);
        // $strConfig = str_replace('#DB_DEBUG#', false, $strConfig);


        @chmod(APP_DIR . '/.env', 0777); //数据库配置文件的地址
        @file_put_contents(APP_DIR . '/.env', $strConfig); //数据库配置文件的地址

        @touch('./install.lock');
        include_once("./templates/step4.php");
        exit();
}

function dir_create($path, $mode = 0777)
{
    if (is_dir($path))
        return TRUE;
    $ftp_enable = 0;
    $path = dir_path($path);
    $temp = explode('/', $path);
    $cur_dir = '';
    $max = count($temp) - 1;
    for ($i = 0; $i < $max; $i++) {
        $cur_dir .= $temp[$i] . '/';
        if (@is_dir($cur_dir))
            continue;
        @mkdir($cur_dir, 0777, true);
        @chmod($cur_dir, 0777);
    }
    return is_dir($path);
}

function dir_path($path)
{
    $path = str_replace('\\', '/', $path);
    if (substr($path, -1) != '/')
        $path = $path . '/';
    return $path;
}

function _dir_path($path)
{
    $path = str_replace('\\', '/', $path);
    if (substr($path, -1) != '/')
        $path = $path . '/';
    return $path;
}

//判断权限
function testwrite($d)
{
    if (is_file($d)) {
        if (is_writeable($d)) {
            return true;
        }
        return false;
    } else {
        $tfile = "_test.txt";
        $fp = @fopen($d . "/" . $tfile, "w");
        if (!$fp) {
            return false;
        }
        fclose($fp);
        $rs = @unlink($d . "/" . $tfile);
        if ($rs) {
            return true;
        }
        return false;
    }
}
function sql_split($sql, $tablepre)
{

    if ($tablepre != "tp_")
        $sql = str_replace("tp_", $tablepre, $sql);

    $sql = preg_replace("/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=utf8mb4", $sql);

    $sql = str_replace("\r", "\n", $sql);
    $ret = array();
    $num = 0;
    $queriesarray = explode(";\n", trim($sql));
    unset($sql);
    foreach ($queriesarray as $query) {
        $ret[$num] = '';
        $queries = explode("\n", trim($query));
        $queries = array_filter($queries);
        foreach ($queries as $query) {
            $str1 = substr($query, 0, 1);
            if ($str1 != '#' && $str1 != '-')
                $ret[$num] .= $query;
        }
        $num++;
    }
    return $ret;
}
