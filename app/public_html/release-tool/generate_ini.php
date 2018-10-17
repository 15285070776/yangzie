<?php
/**
 * 读取公钥和私钥文件的内容，将version.ini的内容使用私钥加密再保存到version.ini中
 * 生成version.ini文件 该文件格式为[version="版本号"   secret_key="public_key"]
 *
 * @author zhangwengang
 * Date: 2018/10/11
 * Time: 16:49
 */

require_once "rsa.php";

$version = trim( $_POST[ "version" ] );
$private_key_file = $_FILES[ "private_key_file" ][ "tmp_name" ];
$public_key_file = $_FILES[ "public_key_file" ][ "tmp_name" ];

if ( ! $version )
    die( "版本号不能为空" );
if ( ! $public_key_file )
    die( "请选择公钥" );
if ( ! $private_key_file )
    die( "请选择私钥" );

$rsa = new Rsa( $public_key_file, $private_key_file );
$public_key = $rsa->getPublicKey(); //获取公钥

//去掉公钥的开始和结尾格式 获取公钥的内容
$index_first = strpos( $public_key, "-----BEGIN PUBLIC KEY-----" ) + strlen( "-----BEGIN PUBLIC KEY-----" );
$index_last = strrpos( $public_key, "-----END PUBLIC KEY-----" );
$public_key = trim( substr( $public_key, $index_first, ( $index_last - $index_first ) ) );

//对version.ini的内容使用私钥进行加密
$secret_key = "secret_key=" . $public_key . "\n";
$version = "version=" . $version . "\n";
$secret_text = $rsa->privateEncrypt( $version . $secret_key );

//将加密后的内容写进文件
$file_version_ini = fopen( "tmp/version.ini", "w" ) or die( "Failed to generate version.ini!" );
fwrite( $file_version_ini, $secret_text );
fclose( $file_version_ini );

echo " success<br> ";

