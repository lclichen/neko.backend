<?php
header("content-type:text/html;charset=utf-8");
include_once "common.php";
$data = initPostData();
$id = (int)$data['id'];
$notice = $data['notice'];
$level_ = (int)$data['level_'];
$uploader = $data['uploader'];
$act = $data['act'];// action 包括 new/update/show/hide
$weixin_key_re = $data['pwdd'];

if($weixin_key_re != $GLOBALS['weixin_key_re']){
    die('{"code":1006,"msg":"权限验证错误"}');
}
//连接数据库

$con = pdo_database();
if($act=='show'){
    $hide = 0;
    $result = update_once($con,"notices","hide",$hide,"id",$id);
}
elseif($act == 'hide'){
    $hide = 1;
    $result = update_once($con,"notices","hide",$hide,"id",$id);
}
elseif($act == 'new'){
    //INSERT INTO `notices`(`id`, `up_ts`, `notice`, `hide`, `level_`, `uploader`) VALUES ('[value-1]','[value-2]','[value-3]','[value-4]','[value-5]','[value-6]')
    $SCondition = "INSERT INTO notices (`id`, `up_ts`, `notice`, `hide`, `level_`, `uploader`) VALUE (NULL, current_timestamp(), :notice , 0, :level_, :uploader)";
    $stmt = $con->prepare($SCondition);
    $result = $stmt->execute(array(':notice'=>$notice,':level_'=>$level_,':uploader'=>$uploader));
}
elseif($act == 'up_lv'){
    $result = update_once($con,"notices","level_",$level_,"id",$id);
}
elseif($act == 'up_notice'){
    $result = update_once($con,"notices","notice",$notice,"id",$id);
}
if($result){
    echo '{"code":10,"msg":"Succeed"}';
}
else{
    echo '{"code":1004,"msg":"Failed"}';
}

$con=null;