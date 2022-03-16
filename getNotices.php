<?php
header("content-type:text/html;charset=utf-8");
include_once(__DIR__."/common.php");
$data = initPostData();
$tag = $data['tag'];

$con = pdo_database();
if($tag=="wx"){
    $SCondition = "SELECT notice FROM `notices` WHERE hide = 0 ORDER BY level_ DESC,id ASC;";
}
elseif($tag=="work"){
    $SCondition = "SELECT id,up_ts,notice,level_ FROM `notices` WHERE hide = 0 ORDER BY level_ DESC,id ASC;";
}
else{
    die('{"code":1001,"msg":"Need A Tag"}');
}


$sth = $con->prepare($SCondition, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->execute();
if($tag=="wx"){
    $rows = $sth->fetchAll(PDO::FETCH_COLUMN);
}
else{
    $rows = $sth->fetchAll(PDO::FETCH_ASSOC);
}

echo json_encode($rows,JSON_UNESCAPED_UNICODE);
$con = null;