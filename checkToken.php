<?php
header("content-type:text/html;charset=utf-8");

include_once(__DIR__."/common.php");
$data = initPostData();
$token = $data['token'];

$con = pdo_database();
//echo($token);
if($token){
    // 由于微信小程序的改版，此处需要增加一个变量，用于记录用户是否已经填写了个人信息
    $sql = 'SELECT openid,nickName FROM `userinfo` WHERE login_token = :token';
    $sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->execute(array(':token' => $token));
    $redata = $sth->fetch(PDO::FETCH_ASSOC);
    $openid = $redata['openid'];
    $nickName = $redata['nickName'];
    if($nickName == "微信用户"){
        $redata['needProfile'] = true;
    }
    else{
        $redata['needProfile'] = false;
    }
}
else{
    die('{"code":1001,"msg":"Token未传入！"}');
}
//echo($identity);
if($openid){
    $redata['code'] = 10;
    echo json_encode($redata,JSON_UNESCAPED_UNICODE);
    $con = null;
}
else{
    $con = null;
    die('{"code":1002,"msg":"请重新登录！"}');
}