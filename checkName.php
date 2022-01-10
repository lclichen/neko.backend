<?php
header("content-type:text/html;charset=utf-8");
include_once(__DIR__."/common.php");
$data = initPostData();
$name = $data['name'];
$token = $data['token'];
$con = pdo_database();

$sql = "SELECT id,name FROM `catsinfo` WHERE name = :name;";

$sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->execute(array(':name' => $name));
if($result = $sth->fetch(PDO::FETCH_ASSOC)){
    $result['code']=1003;
    $result['msg']="有重名";
}
else{
    $result['code']=10;
    $result['msg']="无重名";
}
echo json_encode($result,JSON_UNESCAPED_UNICODE);
$con=null;