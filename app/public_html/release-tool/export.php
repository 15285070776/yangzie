<?php
/**
 * 将日志文件从指定的路径拷贝到当前路径下，然后导出
 * @author zhangwengang
 * Date: 2018/10/17
 * Time: 16:05
 */

require_once "file_util.php";
require_once "rsa.php";
require_once "keys.php";
define( "YZE_LOG_PATH", "C:\wamp64\logs" );
define( "MYSQL_BIN_PATH", "C:\wamp64\bin\mysql\mysql5.7.14\bin\\" ); //mysql的bin目录，将会使用mysqldump备份文件以及使用mysql更新数据库
define( "YZE_MYSQL_USER",  "root" );
define( "YZE_MYSQL_DB",  "test" );
define( "YZE_MYSQL_PASS",  "ydhl" );

$action = $_GET[ "action" ];

function export_file ($filename, $tmp_file_name) {
    echo "文件导出中......<br>";
    if ( file_exists( $filename ) ) {
        if ( FileUtil::copyFile( $filename, "./tmp/" . $tmp_file_name, true ) ) {
            header( "Content-type: text/html; charset=utf-8" );
            header( 'location:' . "./tmp/" . $tmp_file_name );
        } else {
            die( $tmp_file_name . "拷贝失败<br>" );
        }
    } else {
        die( $tmp_file_name . "不存在<br>" );
    }
}

function export_db_file () {
    $file = date( "Ymd", time()) . ".sql";
    if ( ! file_exists( $file ) ) {
        echo "数据库备份中......";
        //数据库备份
        $exec = MYSQL_BIN_PATH . "mysqldump -u " . YZE_MYSQL_USER . " -p" . YZE_MYSQL_PASS . " " . YZE_MYSQL_DB . " > ./tmp/" . $file;
        $output = [];
        exec( $exec, $output, $ret_var );
        if ( $ret_var != 0 )
            die( "数据库备份失败！" );
        echo "数据库备份成功<br>";
    }
    header( "Content-type: text/html; charset=utf-8" );
    header( 'location:' . "./tmp/" . $file );
}

if ( $action == "export_log" ) {
    export_file( YZE_LOG_PATH . "/apache_error.log", "apache_error.log" );
} else if ( $action == "export_db" ) {
    $pass = trim( $_POST[ "db_password" ] );
    $private_key_file = $_FILES[ "private_key_file" ][ "tmp_name" ];
    if ( ! $pass ) {
        die( "db pass is empty!" );
    }
    if ( ! $private_key_file ) {
        die( "请选择私钥!");
    }
    $private_key = FileUtil::readFile( $private_key_file );
    if ( false == $private_key ) {
        die( "文件不存在或者文件读取错误" );
    }
    $rsa = new Rsa( null, null );
    $rsa->setPrivateKey( $private_key );
    $encrypt_pass = $rsa->privateEncrypt( $pass );
    $verify_success_flag = false;
    foreach ( $keys as $key ) {
        $key_temp = "-----BEGIN PUBLIC KEY-----\n";
        $key_temp .= $key;
        $key_temp .= "\n-----END PUBLIC KEY-----";
        $rsa->setPublicKey( $key_temp );
        $decrypted_pass = $rsa->publicDecrypt( $encrypt_pass );
        if ( $pass == $decrypted_pass ) {
            echo "密钥验证成功<br>";
            if ( $pass == YZE_MYSQL_PASS ) {
                $verify_success_flag = true;
                echo "密码验证正确<br>";
            } else {
                die( "密码错误" );
            }
            break;
        } else {
            die( "密钥验证错误" );
        }
    }
    if ( $verify_success_flag ) {
        export_db_file();
    }
}
