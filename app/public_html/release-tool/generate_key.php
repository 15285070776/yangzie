<?php
/**
 * 使用密钥生成和证书签名功能，需要在系统上安装一个可用的 openssl.cnf 文件。
 * win下必须要openssl.cof支持   liunx一般已自带安装
 *
 * @author zhangwengang
 * Date: 2018/10/12
 * Time: 10:18
 */

require_once "rsa.php";
define( "OPENSSL_CNF_PATH", dirname( __FILE__ ) . "/" ); //openssl.cnf的路径

$config = array(
    "digest_alg" => "sha512", //摘要算法或签名哈希算法，通常是 openssl_get_md_methods() 之一
    "private_key_bits" => 1024, //指定应该使用多少位来生成私钥
    "private_key_type" => OPENSSL_KEYTYPE_RSA, //加密类型
);
$config[ "config" ] = OPENSSL_CNF_PATH . "openssl.cnf";

$key = Rsa::createKey( $config ); //生成密钥

die( json_encode( $key ) ); //返回生成的密钥文件名