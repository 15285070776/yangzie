<?php
/**
 * 将日志文件从指定的路径拷贝到当前路径下，然后导出
 * @author zhangwengang
 * Date: 2018/10/17
 * Time: 16:05
 */

require_once "file_util.php";
define( "YZE_LOG_PATH", "C:\wamp64\logs" );

if ( file_exists( YZE_LOG_PATH . "/apache_error.log" ) ) {
    if ( FileUtil::copyFile( YZE_LOG_PATH . "/apache_error.log", "./tmp/apache_error.log", true ) ) {
        header( 'location:' . "./tmp/apache_error.log" );
    } else {
        die( "日志拷贝失败<br>" );
    }
} else {
    die( "日志文件不存在<br>" );
}