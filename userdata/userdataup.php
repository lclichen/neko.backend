<?php

include_once(__DIR__."/wxBizDataCrypt.php");
include_once(__DIR__."/UserInfoDB.php");
include_once(__DIR__."/../config.php");

$code = $_POST['code'];
$encryptedData = $_POST['encryptedData'];
$iv = $_POST['iv'];
$isProfile = $_POST['isProfile'];
if($isProfile){
    $isProfile = true;
}
else{
    $isProfile = false;
}

$Url = "https://api.weixin.qq.com/sns/jscode2session?appid=" . $GLOBALS['appId'] . "&secret=" . $GLOBALS['appSecret'] . "&js_code=" . $code . "&grant_type=authorization_code";

$ch = curl_init();
// 设置URL和相应的选项
curl_setopt($ch, CURLOPT_URL, $Url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// 抓取URL并把它传递给浏览器
$result = json_decode(curl_exec($ch), true);
// 关闭cURL资源，并且释放系统资源
curl_close($ch);
$openid = $result['openid'];

$sessionKey = $result['session_key'];

$pc = new WXBizDataCrypt($GLOBALS['appId'], $sessionKey);
$errCode = $pc->decryptData($encryptedData, $iv, $data);
if ($errCode == 0) {
    $data = json_decode($data,true);
    //if (($data['openId'] == $openid) && ($data['watermark']['appid']==$appId)) {
    if ($data['watermark']['appid']==$GLOBALS['appId']) {
        $db = new UserInfoDB($data,$sessionKey,$openid,$isProfile);
        $token = $db->addUserInfo($data,$sessionKey,$openid,$isProfile);
        echo $token;
    }
} else {
    print($errCode . "\n");
}
