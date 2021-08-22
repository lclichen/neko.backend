<?php
header("content-type:text/html;charset=utf-8");
include_once(__DIR__."/common.php");
$data = initPostData();
$id = (int)$data['id'];
$token = $data['token'];
$con = pdo_database();
if($token){
    [$openid,$ctrl,$nickName] = pdo_check_token($con,$token);
    //var_dump([$token,$openid,$ctrl,$nickName]);
}

if($openid && $ctrl == 'u'){
    $ctrl = pdo_check_cat_owner($con,$openid,$id);
}

if($ctrl == 'a' || $ctrl == 'o'){
    $sql = "SELECT id,name,birthday,color,health,TNR,cutdate,sch_area,uploader,adopt,adopter,sex,description,adoptdate,deathdate,vacdate,vac,rate,raters,uploader,a_tel,secret FROM `catsinfo` WHERE id = :id ;";
    $isA = 1;
    //$isA = 's';
}
elseif($ctrl == 's'){
    $sql = "SELECT * FROM `catsinfo` WHERE id = :id ;";
    $isA = 's';
}
else{
    $sql = "SELECT id,name,birthday,color,health,TNR,cutdate,sch_area,uploader,adopt,sex,description,adoptdate,deathdate,vacdate,vac,rate,raters FROM `catsinfo` WHERE id = :id ;";
    $isA = 0;
}

$sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->execute(array(':id' => $id));


if($result = $sth->fetch(PDO::FETCH_ASSOC)){
    $result['isAdmin']=$isA;

    #get img list
    $SCondition = "SELECT link,likeit,uploaddate,openid FROM `images` WHERE id = :id AND hide = 0";
    $sth = $con->prepare($SCondition, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->execute(array(':id'=>$id));
    $rows = $sth->fetchAll(PDO::FETCH_ASSOC);
    #取最多五张图的链接
    $len=count($rows);
    if($len>5){
        $rows_val = array_rand($rows,5);
        $rows_out = [];
        for($i = 0; $i < 5; $i ++){
            $rows_out[$i] = $rows[$rows_val[$i]];
        }
    }
    else{
        $rows_out = $rows;
    }

    // var_dump($rows_out);
    $len2=count($rows_out);
    #生成返回的json数据
    $outtext = '[';
    $i = 0;
    foreach($rows_out as $row){
        if($ctrl == "s" || $openid == $row['openid']){
            $admin = '1';
        }
        else{
            $admin = '0';
        }
        $outtext .= '{"link":"' . $row['link'] . '","likeit":"' . $row['likeit'] . '","uploaddate":"' . $row['uploaddate'] . '","admin":' . $admin . '}';
        $i++;
        if($i<$len2){
            $outtext .= ',';
        }
    }
    $outtext .= ']';
    $result['imglist']=json_decode($outtext);

    #get personal rate
    if($openid){
        $sth = $con->prepare("SELECT rate FROM rates WHERE id = :id AND openid = :openid", array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->execute(array(':id' => $id, ':openid' => $openid));
        $result['personal_rate']=$sth->fetch(PDO::FETCH_ASSOC)['rate'];
    }
    $result['code']=10;
    echo json_encode($result,JSON_UNESCAPED_UNICODE);
}
$con=null;