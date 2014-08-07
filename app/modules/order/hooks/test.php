<?php
/**
 * 定义一些系统回调，需要定义的回调有：
 * YZE_FILTER_GET_USER_ARO_NAME: 返回用户的aro，默认为/
 * YZE_ACTION_CHECK_USER_HAS_LOGIN: 判断用户是否登录，没有登录请抛出异常
 * YZE_FILTER_YZE_EXCEPTION: 扬子鳄处理过程中出现的异常回调
 * 
 * @author leeboo
 *
 */
namespace app\order;
use yangzie\YZE_Redirect;

use \yangzie\YZE_Request;
use \yangzie\YZE_Hook;
use \yangzie\YZE_Session_Context;


YZE_Hook::add_hook(Order_Module::TEST_HOOK, function ($datas){
    $datas["foo"] = "hook from order bar";
    return $datas;
});
?>