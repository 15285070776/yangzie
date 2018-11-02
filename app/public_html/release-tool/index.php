<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/11
 * Time: 15:53
 */
require_once "__config__.php";
?>
<html>
<head>
    <meta charset="utf-8">
<style type="text/css">
#box{
    position:absolute;
    top: 40%;
    left: 40%;
    padding: 20px;
    margin: 20px;
    border: 5px;
}
</style>
</head>
<body>
<div id="box">
    <div ><button class="action_button" onclick="exportLog()">导出日志</button></div>
    <div ><button class="action_button" id="generate_key" onclick="generateKey()">生成新密钥对</button></div>
    <div >
        <button class="action_button" id="generate_ini">使用现有密钥对生成version.ini</button>
        <form id="choose_secret_key" style="visibility: hidden" method="post" action="generate_ini.php" enctype="multipart/form-data">
            版本号：<input class="input_class" type="text" id="version" name="version">
            公钥：<input class="input_class" type="file" id="public_key_file" name="public_key_file">
            私钥：<input class="input_class" type="file" id="private_key_file" name="private_key_file">
            <input class="form_submit" type="button" value="提交" onclick="disabledButton('choose_secret_key')">
        </form>
    </div>
    <div >
        <button class="action_button" id="update_code">更新代码</button>
        <form id="choose_code_zip" style="visibility: hidden" method="post" action="update.php" enctype="multipart/form-data">
            压缩包：<input class="input_class" type="file" id="new_code_zip" name="new_code_zip">
            <input class="form_submit" type="button" value="提交" onclick="disabledButton('choose_code_zip')">
        </form>
    </div>
    <div >
        <button class="action_button" id="export_db">导出数据库</button>
        <form id="db_info" style="visibility: hidden" method="post" action="export.php?action=export_db" enctype="multipart/form-data">
            DB PASS：<input class="input_class" type="password" id="db_password" name="db_password">
            私钥：<input class="input_class" type="file" id="private_key" name="private_key">
            <input class="form_submit" type="button" value="提交" onclick="disabledButton('db_info')">
        </form>
    </div>
</div>

<script type="text/javascript">
    function generateKey() {
        window.location.href = "generate_key.php";
    }

    var generateIni = document.getElementById("generate_ini");
    generateIni.addEventListener("click", function () {
       document.getElementById("choose_secret_key").style.visibility = "visible";
    });

    document.getElementById("update_code").addEventListener("click", function () {
       document.getElementById("choose_code_zip").style.visibility = "visible";
    });
    
    document.getElementById("export_db").addEventListener("click", function () {
       document.getElementById("db_info").style.visibility = "visible";
    });

    function exportLog() {
        window.location.href = "export.php?action=export_log";
    }

    function disabledButton(form_id) {
        var actionButton = getElementsByClassName("action_button");
        var formSubmit = getElementsByClassName("form_submit");
        for (var i = 0; i < actionButton.length; i++) {
            actionButton[i].disabled = true;
        }
        for (var i = 0; i < formSubmit.length; i++) {
            formSubmit[i].disabled = true;
        }
        document.forms[form_id].submit();
    }

    /**
     * getElementByClassName兼容性
     * @param className 类名
     * @return HTMLCollectionOf<Collection> | Array
     */
    function getElementsByClassName(className) {
        if (document.getElementsByClassName)
            return document.getElementsByClassName(className);
        var result = [];
        var tags = obj.getElementsByTagName("*");
        var tagsLength = tags.length;
        for (var i = 0; i < tagsLength; i++) {
            var classNames = tags[i].className.split(" ");
            var classLength = classNames.length;
            for (var j = 0; j < classLength; j++) {
                if ( classNames[j] === className ) {
                    result.push(tags[i]);
                    break;
                }
            }
        }
        return result;
    }
</script>
</body>
</html>
