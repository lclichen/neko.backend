<?php
header("content-type:text/html;charset=utf-8");
include_once __DIR__ . "/common.php";
$data = initPostData();
$token = $data['token'];
$page = (int)$data['page'];
$read_class = $data['read_class'];
$pagesize = 30;// = (int)$data['pagesize'];
if (!$page) {
    $page=0;
}

$con = pdo_database();

if ($token) {
    [$openid, $identity, $nickName] = pdoCheckUserPrivilege($con, $token);
    //var_dump([$token,$openid,$identity,$nickName]);
}

// 还得有一个切换已读/未读的按钮
if ($openid) {
    $poffset = $page*$pagesize;
    $sqlGetMsgs = "SELECT msgid,msg_status,toadmin,msg_with_user,msg_with_cat,msgdate,msg from messages WHERE ";
    if ($read_class = 'all') {
        if ($identity == 's') {
            $sqlGetMsgs .= "openid = :openid OR (toadmin = 1 OR toadmin = 2)";
        } elseif ($identity == 'a') {
            $sqlGetMsgs .= "openid = :openid OR toadmin = 1";
        } else {
            $sqlGetMsgs .= "openid = :openid";
        }
    } elseif ($read_class = 'marked') {
        if ($identity == 's') {
            $sqlGetMsgs .= "(openid = :openid AND msg_status = 1) OR ((msg_status=3 OR msg_status=4) AND (toadmin = 1 OR toadmin = 2))";
        } elseif ($identity == 'a') {
            $sqlGetMsgs .= "(openid = :openid AND msg_status = 1) OR ((msg_status=3 OR msg_status=4) AND toadmin = 1)";
        } else {
            $sqlGetMsgs .= "openid = :openid AND msg_status = 1";
        }
    } elseif ($read_class = 'tomark') {
        if ($identity == 's') {
            $sqlGetMsgs .= "(openid = :openid AND msg_status = 0) OR (msg_status=2 AND (toadmin = 1 OR toadmin = 2))";
        } elseif ($identity == 'a') {
            $sqlGetMsgs .= "(openid = :openid AND msg_status = 0) OR (msg_status=2 AND toadmin = 1)";
        } else {
            $sqlGetMsgs .= "openid = :openid AND msg_status = 0";
        }
    }
    $sqlGetMsgs .= " ORDER BY `msgdate` DESC LIMIT $poffset,$pagesize;";
    $sthGetMsgs = $con->prepare($sqlGetMsgs, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sthGetMsgs->execute(array(':openid' => $openid));
    $msgs = $sthGetMsgs->fetchAll(PDO::FETCH_ASSOC);
    $result = array();
    $result['code'] = 10;
    $result['msgs'] = $msgs;
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    $con = null;
} else {
    $con = null;
    die('{"code":1002,"msg":"请重新登录！"}');
}