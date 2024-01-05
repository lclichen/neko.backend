<?php
header("content-type:text/html;charset=utf-8");
include_once __DIR__ . "/common.php";
$data = initPostData();
$token = $data['token'];
$seccs = $data['scene'];

$con = pdo_database();
if ($token) {
    [$openid, $identity, $nickName, $uid] = pdoCheckUserPrivilege($con, $token, true);
    //var_dump([$token,$openid,$identity,$nickName]);
}

if ($openid && $identity == 'u') {
    $identity = pdoCheckCatEditPrivilege($con, $openid, $id);
}

$ntime = date("Y-m-d H:i:s");

$sth = $con->prepare("SELECT * FROM invitepower WHERE secret_checksum = :seccs");
$sth->execute(array(':seccs' => $seccs));
$resu = $sth->fetch(PDO::FETCH_ASSOC);
if($resu){
    if ($resu['invite_period_time']>$ntime && $resu['times_left'] > 0) {
        $sthi = $con->prepare('INSERT INTO userpower (catid,openid,power,auth_by) VALUES (:id, :openid, "e", :authby)', array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $result = $sthi->execute(array(':id' => $resu['catid'], ':openid' => $openid, ':authby' => $resu['inviter_openid']));
        if($resu['times_left']>1){
            $con->prepare("UPDATE invitepower SET times_left = times_left - 1 WHERE secret_checksum = :seccs ")->execute(array(':seccs' => $seccs));
        } else {
            $con->prepare("DELETE FROM invitepower WHERE secret_checksum = :seccs")->execute(array(':seccs' => $seccs));
        }
    } else {
        $con->prepare("DELETE FROM invitepower WHERE secret_checksum = :seccs")->execute(array(':seccs' => $seccs));
        $con = null;
        die('{"code":1005,"msg":"邀请码已失效！"}');
    }
    if($result){
        $redata['code'] = 10;
    }
    echo json_encode($redata, JSON_UNESCAPED_UNICODE);
    // 邀请成功的消息
    setNewMsg($con, $resu['inviter_openid'], 5, 0, $uid, $resu['catid'],
    '{"content":"template_4"}');
    $con = null;
}else{
    $con = null;
    die('{"code":1005,"msg":"邀请码已失效！"}');
}
