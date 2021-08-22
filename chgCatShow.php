<?php
header("content-type:text/html;charset=utf-8");
include_once "common.php";
$data = initPostData();
$id = $data['id'];
$act = $data['act'];
$weixin_key_re = $data['pwdd'];

if($weixin_key_re != $GLOBALS['weixin_key_re']){
    die('{"code":1006,"msg":"权限验证错误"}');
}
//连接数据库

$con = pdo_database();
if($act=='show'){
    $hide = 0;
}
if($act == 'hide'){
    $hide = 1;
}
update_once($con,"catsinfo","hide",$hide,"id",$id);

echo '{"code":10,"msg":"Succeed"}';
$con=null;
//重构完成。