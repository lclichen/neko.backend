<?php
header("content-type:image/jpeg");
header('Access-Control-Allow-Headers:x-requested-with,content-type');
include_once(__DIR__."/../common.php");

function getAccessToken($appid, $appsecret) {
    //TODO: access_token 应该全局存储与更新，以下代码以写入到文件中做示例
    //TODO: 每个应用的access_token应独立存储，此处用secret作为区分应用的标识
    $path = __DIR__."/.cache/$appsecret.php";
    $data = json_decode(file_get_contents($path));
    if($data->expire_time < time()) {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";
        $res = json_decode(http_get($url)['content']);
        $access_token = $res->access_token;
        if($access_token) {
            $data->expire_time = time() + 7000;
            $data->access_token = $access_token;
            file_put_contents($path, json_encode($data));
        }
    } else {
        $access_token = $data->access_token;
    }
    return $access_token;
}

function getUnlimitedWxacode($token,$scene,$page,$width,$auto_color,$line_color,$is_hyaline)
{
    $msg = array(
        'scene'=>$scene,
        'page'=>$page,
        'width'=>$width,
        'auto_color'=>$auto_color,
        'line_color'=>$line_color,
        'is_hyaline'=>$is_hyaline
    );
    $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=$token";
    
    return http_post($url,$msg)['content'];
}
$data = initPostData();

$Scene = $data['scene']; //最大32个可见字符，只支持数字，大小写英文以及部分特殊字符：!#$&'()*+,/:;=?@-._~
$Page = $data['page']; //默认主页 //必须是已经发布的小程序存在的页面（否则报错），例如 pages/index/index, 根路径前不要填加 /,不能携带参数（参数请放在scene字段里），如果不填写这个字段，默认跳主页面
$Width = $data['width']; //默认430 //二维码的宽度，单位 px，最小 280px，最大 1280px
$Auto_color = boolval($data['auto_color']); //false //自动配置线条颜色，如果颜色依然是黑色，则说明不建议配置主色调，默认 false
$Line_color = json_decode($data['line_color']); //{"r":0,"g":0,"b":0} //auto_color 为 false 时生效，使用 rgb 设置颜色 例如 {"r":"xxx","g":"xxx","b":"xxx"} 十进制表示
$Is_hyaline = boolval($data['is_hyaline']); //false //是否需要透明底色，为 true 时，生成透明底色的小程序
$filename = __DIR__."/acode/".urlencode("$Page-$Scene-$Width-$Line_color");
if($Auto_color){
    $filename .= '1';
}
if($Is_hyaline){
    $filename .= '1';
}
$filename .= '.jpg';
if(is_file($filename)){
    $f = fopen($filename,"rb");
    $resp = fread($f,filesize($filename));
    fclose($f);
    //$resp = base64_decode(file_get_contents("./acode/$filename"));
}
else{
    $Token = getAccessToken($GLOBALS['appId'],$GLOBALS['$appSecret']);

    $resp = getUnlimitedWxacode($Token,$Scene,$Page,$Width,$Auto_color,$Line_color,$Is_hyaline);
    
    $f = fopen($filename,"wb");
    fwrite($f,$resp);
    fclose($f);
    //file_put_contents($filename, base64_encode($resp));
}

echo $resp;
?>