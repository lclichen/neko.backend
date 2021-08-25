<?php
header("content-type:text/html;charset=utf-8");
include_once "common.php";
$data = initPostData();
$id = (int)$data['id'];
$new_name = $data['new_name'];
$weixin_key_re = $data['pwdd'];

if($weixin_key_re != $GLOBALS['weixin_key_re']){
    die('{"code":1006,"msg":"权限验证错误"}');
}
//连接数据库

$con = pdo_database();

update_once($con,"catsinfo","name",$new_name,"id",$id);

echo '{"code":10,"msg":"Succeed"}';
$con=null;
// 还有改对象存储中文件名的。。。要写