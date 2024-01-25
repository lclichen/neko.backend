<?php
header("content-type:text/html;charset=utf-8");
include_once __DIR__ . "/common.php";
$data = initPostData();
//设定头像与昵称，使用自定义鉴权，不再调用wx.login。

$token = $data['token'];
$avatarUrl = $data['avatarUrl'];
$newName = $data['nickName'];
if ($newName == "" || is_null($newName) || $newName == "undefined"){
    die('{"code":1002,"msg":"请勿传入空值"}');
}
$con = pdo_database();
$redata = array('code'=>10);
if ($token == '') {
    die('{"code":1006,"msg":"请先登录"}');
}
if ($token) {
    [$openid, $identity, $nickName] = pdoCheckUserPrivilege($con, $token);
}

if ($openid) {
    if ($newName) {
        update_once($con,"userinfo","nickName",$newName,"openid",$openid,"");
        update_once($con,"userinfo","needProfile","0","openid",$openid,"");
    }
    if ($avatarUrl) {
        update_once($con,"userinfo","avatarUrl",$avatarUrl,"openid",$openid,"");
    }
}
else {
    die('{"code":1002,"msg":"登录态已失效"}');
}

$redata["msg"]="修改成功";
echo json_encode($redata,JSON_UNESCAPED_UNICODE);
$con = null;
// 不包含敏感信息，不再需要进行数据解密。
