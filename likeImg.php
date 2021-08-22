<?php
header("content-type:text/html;charset=utf-8");

include_once(__DIR__."/common.php");
$data = initPostData();
$link = $data['link'];
if($link == ''){
    return;
}
//连接数据库
$con = pdo_database();
$sql = "SELECT likeit FROM images WHERE link = :link";
$sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->execute(array(':link' => $link));
$likenum = $sth->fetch(PDO::FETCH_ASSOC)['likeit'];

if($likenum === NULL){
    $con=null;
    return;
}
$likenum += 1;
update_once($con,"images","likeit",$likenum,"link",$link);
echo $likenum;
$con = null;