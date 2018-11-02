<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/26
 * Time: 9:22
 */

//为了导入init.php文件，进行工作目录转换
$oldcwd = getcwd();
chdir("../");
require_once "init.php"; //导入init.php为了使用__config__.php里面的配置
chdir($oldcwd);

require_once "file_util.php";
require_once "rsa.php";
require_once "keys.php";

define( "OPENSSL_CNF_PATH", YZE_APP_PATH . "public_html" . DS . "release-tool" . DS); //生成密钥需要的openssl.cnf的路径
define( "VERSION_INI", "version.ini" ); //上传压缩包必须的version.ini文件名
define( "CODE_TEMP_DIR", "code_temp"); //解压文件临时保存的目录
define( "TEMP_DIR", "tmp/"); //临时文件目录

if ( ! file_exists( TEMP_DIR ) ) {
    mkdir( TEMP_DIR );
}