<?php
header("content-type:text/html;charset=utf-8");
include_once(__DIR__."/common.php");
$data = initPostData();
$pid = (int)$data['pid'];

if($pid<0){
    die();
}
$result = [];
/*
//需要增加一个pay_name大概，总不能日常实名上网
$con = pdo_database();
//pid(自增id),upd_date(更新日期),type(类型：待补充类型0，接收捐款1，活动支出2，押金收入3，押金支出4，利息收入5),pay_date(支付日期),pay_num(金额),pay_way(支付方式：待补充0，现金1，支付宝2，微信3，银行卡4，其它(在备注中补充)5),pay_image(支付截图),pay_name(支付人昵称),pay_openid(支付人openid),chk_way(报销方式：未报销0，微信1，支付宝2),chk_date(报销日期),chk_status(入账状态：待提审0,已入账1,需审核2,审核不通过，需修改3),chk_image(报销截图),chk_name(审核人昵称),chk_openid(审核人openid),detail(备注)
$sql = 'SELECT pid,pay_date,pay_num,check_way FROM `paid` WHERE check_status = 1 ORDER BY pay_date DESC LIMIT :pageId * 20,20';
$sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->execute(array(':pageId' => $pageId));
$res = $sth->fetchAll(PDO::FETCH_ASSOC);


$result['paidList'] = json_encode($res,JSON_UNESCAPED_UNICODE);
*/

echo json_encode($result);