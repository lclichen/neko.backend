<?php
header("content-type:text/html;charset=utf-8");
include_once "common.php";
$data = initPostData();
$link = $data['link'];
$token = $data['token'];
if($link == ''){
    return;
}
//连接数据库
$con = pdo_database();
if($token){
    [$openid,$identity,$nickName] = pdoCheckUserPrivilege($con,$token);
}
//echo $identity;
if($openid && $identity == 'u'){
    $identity = pdfCheckImageOwner($con,$openid,$link);
}
// 此处仅超级管理员和上传图片的用户本人可以删除，
if(($identity == 's' || $identity == 'o') && strlen($link) > 4 ){
    $sql = "UPDATE `images` SET hide = 1 WHERE link = :link;";
}
else{
    $con = null;
    die('{"code":1006,"msg":"无权限，请登录后重试。"}');
}
$sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$result = $sth->execute(array(':link' => $link));
echo '{"code":10,"msg":"已删除"}';
$con=null;
