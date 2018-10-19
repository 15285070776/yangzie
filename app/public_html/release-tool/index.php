<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/11
 * Time: 15:53
 */
?>

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

<div id="box">
    <div ><button class="action_button" onclick="exportLog()">导出日志</button></div>
    <div ><button class="action_button" id="generate_key">生成新密钥对</button></div>
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
            私钥：<input class="input_class" type="file" id="private_key_file" name="private_key_file">
            <input class="form_submit" type="button" value="提交" onclick="disabledButton('db_info')">
        </form>
    </div>
</div>

<script src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.8.0.js">
</script>

<script type="text/javascript">
    $("#generate_key").click(function () {
        $.post("generate_key.php", null, function(data,status){
            if (status == "success") {
                alert("密钥对:" + data);
            } else {
                alert("密钥生成失败");
            }
        });
    });

    $("#generate_ini").click(function () {
        $("#choose_secret_key").css("visibility", "visible");
    });

    $("#update_code").click(function () {
        $("#choose_code_zip").css("visibility", "visible");
    });

    $("#export_db").click(function () {
        $("#db_info").css("visibility", "visible");
    });

    function exportLog() {
        window.location.href = "export.php?action=export_log";
    }

    function disabledButton(form_id) {
        $(".action_button").attr("disabled", "true");
        $(".form_submit").attr("disabled", "true");
        document.forms[form_id].submit();
    }
</script>
