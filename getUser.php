<?php
header("content-type:text/html;charset=utf-8");
//可能需要一些改动以适应无法获取用户信息的状态。解密数据现在是没有必要性的？
include_once "common.php";
$data = initPostData();
$token = $data['token'];

$con = pdo_database();

if ($token) {
    // 由于微信小程序的改版，此处需要增加一个变量，用于记录用户是否已经填写了个人信息
    $sql = 'SELECT openid,admin,nickName,pay_name,avatarUrl FROM `userinfo` WHERE login_token = :token';
    $sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->execute(array(':token' => $token));
    $redata = $sth->fetch(PDO::FETCH_ASSOC);
    $openid = $redata['openid'];
    $nickName = $redata['nickName'];
    if ($nickName == "微信用户") {
        $redata['needProfile'] = true;
    } else {
        $redata['needProfile'] = false;
    }
    $redata['openid'] = '';
} else {
    die('{"code":1001,"msg":"Token未传入！"}');
}

if ($openid) {
    $sqlGetCredit = "SELECT SUM(edit_count) FROM userpower WHERE openid = :openid";
    $sthGetCredit = $con->prepare($sqlGetCredit, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sthGetCredit->execute(array(':openid' => $openid));
    $credit = $sthGetCredit->fetch(PDO::FETCH_ASSOC)['SUM(edit_count)'];

    $sqlGetCats = "SELECT id,name,sex,color,TNR,adopt,sch_area,health from catsinfo".
        " WHERE id IN (SELECT catid FROM userpower WHERE openid = :openid)";
    $sthGetCats = $con->prepare($sqlGetCats, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sthGetCats->execute(array(':openid' => $openid));

    $rows = $sthGetCats->fetchAll(PDO::FETCH_ASSOC);

    $redata['id'] = $rows;
    $redata['credit'] = $credit;
    $redata['code'] = 10;
    echo json_encode($redata, JSON_UNESCAPED_UNICODE);
    $con = null;
} else {
    $con = null;
    die('{"code":1002,"msg":"请重新登录！"}');
}
