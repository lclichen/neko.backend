<?php

include_once __DIR__."/UserInfoDB.php";
//后续仅用于登录，去除用户信息的收集。
include_once __DIR__ . "/../common.php";
$data = initPostData();
$code = $data['code'];

if($isProfile){
    $isProfile = true;
}
else{
    $isProfile = false;
}

$Url = "https://api.weixin.qq.com/sns/jscode2session?appid=".
        $GLOBALS['appId'] . "&secret=" . $GLOBALS['appSecret'].
        "&js_code=" . $code . "&grant_type=authorization_code";

$ch = curl_init();
// 设置URL和相应的选项
curl_setopt($ch, CURLOPT_URL, $Url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$retry_times = 3;
while(is_null($sessionKey) && $retry_times > 0){
    // 抓取URL并把它传递给浏览器
    $result = json_decode(curl_exec($ch), true);
    $openid = $result['openid'];
    $sessionKey = $result['session_key'];
    // unionid
    $errCode = $result['errcode'];
    if($errCode){
        $errMsg = $result['errmsg'];
    }
    $retry_times -= 1;
}
// 关闭cURL资源，并且释放系统资源
curl_close($ch);

// 不包含敏感信息，不再需要进行数据解密。
$redata = array('code'=>10);
if ($errCode == 0 && ($sessionKey)) {
    //if (($data['openId'] == $openid) && ($data['watermark']['appid']==$appId)) {
    $db = new UserInfoDB($sessionKey,$openid);
    $token = $db->addUserInfo($sessionKey,$openid);
    $redata['token'] = $token;
    echo json_encode($redata,JSON_UNESCAPED_UNICODE);
} else {
    $redata['code'] = $errCode;
    echo json_encode($redata,JSON_UNESCAPED_UNICODE);
}
