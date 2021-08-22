<?php
#将文件复制为"config.php"后再填写所需内容即可。
$dbhost = "localhost"; //数据库地址
$dbuser = "你的数据库用户名哦"; //数据库用户名
$dbpass = "你的数据库密码哦"; //数据库密码
$dbname = "nekoustc"; //数据库名称
$dbms="mysql";     //数据库类型

//用于sc_send函数（消息上报）的Token，可无视。
$weixin_key="自定义的Token哦"; 

//用于企业微信接收消息的鉴权。
$weixin_key_re = "也是自定义的Token哦";

// Ucloud对象存储公钥
$UCLOUD_PUBLIC_KEY = 'UCLOUD_PUBLIC_KEY'; //
// Ucloud对象存储私钥
$UCLOUD_PRIVATE_KEY = 'UCLOUD_PRIVATE_KEY'; //

$appId = "wxf2701f15e3f6197e"; // 科大猫咪相簿小程序的appId
$appSecret = "小程序的appSecret"; // 科大猫咪相簿小程序的appSecret