<?php
header("content-type:text/html;charset=utf-8");
include_once __DIR__ . "/common.php";
$data = initPostData();
$token = $data['token'];
$msgid = (int)$data['msgid'];
$msg_status = $data['msg_status'];
$re_status = $data['re_status'];

$con = pdo_database();
$redata = array('code'=>10);
if ($token == '') {
    die('{"code":1006,"msg":"请先登录"}');
}

if ($token) {
    [$openid, $identity, $nickName] = pdoCheckUserPrivilege($con, $token);
    //var_dump([$token,$openid,$identity,$nickName]);
}

if ($openid) {
    switch ($msg_status) {
        case 0:
            // 这个简单一些，直接匹配openid和msgid即可。
            // 交互仅限于标记为已读或者从已读转换为未读？
            $sqlQuery = "UPDATE messages SET msg_status = 1 WHERE openid = :openid AND msgid = :msgid AND msg_status=0";
            $stmt = $con->prepare($sqlQuery);
            $stmt->execute(array(':openid' => $openid, ':msgid' => $msgid));
            $redata["msg"]="修改成功";
            break;
        case 2:
            // 只对msg_status=2的进行操作
            if ($re_status == 0){
                $sta = 3;
            } elseif ($re_status == 1) {
                $sta = 4;
            } else{
                $con = null;
                die('{"code":1001,"msg":"缺少输入参数"}');
            }
            if ($identity == 's') {
                $sqlQuery = "UPDATE messages SET msg_status=:sta WHERE toadmin!=0 AND msg_status=2 AND openid= :openid AND msgid= :msgid";
            } elseif ($identity == 'a'){
                $sqlQuery = "UPDATE messages SET msg_status=:sta WHERE toadmin=1 AND msg_status=2 AND openid= :openid AND msgid= :msgid";
            }
            else {
                $con = null;
                die('{"code":1001,"msg":"输入参数错误"}');
            }
            $stmt = $con->prepare($sqlQuery);
            $result_1 = $stmt->execute(array(':sta' => $sta, ':openid' => $openid, ':msgid' => $msgid));
            // 根据消息内容（？）对对应的档案或用户进行处理，比如显示档案或授权用户权限(或许应该在)
            if (!$result_1) {
                $con = null;
                die('{"code":1001,"msg":"Msg更新失败"}');
            }
            $sqlSelect = "SELECT openid,msg,msg_with_user,msg_with_cat FROM messages WHERE msgid=:msgid";
            $sth = $con->prepare($sqlSelect);
            $sth->execute(array(':msgid' => $msgid));
            $resp = $sth->fetch(PDO::FETCH_ASSOC);
            $content = json_decode($resp['msg'])['content'];
            if ($content == "template_3") {
                if ($re_status == 0){
                    // 拒绝
                    $result_2 = true;
                    setNewMsg($con, $resp['openid'], 0, 0, $resp['msg_with_user'], $resp['msg_with_cat'],
                    '{"content":"template_1"}');
                } elseif ($re_status == 1) {
                    $result_2 = update_once($con,"catsinfo","hide",0,"id",$resp['msg_with_cat']);
                    // 通过
                    setNewMsg($con, $resp['openid'], 0, 0, $resp['msg_with_user'], $resp['msg_with_cat'],
                    '{"content":"template_0"}');
                }
            }
            if ($result_2) {
                $redata["msg"]="审核成功";
            }
            break;
        default:
            $con = null;
            die('{"code":1001,"msg":"输入参数错误"}');
            break;
    }
}
else {
    die('{"code":1002,"msg":"登录态已失效"}');
}

// $redata["msg"]="修改成功";
echo json_encode($redata,JSON_UNESCAPED_UNICODE);
$con = null;
