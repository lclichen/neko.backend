<?php
header("content-type:text/html;charset=utf-8");
include_once(__DIR__."/common.php");
$data = initPostData();

$name = $data['name'];
$sex = $data['sex'];
$birth_y = $data['birth_y'];
$birth_m = $data['birth_m'];
$health = $data['health'];
$deathdate = $data['deathdate'];
$vac = $data['vac'];
$vacdate = $data['vacdate'];
$TNR = $data['tnr'];
$cutdate = $data['cutdate'];
$adopt = $data['adopt'];
$sch_area = $data['area'];
$description = $data['desc'];
$secret = $data['secret'];
$uploader = $data['uploader'];
$tag = $data['tag'];
$adopter = $data['adopter'];
$adoptdate = $data['adoptdate'];
$a_tel = $data['a_tel'];
$color = $data['color'];
$token = $data['token'];

if($name == '' || $uploader == ''){
    die('{"code":1001,"msg":"请输入姓名、描述及上传者。"}');
}
if($token == ''){
    die('{"code":1006,"msg":"请先登录"}');
}
$postdata = json_encode($data,JSON_UNESCAPED_UNICODE); //获得POST请求提交的数据
//打印日志 方便查看
$fp = fopen(__DIR__.'/.log/log.txt','a+') or die('{"code":1002,"msg":"无法写入log文件"}');
$D_T = date("Y-m-d h:i:sa");
fwrite($fp, $D_T."\n");
fwrite($fp,$postdata."\n");
fclose($fp);
// 连接数据库
$con = pdo_database();
if($token){
    [$openid,$ctrl,$nickName] = pdo_check_token($con,$token);
}
$report = "";
// 按名字查找匹配
$sql = 'SELECT id FROM catsinfo WHERE name = :name';
$sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->execute(array(':name' => $name));
$matchid = $sth->fetch(PDO::FETCH_ASSOC)['id'];


if($matchid !== null){//匹配到id,更新模式,tag应为add
    if($tag != 'add'){//防同名重复提交。
        $con = null;
        die('{"code":1003,"msg":"请勿提交重复数据"}');
    }
    $id=$matchid;
}
else{//匹配不到id,新增模式,tag应为new
    if($tag != 'new'){//防同名重复提交。
        $con = null;
        die('{"code":1004,"msg":"改名请联系管理员"}');
    }
    $hide = 1;// 控制是否默认隐藏
    $id = $con->query("SELECT MAX(id) FROM catsinfo;")->fetch(PDO::FETCH_ASSOC)['MAX(id)'] + 1;
    $sql = 'INSERT INTO catsinfo (id, name, color, hide, openid, sex, health, vac, TNR, sch_area ,adopt,adopter,description,secret,a_tel) VALUES ( :id, :name, "emp", :hide, :openid, "empty", "empty", "empty", "empty", "empty", "empty","","","","")';
    $sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $result = $sth->execute(array(':id' => $id,':name' => $name,':hide'=>$hide,':openid'=>$openid));
    if($result){
        $report = "已新增 $id $name 的数据，Uper: $uploader";
    }
    else{
        $con=null;
        die('{"code":1005,"msg":"新增 '.$id.' '.$name.' 失败！"}');
    }
    //echo "test $id | $ctrl | $openid \n";
}
if($openid != '' && $ctrl == 'u'){
    $ctrl = pdo_check_cat_owner($con,$openid,$name);
    //echo "《$id $name 资讯认证成功》\n";
}
if(!in_array($ctrl,["s","a","o"],true)){
    die('{"code":1006,"msg":"无权限，请登录后重试。"}');
}
if($sex && $sex !='empty'){
    update_once($con,"catsinfo","sex",$sex,"id",$id);
}
if($health && $health !='empty'){
    update_once($con,"catsinfo","health",$health,"id",$id);
}
if($deathdate && $deathdate !=''){
    update_once($con,"catsinfo","deathdate",$deathdate,"id",$id);
}
if($vac && $vac !='empty'){
    update_once($con,"catsinfo","vac",$vac,"id",$id);
}
if($vacdate && $vacdate !=''){
    update_once($con,"catsinfo","vacdate",$vacdate,"id",$id);
}
if($TNR && $TNR !='empty'){
    update_once($con,"catsinfo","TNR",$TNR,"id",$id);
}
if($cutdate && $cutdate !=''){
    update_once($con,"catsinfo","cutdate",$cutdate,"id",$id);
}
if($sch_area && $sch_area !='empty'){
    update_once($con,"catsinfo","sch_area",$sch_area,"id",$id);
}
if($adopt && $adopt !='empty'){
    update_once($con,"catsinfo","adopt",$adopt,"id",$id);
}
if ($birth_y != "year"){
    if($birth_m != "month"){
        $birthday = $birth_y."-".$birth_m."-00";
    }
    else{
        $birthday = $birth_y."-00-00";
    }
    update_once($con,"catsinfo","birthday",$birthday,"id",$id);
}
if($description && $description !=''){
    update_once($con,"catsinfo","description",$description,"id",$id);
}
if($secret && $secret !=''){
    update_once($con,"catsinfo","secret",$secret,"id",$id);
}
if($uploader && $uploader !=''){
    update_once($con,"catsinfo","uploader",$uploader,"id",$id);
}
if($adopter && $adopter !=''){
    update_once($con,"catsinfo","adopter",$adopter,"id",$id);
}
if($adoptdate && $adoptdate !=''){
    update_once($con,"catsinfo","adoptdate",$adoptdate,"id",$id);
}
if($a_tel && $a_tel !=''){
    update_once($con,"catsinfo","a_tel",$a_tel,"id",$id);
}
if($color && $color !=''){
    update_once($con,"catsinfo","color",$color,"id",$id);
}
if($openid && $openid != ''){
    update_once($con,"catsinfo","openid",$openid,"id",$id," AND openid IS NULL");
}
    
if($report == ""){
    $report = "已更新 $id $name 的数据";
}
sc_send("通知消息-USTCAT",$nickName.' '.$report, $IsJson = true);
echo '{"code":10,"msg":"'.$report.'"}';
$con=null;