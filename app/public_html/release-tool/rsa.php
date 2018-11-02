<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/11
 * Time: 17:00
 */

class Rsa {
    private $public_key = ''; //公密钥
    private $private_key = ''; //私密钥
    private $public_key_resource = ''; //公密钥资源
    private $private_key_resource = ''; //私密钥资源
    /**
     * 架构函数
     * @param [string] $public_key_file  [公密钥文件地址]
     * @param [string] $private_key_file [私密钥文件地址]
     */
    public function __construct( $public_key_file, $private_key_file ) {

        if ( $public_key_file && $private_key_file ) {
            $this->secret_key_file_check( $public_key_file, $private_key_file );
        }
    }

    //如果创建的类带参数，检查文件是否存在，并读取文件当中的公钥和密钥
    private function secret_key_file_check( $public_key_file, $private_key_file ) {
        try {
            if( ! file_exists( $public_key_file ) || ! file_exists( $private_key_file ) ) {
                throw new Exception( 'key file no exists' );
            }
            if ( false == ( $this->public_key = file_get_contents( $public_key_file ) ) || false == ( $this->private_key = file_get_contents( $private_key_file ) ) ) {
                throw new Exception( 'read key file fail' );
            }
            if( false == ( $this->private_key_resource = $this->is_bad_private_key( $this->private_key ) ) ) {
                throw new Exception( 'private key no usable' );
            }
            if( false == ( $this->public_key_resource = $this->is_bad_public_key( $this->public_key ) ) ) {
                throw new Exception( 'public key  no usable' );
            }
        } catch ( Exception $e ) {
            die( $e->getMessage() );
        }
    }

    private function is_bad_public_key($public_key) {
        return openssl_pkey_get_public($public_key);
    }
    private function is_bad_private_key($private_key) {
        $private_key = openssl_pkey_get_private($private_key);
        return $private_key;
    }

    /**将密钥写入文件中
     * @param $file 文件名
     * @param $key 需要写入文件的密钥
     */
    private static function write_key_to_file( $file, $key ) {
        $file = fopen( $file, "w" ) or die( "Failed to create {$file}!" );
        fwrite( $file, $key );
        fclose( $file );
    }

    public function setPrivateKey( $key ) {
        $this->private_key = $key;
        try {
            if( false == ( $this->private_key_resource = $this->is_bad_private_key( $this->private_key ) ) ) {
                throw new Exception( 'private key  no usable' );
            }
        } catch ( Exception $e ) {
            die( $e->getMessage() );
        }
    }

    public function setPublicKey( $key ) {
        $this->public_key = $key;
        try {
            if( false == ( $this->public_key_resource = $this->is_bad_public_key( $this->public_key ) ) ) {
                throw new Exception( 'public key  no usable' );
            }
        } catch ( Exception $e ) {
            die( $e->getMessage() );
        }
    }

    public function getPublicKey() {
        return $this->public_key;
    }

    /**
     * 生成一对公私密钥 成功返回公私密钥文件名 失败返回false
     */
    public static function createKey( $config ) {
        $res = openssl_pkey_new( $config );
        if( $res == false ) return false;
        openssl_pkey_export( $res, $private_key, null, $config );
        $public_key = openssl_pkey_get_details( $res );
        $timestamp = time();

        $key_file = 'tmp/' . $timestamp . '.key';
//        $private_key_file = 'tmp/' . $timestamp . '_rsa.prt';
//        $public_key_file = 'tmp/' . $timestamp . '_rsa.pub';
        if ( ! file_exists( 'tmp' ) ) mkdir( 'tmp' );
        self::write_key_to_file( $key_file, $private_key . $public_key[ 'key' ] );
//        self::write_key_to_file( $private_key_file, $private_key );
//        self::write_key_to_file( $public_key_file, $public_key[ 'key' ] );
//        return array( $timestamp . '_rsa.prt', $timestamp . '_rsa.pub');

        return $timestamp . '.key';
    }

    /**
     * 用私密钥加密 将数据拆分成一块一块的再加密，组合到一起
     */
    public function privateEncrypt( $input ) {
        $split = str_split( $input, 117 ); // 1024/8-11
        $output = "";
        foreach ( $split as $chunk ) {
            $encrypt_data = "";
            if( false == openssl_private_encrypt( $chunk, $encrypt_data, $this->private_key_resource ) ) {
                return false;
            }
            $output .= base64_encode( $encrypt_data );
        }
        return $output;
    }

    /**
     * 解密 私密钥加密后的密文
     */
    public function publicDecrypt( $input ) {
        $split = str_split( $input, 172 ); //调用base64_encode()后固定172位
        $output = "";
        foreach ( $split as $chunk ) {
            $decrypt_data = "";
            if( false == openssl_public_decrypt( base64_decode( $chunk ), $decrypt_data, $this->public_key_resource ) ) {
                return false;
            }
            $output .= $decrypt_data;
        }
        return $output;
    }
}

