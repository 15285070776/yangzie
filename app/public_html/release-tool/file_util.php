<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/15
 * Time: 15:52
 */

//define("PUBLIC_HTML_PATH", dirname(dirname(__FILE__)) . "/");
define("PUBLIC_HTML_PATH", dirname(dirname(dirname(dirname(__FILE__)))) . "/test/app/public_html/");
class FileUtil {

    /**
     * 建立文件夹
     * @param string $aim_url
     * @return bool
     */
    public static function createDir($aim_url) {
        $aim_url = str_replace('', '/', $aim_url);
        $aim_dir = '';
        $arr = explode('/', $aim_url);
        $result = true;
        foreach ($arr as $str) {
            $aim_dir .= $str . '/';
            if (!file_exists($aim_dir)) {
                $result = mkdir($aim_dir);
            }
        }
        return $result;
    }

    /**
     * 删除文件夹
     *
     * @param string $aim_dir
     * @return boolean
     */
    public static function unlinkDir($aim_dir) {
        $aim_dir = str_replace('', '/', $aim_dir);
        $aim_dir = substr($aim_dir, -1) == '/' ? $aim_dir : $aim_dir . '/';
        if (!is_dir($aim_dir)) {
            return false;
        }
        $dir_handle = opendir($aim_dir);
        while (false !== ($file = readdir($dir_handle))) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (!is_dir($aim_dir . $file)) {
                self::unlinkFile($aim_dir . $file);
            } else {
                self::unlinkDir($aim_dir . $file);
            }
        }
        closedir($dir_handle);
        return rmdir($aim_dir);
    }

    /**
     * 删除文件
     *
     * @param string $aim_url
     * @return boolean
     */
    public static function unlinkFile($aim_url) {
        if (file_exists($aim_url)) {
            unlink($aim_url);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 复制文件夹
     *
     * @param string $old_dir
     * @param string $aim_dir
     * @param boolean $over_write 该参数控制是否覆盖原文件
     * @return boolean
     */
    public static function copyDir($old_dir, $aim_dir, $over_write = false) {
        $aim_dir = str_replace('', '/', $aim_dir);
        $aim_dir = substr($aim_dir, -1) == '/' ? $aim_dir : $aim_dir . '/';
        $old_dir = str_replace('', '/', $old_dir);
        $old_dir = substr($old_dir, -1) == '/' ? $old_dir : $old_dir . '/';
        if (!is_dir($old_dir)) {
            return false;
        }
        if (!file_exists($aim_dir)) {
            self::createDir($aim_dir);
        }
        $dir_handle = opendir($old_dir);
        while (false !== ($file = readdir($dir_handle))) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (!is_dir($old_dir . $file)) {
                //替换文件时跳过app/public_html/index.php
                $old_dir_temp = str_replace('\\', '/', $aim_dir);
                $aim_dir_temp = str_replace('\\', '/', PUBLIC_HTML_PATH);
                if ($old_dir_temp . $file != $aim_dir_temp . 'index.php')
                    self::copyFile($old_dir . $file, $aim_dir . $file, $over_write);
            } else {
                self::copyDir($old_dir . $file, $aim_dir . $file, $over_write);
            }
        }
        return closedir($dir_handle);
    }

    /**
     * 复制文件
     *
     * @param string $file_url
     * @param string $aim_url
     * @param boolean $over_write 该参数控制是否覆盖原文件
     * @return boolean
     */
    public static function copyFile($file_url, $aim_url, $over_write = false) {
        if (!file_exists($file_url)) {
            return false;
        }
        if (file_exists($aim_url) && $over_write == false) {
            return false;
        } elseif (file_exists($aim_url) && $over_write == true) {
            self::unlinkFile($aim_url);
        }
        $aim_dir = dirname($aim_url);
        self::createDir($aim_dir);
        copy($file_url, $aim_url);
        return true;
    }

    /**
     * 读文件
     * @param string $file_url
     * @return content 文件文本内容
     */
    public static function readFile( $file_url ) {
        if ( ! file_exists( $file_url ) ) {
            return false;
        }
        $file = fopen( $file_url, "r" );
        if ( ! $file ) {
            return false;
        }
        $file_content = fread( $file, filesize( $file_url ) );
        fclose( $file );
        return $file_content;
    }

}