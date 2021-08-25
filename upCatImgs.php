<?php
header("content-type:text/html;charset=utf-8");
include_once(__DIR__."/common.php");
$data = initPostData();
$postdata = json_encode($data,JSON_UNESCAPED_UNICODE); //获得POST请求提交的数据

//$id = (int)$data['id'];
$name = $data['name'];
$imgname = $data['imgname'];
$token = $data['token'];
if($name == ''){
    die('{"code":1001,"msg":"请输入猫的名字"}');
}
if($imgname == ''){
    die('{"code":1001,"msg":"请传入图片名字"}');
}
if($token == ''){
    die('{"code":1006,"msg":"请先登录"}');
}
//打印日志 方便查看
$fp = fopen(__DIR__.'/.log/imglog.txt','a+') or die('{"code":1002,"msg":"无法写入log文件"}');
$D_T = date("Y-m-d H:i:s");
fwrite($fp, $D_T."\n");
fwrite($fp,$postdata."\n");
fclose($fp);

//$location = array("weidu"=>$postdata['latitude'],"jingdu"=>$postdata['longitude']);//暂时不开发本功能；准备单独整个数据库放。
//连接数据库

$con = pdo_database();
if($token){
    [$openid,$ctrl,$nickName] = pdo_check_token($con,$token);
}
$sql_select = $con->prepare('SELECT id FROM catsinfo WHERE name = ?');
$sql_select->bindParam(1,$name);
$sql_select->execute();
$matchid = $sql_select->fetch(PDO::FETCH_ASSOC)['id'];
if($matchid === null){//上传图集图片时还没有档案
    $con=null;
    die('{"code":1007,"msg":"请先建立档案"}');
}
else{
    $id=$matchid;
}
$sql_select = $con->prepare('SELECT link FROM images WHERE link = ?');
$sql_select->bindParam(1,$imgname);
$sql_select->execute();
$matchlink = $sql_select->fetch(PDO::FETCH_ASSOC)['id'];
if($matchlink){
    $con=null;
    die('{"code":1003,"msg":"同名文件已存在"}');
}

$hide = 0;
$sql_insert = $con->prepare('INSERT INTO images (id,link,uploaddate,openid,likeit,hide) VALUES (?, ?, ?, ?, 0, 0)');
$sql_insert->bindParam(1,$id);
$sql_insert->bindParam(2,$imgname);
$sql_insert->bindParam(3,$D_T);
$sql_insert->bindParam(4,$openid);
$result = $sql_insert->execute();
if(!$result){
    echo '{"code":1005,"msg":"数据库记录失败！"}';
}
else{
    echo '{"code":10,"msg":"上传成功"}';
}
$con=null;