<?php
header("content-type:text/html;charset=utf-8");
include_once "common.php";
$data = initPostData();
$token = $data['token'];

$con = pdo_database();

if ($token) {
    $sql = 'SELECT openid,admin,nickName,pay_name,avatarUrl,needProfile FROM `userinfo` WHERE login_token = :token';
    $sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->execute(array(':token' => $token));
    $redata = $sth->fetch(PDO::FETCH_ASSOC);
    $openid = $redata['openid'];
    $nickName = $redata['nickName'];
    if ($redata['needProfile']) {
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

    $sqlGetMsgs = "SELECT msgid,msg,msg_status,msgdate from messages WHERE openid = :openid";
    $sthGetMsgs = $con->prepare($sqlGetMsgs, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sthGetMsgs->execute(array(':openid' => $openid));

    $usermsgs = $sthGetMsgs->fetchAll(PDO::FETCH_ASSOC);

    $redata['id'] = $rows;// 此外具有编辑权限的猫的列表
    $redata['credit'] = $credit;// 此为统计得到的战斗力数值（积分）
    $redata['usermsgs'] = $usermsgs;// 此为用户消息列表
    $redata['code'] = 10;
    echo json_encode($redata, JSON_UNESCAPED_UNICODE);
    $con = null;
} else {
    $con = null;
    die('{"code":1002,"msg":"请重新登录！"}');
}
