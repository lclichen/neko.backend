<?php
header("content-type:text/html;charset=utf-8");

include_once(__DIR__."/common.php");
$data = initPostData();
$token = $data['token'];
$id = $data['id'];
$personal_rate = $data['personal_rate'];
$action = $data['action'];
if($id == '' || !(is_int($personal_rate) && $personal_rate <= 10 && $personal_rate >= -10)){
    return;
}
if($token == ''){
    die('{"code":1006,"msg":"请先登录"}');
}
if($token){
    [$openid,$ctrl,$nickName] = pdo_check_token($con,$token);
}
//连接数据库
$con = pdo_database();
// 检查数据库中自己的评分
$sql = "SELECT rate FROM rates WHERE id = :id AND openid = :openid";
$sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->execute(array(':id' => $id,':openid'=>$openid));
$rate_in_db = $sth->fetch(PDO::FETCH_ASSOC)['rate'];
// 获取当前评分总和
$sql = "SELECT rate,raters FROM catsinfo WHERE id = :id";
$sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->execute(array(':id' => $id));
$result = $sth->fetch(PDO::FETCH_ASSOC);
$rate = $result['rate'];
$raters = $result['raters'];

if($action == "up" && $rate_in_db != $personal_rate && !is_null($rate_in_db)){
    $sth = $con->prepare("UPDATE rates SET rate = :newrate WHERE id = :id AND openid = :openid");
    $sth->execute(array(':newrate'=>$personal_rate,':id' => $id,':openid'=>$openid));
    $sth = $con->prepare("UPDATE catsinfo SET rate = rate+:diff WHERE id = :id");
    $sth->execute(array(':diff'=>($personal_rate - $rate_in_db),':id' => $id));
    $rate += ($personal_rate - $rate_in_db);
}
if($action == "new" && is_null($rate_in_db)){
    $sth = $con->prepare("INSERT INTO rates (id,rate,openid) VALUES (:id,:newrate,:openid)");
    $sth->execute(array(':newrate'=>$personal_rate,':id' => $id,':openid'=>$openid));
    $sth = $con->prepare("UPDATE catsinfo SET rate = rate+:diff WHERE id = :id");
    $sth->execute(array(':diff'=>($personal_rate),':id' => $id));
    $sth = $con->prepare("UPDATE catsinfo SET raters = raters+1 WHERE id = :id");
    $sth->execute();
    $rate += ($personal_rate);
    $raters += 1;
}
if($action == "del" && !is_null($rate_in_db)){
    $sth = $con->prepare("DELETE FROM rates WHERE id = :id AND openid = :openid AND rate = :rate_in_db");
    $sth->execute(array(':id' => $id,':openid'=>$openid,':rate_in_db'=>$rate_in_db));
    $sth = $con->prepare("UPDATE catsinfo SET rate = rate-:diff WHERE id = :id");
    $sth->execute(array(':diff'=>($rate_in_db),':id' => $id));
    $sth = $con->prepare("UPDATE catsinfo SET raters = raters-1 WHERE id = :id");
    $sth->execute();
    $rate -= ($rate_in_db);
    $raters -= 1;
}

echo("{'id':$id,'rate':$rate,'raters':$raters,'personal_rate':$personal_rate}");

$con = null;