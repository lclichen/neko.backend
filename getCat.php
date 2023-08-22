<?php
header("content-type:text/html;charset=utf-8");
include_once __DIR__ . "/common.php";
$data = initPostData();
$id = (int)$data['id'];
$token = $data['token'];
$con = pdo_database();
if ($token) {
    [$openid, $identity, $nickName] = pdoCheckUserPrivilege($con, $token);
    //var_dump([$token,$openid,$identity,$nickName]);
}

if ($openid && $identity == 'u') {
    $identity = pdoCheckCatEditPrivilege($con, $openid, $id);
}

if ($identity == 'a' || $identity == 'o' || $identity == 'e') {
    $sql = "SELECT id,name,birthday,color,health,TNR,cutdate,sch_area,uploader,adopt,adopter,sex,description,adoptdate,deathdate,vacdate,vac,rate,raters,uploader,a_tel,secret FROM `catsinfo` WHERE id = :id ;";
    $isA = 1;
    //$isA = 's';
} elseif ($identity == 's') {
    $sql = "SELECT * FROM `catsinfo` WHERE id = :id ;";
    $isA = 's';
} else {
    $sql = "SELECT id,name,birthday,color,health,TNR,cutdate,sch_area,uploader,adopt,sex,description,adoptdate,deathdate,vacdate,vac,rate,raters FROM `catsinfo` WHERE id = :id ;";
    $isA = 0;
}

$sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->execute(array(':id' => $id));


if ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
    $result['isAdmin'] = $isA;

    // get img list
    // 此处的admin是针对图像本身的，isAdmin是针对档案的
    $SCondition = "SELECT link,likeit,uploaddate,openid FROM `images` WHERE id = :id AND hide = 0";
    $sth = $con->prepare($SCondition, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->execute(array(':id' => $id));
    $rows = $sth->fetchAll(PDO::FETCH_ASSOC);
    // 取最多七张图的链接
    $len = count($rows);
    if ($len > 7) {
        $rows_val = array_rand($rows, 7);
        $rows_out = [];
        for ($i = 0; $i < 7; $i++) {
            $rows_out[$i] = $rows[$rows_val[$i]];
        }
    } else {
        $rows_out = $rows;
    }

    // var_dump($rows_out);
    $len2 = count($rows_out);
    // 生成返回的json数据
    $outtext = '[';
    $i = 0;
    foreach ($rows_out as $row) {
        if ($openid != '' && ($identity == "s" || $openid == $row['openid'])) {
            $admin = '1';
        } else {
            $admin = '0';
        }
        $outtext .= '{"link":"' . $row['link'] . '","likeit":"' . $row['likeit'] .
             '","uploaddate":"' . $row['uploaddate'] . '","admin":' . $admin . '}';
        $i++;
        if ($i < $len2) {
            $outtext .= ',';
        }
    }
    $outtext .= ']';
    $result['imglist'] = json_decode($outtext);

    //get personal rate
    if ($openid) {
        $sth = $con->prepare(
            "SELECT rate FROM rates WHERE id = :id AND openid = :openid",
            array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)
        );
        $sth->execute(array(':id' => $id, ':openid' => $openid));
        $result['personal_rate'] = $sth->fetch(PDO::FETCH_ASSOC)['rate'];
    }
    $result['code'] = 10;
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}
$con = null;
