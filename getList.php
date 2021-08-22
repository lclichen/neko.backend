<?php
header("content-type:text/html;charset=utf-8");
include_once(__DIR__."/common.php");
$data = initPostData();

$name = $sex = $health = $TNR = $keyword = $sch_area = $adopt = '';
$name = $data['name'];//~~~
$sex = $data['sex'];//empty/male/female
$health = $data['health'];//empty/healthy/sick/death
$TNR = $data['tnr'];//empty/cut/cutting
$keyword = $data['keyword'];//~~~
$sch_area = $data['area'];//empty/west/east/north/south/middle/ustciat/island/other
$adopt = $data['adopt'];//empty/yes/no
$color = $data['color'];
$tag = $data['tag'];

$con = pdo_database();
$arr = array();
$SCondition = "SELECT id,name,sex,color,TNR,adopt,sch_area,health FROM `catsinfo` WHERE ";
if(strlen($sch_area)>0 && $sch_area != 'all'){ #根据校区进行筛选
    $SCondition .= "sch_area = :sch_area AND ";
    $arr[':sch_area'] = $sch_area;
}
if(strlen($adopt)>0){ #根据领养情况进行筛选
    $SCondition .= "adopt = :adopt AND ";
    $arr[':adopt']=$adopt;
}
if(strlen($sex)>0){ #根据性别进行筛选
    $SCondition .= "sex = :sex AND ";
    $arr[':sex'] = $sex;
}
if(strlen($name)>0){ #根据名字进行筛选
    $SCondition .= "name LIKE :name AND ";
    $arr[':name'] = "%$name%";
}
if(strlen($TNR)>0){ #根据绝育情况进行筛选
    $SCondition .= "TNR = :tnr AND ";
    $arr[':tnr'] = $TNR;
}
if(strlen($health)>0){ #根据健康情况进行筛选
    $SCondition .= "health = :health AND ";
    $arr[':health'] = $health;
}
if(strlen($color)>0 && $color != 'all'){ #根据健康情况进行筛选
    $SCondition .= "color = :color AND ";
    $arr[':color'] = $color;
}
if(strlen($keyword)>0){ #根据关键词进行筛选
    $SCondition .= "(name LIKE :keyword OR description LIKE :keyword ) AND ";
    $arr[':keyword'] = '%'."$keyword".'%';
}
$SCondition .= "hide = 0;";

$sth = $con->prepare($SCondition, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->execute($arr);

$rows = $sth->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($rows,JSON_UNESCAPED_UNICODE);