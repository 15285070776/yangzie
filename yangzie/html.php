<?php

namespace yangzie;

class YZE_Form extends YZE_Object {
    private $form_name;
    private $model;
    private $method = "post";
    private $acl;
    private $view;
    private $is_delete_form;
    public function __construct(YZE_View_Adapter $view, $form_name, YZE_Model $model = null, $is_delete = false) {
        $this->form_name = $form_name;
        $this->model = $model;
        $this->view = $view;
        $this->acl = YZE_ACL::get_instance ();
        $this->is_delete_form = $is_delete;
    }
    public function begin_form(array $attrs = array(), $is_upload_form = false) {
        ob_start ();
        $name = $this->form_name;
        $model = $this->model;
        $html = $modify = '';
        foreach ( $attrs as $n => $value ) {
            $html .= "$n = '$value' ";
        }
        $token = yze_request_token ();
        if ($model) {
            $modify = "<input type='hidden' name='yze_modify_version' value='" . $model->get_version_value () . "'/>
					<input type='hidden' name='yze_model_id' value='" . $model->get_key () . "'/>
					<input type='hidden' name='yze_model_name' value='" . get_class ( $model ) . "'/>
					<input type='hidden' name='yze_module_name' value='" . $model->get_module_name () . "'/>
					<input type='hidden' name='yze_method' value='" . ($this->is_delete_form ? "delete" : "put") . "'/>";
        }
        echo "<form name='$name' method='{$this->method}' $html " . ($is_upload_form ? 'enctype="multipart/form-data"' : '') . ">
		<input type='hidden' name='yze_request_token' value='{$token}'/>
		$modify";
    }
    public function end_form() {
        echo '</form>';
        $form = ob_get_clean ();
        
        $aro = do_filter ( YZE_FILTER_GET_USER_ARO_NAME, array (
                "aro" => "/" 
        ) );
        $aroname = $aro ['aro'];
        
        if ($this->acl->check_byname ( $aroname, $this->form_name )) {
            echo $form;
        }
    }
}
function yze_request_token() {
    return YZE_Session_Context::get_instance ()->get_request_token ( get_class ( YZE_Request::get_instance ()->controller () ) );
}

/**
 * 显示给定的视图并停止执行
 *
 * @param YZE_View_Adapter $view            
 * @param YZE_Resource_Controller $controller            
 */
function yze_die(YZE_View_Adapter $view, YZE_Resource_Controller $controller) {
    $layout = new YZE_Layout ( "error", $view, $controller );
    $layout->output ();
    die ( 0 );
}

/**
 * 取得一个对象的默认值，如果name有缓存（表单提交失败）取缓存的值；如果对象存在
 * 取对象的值，其它返回空。uri为空表示当前请求uri
 *
 * @author leeboo
 *        
 * @param YZE_Model $object            
 * @param unknown $name            
 * @param string $controller
 *            处理的控制器
 * @return int $index 如果name是数据，则表示数组的索引
 *        
 * @return
 *
 */
function yze_get_default_value($object, $name, $controller, $index = null) {
    $controller_name = get_class ( $controller );
    if (YZE_Session_Context::post_cache_has ( $name, $controller_name )) {
        $cache_data = YZE_Session_Context::get_cached_post ( $name, $controller_name );
        if (is_array ( $cache_data )) {
            return @$cache_data [$index];
        }
        return $cache_data;
    }
    if ($object && is_array ( $object )) {
        return @$object [$name];
    }
    
    if ($object) {
        return $object->get ( $name );
    }
    return "";
}

/**
 * 返回当前控制器的出错信息
 *
 * @author leeboo
 *        
 * @param $begin_tag 每条错误消息的开始html标签
 * @param $end_tag 每条错误消息的结束html标签
 * 
 * @return string
 *
 */
function yze_controller_error($begin_tag = null, $end_tag = null) {
    $session = YZE_Session_Context::get_instance ();
    $controller = YZE_Request::get_instance ()->controller ();
    
    if (($exception = $session->get_controller_exception ( get_class ( $controller ) ))) {
        if(is_a($exception, "\\yangzie\\YZE_Request_Validate_Failed")){
            $err = "";
            foreach ($exception->get_validater()->get_result() as $msg){
                $err .= $begin_tag . $msg . $end_tag;
            }
            return $err;
        }
        
        return $begin_tag . $exception->getMessage() . $end_tag;
    }
}

/**
 * 取得表单字段的验证错误消息
 */
function yze_form_field_error(YZE_Resource_Controller $controller,  $field_name) {
    $datas = YZE_Session_Context::get_instance ()->get_controller_validates ( get_class ( $controller ) );

    return @ $datas [$field_name];
}

/**
 * 把传入的文件压缩成一个文件后返回该文件的uri，比如把所有的css文件压缩成一个；
 * js文件压缩成一个。该api会考虑缓存，如果所传入的文件没有变化，则直接返回之前压缩的文件
 * 压缩的文件存放在YZE_APP_CACHES_PATH / compressed 中， 缓存文件的命名及内容依赖于传入的文件
 * 顺序。
 *
 * 该api参数是可变参数，传入每个文件的操作系统绝对路径。
 * 调用方法 yze_output_compressed_file("/path/to/file/one.css", "/path/to/file/two.css");
 *
 *
 * @author leeboo
 *        
 *        
 * @return string 压缩文件的uri
 */
function yze_compress_file() {
    $num_args = func_num_args ();
    if (! $num_args)
        return;
    
    $cache_name = "";
    $version = "";
    $cache_content = "";
    yze_make_dirs ( YZE_APP_CACHES_PATH . "compressed" );
    
    for($i = 0; $i < $num_args; $i ++) {
        $file_name = func_get_arg ( $i );
        if (! is_file ( $file_name ))
            continue;
        
        $cache_name .= $file_name;
        $version .= filemtime ( $file_name );
    }
    if (! $cache_name)
        return;
    
    $ext = pathinfo ( $file_name, PATHINFO_EXTENSION );
    $cache_name = YZE_APP_CACHES_PATH . "compressed/" . md5 ( $cache_name ) . "-" . md5 ( $version ) . "." . $ext;
    
    if (yze_isfile ( $cache_name ))
        return yze_remove_abs_path ( $cache_name ); // not changed
    
    for($i = 0; $i < $num_args; $i ++) {
        $cache_content .= file_get_contents ( func_get_arg ( $i ) ) . "\n";
    }
    
    // 删除之前的缓存文件，如果有的话
    foreach ( glob ( YZE_APP_CACHES_PATH . "compressed/" . md5 ( $cache_name ) . "-*." . $ext ) as $old ) {
        @unlink ( YZE_APP_CACHES_PATH . "compressed/" . $old );
    }
    
    file_put_contents ( $cache_name, $cache_content );
    return yze_remove_abs_path ( $cache_name );
}
?>