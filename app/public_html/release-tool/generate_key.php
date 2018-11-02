<?php
/**
 * 使用密钥生成和证书签名功能，需要在系统上安装一个可用的 openssl.cnf 文件。
 * win下必须要openssl.cof支持   liunx一般已自带安装
 *
 * 生成的密钥分为私钥和公钥两部分，公钥和私钥都需要单独保存到本地的文件中，用来生成version.ini 或者 导出数据库
 * 公钥的内容部分还需要保存到key.php中的key数组当中
 * @author zhangwengang
 * Date: 2018/10/12
 * Time: 10:18
 */

require_once "__config__.php";

$config = array(
    "digest_alg" => "sha512", //摘要算法或签名哈希算法，通常是 openssl_get_md_methods() 之一
    "private_key_bits" => 1024, //指定应该使用多少位来生成私钥
    "private_key_type" => OPENSSL_KEYTYPE_RSA, //加密类型
);
$config[ "config" ] = OPENSSL_CNF_PATH . "openssl.cnf";

$key = Rsa::createKey( $config ); //生成密钥

header( "Content-type: text/html; charset=utf-8" );
header( 'location:' . TEMP_DIR . $key );

die( json_encode( $key ) ); //返回生成的密钥文件名