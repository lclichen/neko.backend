<?php
header("content-type:text/html;charset=utf-8");
include_once __DIR__."/common.php";
$data = initPostData();
$tag = $data['tag'];
$token = $data['token'];
//$tag:new upd chk
if($token == ''){
    die('{"code":1006,"msg":"请先登录"}');
}

// 连接数据库
/*
$con = pdo_database();
if($token){
    [$openid,$identity,$nickName] = pdoCheckUserPrivilege($con,$token);
}
*/