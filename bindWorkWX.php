<?php
header("content-type:text/html;charset=utf-8");

include_once(__DIR__."/common.php");
$data = initPostData();
$token = $data['token'];
$ww_userid = $data['ww_userid'];
$pay_name = $data['pay_name'];
// 绑定企业微信管理后台
// 格式：/bind $token $payName,直接粘贴到企业微信即可
$input = array(':token'=>$token,':ww_userid'=>$ww_userid,':pay_name'=>$pay_name);
$con = pdo_database();
$sql = "UPDATE userinfo SET ww_userid = :ww_userid WHERE token = :token AND ww_userid IS NULL";
$sth = $con->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->execute($input);

$sql = "UPDATE userinfo SET pay_name = :pay_name WHERE token = :token AND ww_userid = :ww_userid";
$sth = $con->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->execute($input);

$sql = "SELECT id,nickName FROM userinfo WHERE token = :token AND ww_userid = :ww_userid AND pay_name = :pay_name";
$sth = $con->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->execute($input);

$res = $sth->fetch(PDO::FETCH_ASSOC);

if($res['nickName']){
    sc_send($title = "您已成功使用企业微信绑定小程序管理后台",$text = "您的企业微信ID是 ".$ww_userid."\n您的账单用昵称为 ".$pay_name."\n您的微信昵称为 ".$res['nickName']."\n如有错漏请联系@离离沐雪",$type = 'text',$touser = $ww_userid, $toparty = '', $IsJson = true);
}
else{
    sc_send($title = "企业微信绑定失败！",$text = "请尝试在小程序重新认证信息以刷新token，如果还不成功请联系@离离沐雪",$type = 'text',$touser = $ww_userid, $toparty = '', $IsJson = true);
}

$con = null;