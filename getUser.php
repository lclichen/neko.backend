<?php
header("content-type:text/html;charset=utf-8");
$postdata = json_encode($_POST,JSON_UNESCAPED_UNICODE); //获得POST请求提交的数据

include_once "common.php";
$data = initPostData();
$token = $data['token'];

$con = pdo_database();
//echo($token);
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
//echo($ctrl);
if($openid){
    $sql = "SELECT id,name,sex,color,TNR,adopt,sch_area from catsinfo WHERE openid = :openid";
    $sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->execute(array(':openid' => $openid));

    $row = $sth->fetch(PDO::FETCH_ASSOC);
    $result = '[';
    while($row){
        $result .= '{"text":"' . $row['name'] . '","value":' . $row['id'] . ',"sex":"' . $row['sex'] . '","color":"' . $row['color'] . '","adopt":"' . $row['adopt'] . '","area":"' . $row['sch_area'] . '","tnr":"' . $row['TNR'] . '"}';
        $row = $sth->fetch(PDO::FETCH_ASSOC);
        if($row){
            $result .= ",";
        }
    }
    $result .= ']';
    //$sth->fetchAll(PDO::FETCH_NUM);
    $redata['id'] = json_decode($result);
    $redata['code'] = 10;
    //echo json_encode($result,JSON_UNESCAPED_UNICODE);
    echo json_encode($redata,JSON_UNESCAPED_UNICODE);
    $con = null;
}
else{
    $con = null;
    die('{"code":1002,"msg":"请重新登录！"}');
}