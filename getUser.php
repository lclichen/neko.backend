<?php
header("content-type:text/html;charset=utf-8");

include_once "common.php";
$data = initPostData();
$token = $data['token'];

$con = pdo_database();

if($token){
    $sql = 'SELECT openid,admin,nickName,credit FROM `userinfo` WHERE login_token = :token';
    $sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->execute(array(':token' => $token));
    $redata = $sth->fetch(PDO::FETCH_ASSOC);
    $openid = $redata['openid'];
    if($nickName == "微信用户"){
        $redata['needProfile'] = true;
    }
    else{
        $redata['needProfile'] = false;
    }
    $redata['openid'] = '';
}
else{
    die('{"code":1001,"msg":"Token未传入！"}');
}

if($openid){
    $sql = "SELECT id,name,sex,color,TNR,adopt,sch_area,health from catsinfo WHERE openid = :openid";
    $sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->execute(array(':openid' => $openid));

    $rows = $sth->fetchAll(PDO::FETCH_ASSOC);
    
    $redata['id'] = $rows;
    $redata['code'] = 10;
    echo json_encode($redata,JSON_UNESCAPED_UNICODE);
    $con = null;
}
else{
    $con = null;
    die('{"code":1002,"msg":"请重新登录！"}');
}