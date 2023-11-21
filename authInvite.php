<?php
header("content-type:text/html;charset=utf-8");
include_once __DIR__ . "/common.php";
$data = initPostData();
$catid = (int)$data['catid'];
$token = $data['token'];
$period_days = (int)$data['pdays'];
$times_left = (int)$data['tleft'];

$con = pdo_database();
if ($token) {
    [$openid, $identity, $nickName] = pdoCheckUserPrivilege($con, $token);
    //var_dump([$token,$openid,$identity,$nickName]);
}

if ($openid && $identity == 'u') {
    $identity = pdoCheckCatEditPrivilege($con, $openid, $id);
}
$redata = array();
if ($identity == 'a' || $identity == 'o' || $identity == 's') {
    $sth = $con->prepare("INSERT INTO invitepower (catid,inviter_openid,invite_create_time,invite_period_time,times_left,secret_checksum) VALUES (:catid,:openid,:ntime,:ptime,:times_left,:seccs)");
    $ntime = date("Y-m-d H:i:s");
    $ptime = date("Y-m-d H:i:s", strtotime("+$period_days day"));
    $seccs = md5($openid.$ntime.$catid);
    $result = $sth->execute(array(':catid'=>$catid,':openid' => $openid,':ntime'=>$ntime,':ptime'=>$ptime,'times_left'=>$times_left,':seccs'=>$seccs));
    if($result){
        $redata['code'] = 10;
        $redata['scene'] = $seccs;
    }
    echo json_encode($redata, JSON_UNESCAPED_UNICODE);
    $con = null;
    return;
}

$con = null;
die('{"code":1002,"msg":"请重新登录！"}');