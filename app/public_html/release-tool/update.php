<?php
/**
 * 上传文件有默认文件大小限制2M，需要修改apache\bin\php.ini 配置文件  upload_max_filesize 和 post_max_size选项的值
 * 执行数据库sql文件备份需要用到mysql\bin目录下的mysqldump命令，以及用mysql命令更新数据库
 * @author zhangwengang
 * Date: 2018/10/12
 * Time: 15:44
 */

require_once "__config__.php";

if ( ! $_FILES ) {
  die( "文件上传失败，请检查上传文件大小是否超过允许上传的最大值");
}
//判断文件是否上传错误
if ( $_FILES[ "new_code_zip" ][ "error" ] > 0 ) {
    die( "文件上传出错，错误代码：" . $_FILES[ "new_code_zip" ][ "error" ] );
}

$new_code_zip = $_FILES[ "new_code_zip" ][ "tmp_name" ];
$real_file_name = $_FILES[ "new_code_zip" ][ "name" ];
move_uploaded_file( $_FILES[ "new_code_zip" ][ "tmp_name" ], TEMP_DIR . $real_file_name );

//对压缩包进行解压缩
$zip = new ZipArchive;
if ( $zip->open( TEMP_DIR . $real_file_name ) === TRUE ) {//中文文件名要使用ANSI编码的文件格式
    FileUtil::unlinkDir( CODE_TEMP_DIR ); //删除文件夹及文件夹里的文件
    $zip->extractTo( CODE_TEMP_DIR );//提取全部文件到code_temp目录中
    $zip->close();
    unlink( TEMP_DIR . $real_file_name );
    echo "解压成功<br>";
} else {
    unlink( TEMP_DIR . $real_file_name );
    die( "解压失败" );
}

//读取version.ini文件的内容
if ( ! file_exists( CODE_TEMP_DIR . "/" . VERSION_INI ) ) {
    die( "not found file " . VERSION_INI );
}
echo "读取version.ini<br>";
$file_version_ini = fopen( CODE_TEMP_DIR . "/" . VERSION_INI, "r" ) or die( "Failed to open " . VERSION_INI . "!" );
$file_content = fread( $file_version_ini, filesize( CODE_TEMP_DIR . "/" . VERSION_INI ) );
fclose( $file_version_ini );

//对version.ini的内容使用公钥解密，判断公钥是否在允许的列表当中
$rsa = new Rsa( null, null );
echo "公钥验证中......<br>";
$verify_success_flag = false;
foreach ( $keys as $key ) {
    $key_temp = "-----BEGIN PUBLIC KEY-----\n";
    $key_temp .= $key;
    $key_temp .= "\n-----END PUBLIC KEY-----";
    $rsa->setPublicKey( $key_temp );
    $file_content_decrypted = $rsa->publicDecrypt( $file_content );
    //提取version.ini中的公钥部分内容
    $index_first = strrpos( $file_content_decrypted, "=" ) + 1;
    $index_last = strlen( $file_content_decrypted ) - 1;
    $secret_key = substr( $file_content_decrypted, $index_first, ( $index_last - $index_first ) );

    //去掉公钥中的回车、换行、空格、tab，判断公钥是否正确
    $key = str_replace( array( "\r", "\n", " ", "\t" ), "", $key );
    $secret_key = str_replace( array( "\r", "\n", " ", "\t" ), "", $secret_key );

    if ( $secret_key == $key ) {
        $verify_success_flag = true;
        echo "公钥匹配成功<br>";

        //数据库更新
        if ( file_exists( CODE_TEMP_DIR . "/" . "update.sql" ) ) {
            if ( ! YZE_MYSQL_BIN_PATH ) {
                die( "还没有配置mysql/bin路径" );
            }
            if ( ! file_exists( YZE_MYSQL_BIN_PATH ) ) {
                die( YZE_MYSQL_BIN_PATH . "不存在" );
            }
            //数据库备份
            echo "数据库备份中......<br>";
            $exec = YZE_MYSQL_BIN_PATH . "/mysqldump -u " . YZE_MYSQL_USER . " -p" . YZE_MYSQL_PASS . " " . YZE_MYSQL_DB . " > " . TEMP_DIR . date( "Ymd", time( ) ).".sql";
            $output = [];
            exec( $exec, $output, $ret_var );
            if ( $ret_var != 0 )
                die( "数据库备份失败！" );
            echo "数据库备份成功<br>";

            echo "数据库更新中......<br>";
            $exec = YZE_MYSQL_BIN_PATH . "/mysql -u " . YZE_MYSQL_USER . " -p" . YZE_MYSQL_PASS . " " . YZE_MYSQL_DB . " < " . CODE_TEMP_DIR . "/update.sql";
            exec( $exec, $output, $ret_var );
            if ( $ret_var != 0 )
                die( "数据库更新失败!" );
            echo "数据库更新成功<br>";
        }

        if ( false == FileUtil::copyFile( YZE_PUBLIC_HTML . "index.php", "index_dump.php", true )) {
            echo "index.php 文件备份失败<br>";
        }
        $file_is_exists = file_exists("index_replace.php");
        if ( $file_is_exists && false == FileUtil::copyFile( "index_replace.php", YZE_PUBLIC_HTML . "index.php", true ) ) {
            die( "index.php替换失败!" );
        } elseif ( $file_is_exists ) {
            echo "index.php替换成功<br>";
        }

        $file_is_exists = file_exists(CODE_TEMP_DIR . "/yangzie");
        if ( $file_is_exists && 0 != FileUtil :: copyDir( CODE_TEMP_DIR . "/yangzie", YZE_INSTALL_PATH . "yangzie", true ) ) {
            die( "yangzie框架更新失败" );
        } elseif ( $file_is_exists ) {
            echo "yangzie框架更新成功<br>";
        }

        $file_is_exists = file_exists(CODE_TEMP_DIR . "/app");
        if ( $file_is_exists && 0 != FileUtil :: copyDir( CODE_TEMP_DIR . "/app", YZE_INSTALL_PATH . "app", true ) ) {
            die( "app代码更新失败" );
        } elseif ( $file_is_exists ) {
            echo "app代码更新成功<br>";
        }

        if ( FileUtil::copyFile( CODE_TEMP_DIR . "/app/public_html/index.php", YZE_PUBLIC_HTML . "index.php", true ) ) {
            echo "index.php更新成功<br>";
        } else if ( FileUtil::copyFile( "index_dump.php", YZE_PUBLIC_HTML . "index.php", true ) ) {
            echo "index.php已还原<br>";
        } else {
            die( "index.php更新或还原失败" );
        }
    }
}
if ( ! $verify_success_flag ) {
    echo "公钥验证失败<br>";
}
echo "程序执行结束<br>";



