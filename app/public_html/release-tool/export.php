<?php
/**
 * 将日志文件从指定的路径拷贝到当前路径下，然后导出
 * @author zhangwengang
 * Date: 2018/10/17
 * Time: 16:05
 */

require_once "__config__.php";

$action = $_GET[ "action" ];
function export_file ($filename, $tmp_file_name) {
    echo "文件导出中......<br>";
    if ( file_exists( $filename ) ) {
        if ( FileUtil::copyFile( $filename, TEMP_DIR . $tmp_file_name, true ) ) {
            header( "Content-type: text/html; charset=utf-8" );
            header( 'location:' . TEMP_DIR . $tmp_file_name );
        } else {
            die( $tmp_file_name . "拷贝失败<br>" );
        }
    } else {
        die( $tmp_file_name . "不存在<br>" );
    }
}

function export_db_file () {
    if ( ! YZE_MYSQL_BIN_PATH ) {
        die( "还没有配置mysql/bin的路径");
    }
    if ( ! file_exists( YZE_MYSQL_BIN_PATH ) ) {
        die( YZE_MYSQL_BIN_PATH . " 不存在" );
    }
    $file = date( "Ymd", time()) . ".sql";
    if ( ! file_exists( $file ) ) {
        echo "数据库备份中......<br>";
        //数据库备份
        $exec = YZE_MYSQL_BIN_PATH . "/mysqldump -u " . YZE_MYSQL_USER . " -p" . YZE_MYSQL_PASS . " " . YZE_MYSQL_DB . " > " . TEMP_DIR . $file;
        $output = [];
        exec( $exec, $output, $ret_var );
        if ( $ret_var != 0 )
            die( "数据库备份失败！" );
        echo "数据库备份成功<br>";
    }
    header( "Content-type: text/html; charset=utf-8" );
    header( 'location:' . TEMP_DIR . $file );
}

if ( $action == "export_log" ) {
    if ( ! YZE_LOG_PATH_NAME ) {
        die( "还没有配置日志文件路径" );
    }
    if ( ! file_exists( YZE_LOG_PATH_NAME ) ) {
        die( YZE_LOG_PATH_NAME . " 不存在" );
    }
    export_file( YZE_LOG_PATH_NAME, "apache_error.log" );
} else if ( $action == "export_db" ) {
    $pass = trim( $_POST[ "db_password" ] );
    $private_key_file = $_FILES[ "private_key" ][ "tmp_name" ];
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
        }
    }
    if ( $verify_success_flag ) {
        export_db_file();
    } else {
        die( "密钥验证错误" );
    }
}

?>
