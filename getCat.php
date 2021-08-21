<?php
header("content-type:text/html;charset=utf-8");
include_once(__DIR__."/common.php");
$data = initPostData();
$id = (int)$data['id'];
$token = $data['token'];
$con = pdo_database();
if($token){
    [$openid,$ctrl,$nickName] = pdo_check_token($con,$token);
}
if($openid && $ctrl == 'u'){
    $ctrl = pdo_check_cat_owner($con,$openid,$id);
}

if($ctrl == 'a' || $ctrl == 'o'){
    $sql = "SELECT id,name,birthday,color,health,TNR,cutdate,sch_area,uploader,adopt,adopter,sex,description,adoptdate,deathdate,vacdate,vac,rate,raters,uploader,a_tel,secret FROM `catsinfo` WHERE id = :id ;";
    $isA = 1;
    //$isA = 's';
}
elseif($ctrl == 's'){
    $sql = "SELECT * FROM `catsinfo` WHERE id = :id ;";
    $isA = 's';
}
else{
    $sql = "SELECT id,name,birthday,color,health,TNR,cutdate,sch_area,uploader,adopt,sex,description,adoptdate,deathdate,vacdate,vac,rate,raters FROM `catsinfo` WHERE id = :id ;";
    $isA = 0;
}

$sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->execute(array(':id' => $id));


if($result = $sth->fetch(PDO::FETCH_ASSOC)){
    $result['isAdmin']=$isA;
    if($openid){
        $sth = $con->prepare("SELECT rate FROM rates WHERE id = :id AND openid = :openid", array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->execute(array(':id' => $id,':openid' => $openid));
        $result['personal_rate']=$sth->fetch(PDO::FETCH_ASSOC)['rate'];
    }
    echo json_encode($result,JSON_UNESCAPED_UNICODE);
}
$con=null;