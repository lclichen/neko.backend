<?php
include_once(__DIR__."/config.php");

function test(){
    echo $GLOBALS['dbhost'];
    echo "\nThat A Test Function~\n";
    die();
}

function initPostData(){ // 网络请求接收通用函数
    $data = array();
    if(!empty($_GET)){
        $data = array_merge($data,$_GET);
        //print_r("GET-".$data);
        return $data;
    }
    if(!empty($_POST) && $_SERVER["CONTENT_TYPE"]!='application/json'){
        $data = array_merge($data,$_POST);
        //print_r("POST-".$_SERVER["CONTENT_TYPE"].$data);
        return $data;
    }
    $content = file_get_contents('php://input');
    //print_r($content);
    $data = array_merge($data,json_decode($content, true));
    /*if(empty($data)){
        die("Empty!");
    }*/
    return $data;
}

function execCURL($ch){
    $response = curl_exec($ch);
    $error    = curl_error($ch);
    $result   = array( 'header' => '', 
                     'content' => '', 
                     'curl_error' => '', 
                     'http_code' => '',
                     'last_url' => '');
    
    if ($error != ""){
        $result['curl_error'] = $error;
        return $result;
    }

    $header_size = curl_getinfo($ch,CURLINFO_HEADER_SIZE);
    $result['header'] = str_replace(array("\r\n", "\r", "\n"), "<br/>", substr($response, 0, $header_size));
    $result['content'] = substr( $response, $header_size );
    $result['http_code'] = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    $result['last_url'] = curl_getinfo($ch,CURLINFO_EFFECTIVE_URL);
    $result["base_resp"] = array();
    $result["base_resp"]["ret"] = $result['http_code'] == 200 ? 0 : $result['http_code'];
    $result["base_resp"]["err_msg"] = $result['http_code'] == 200 ? "ok" : $result["curl_error"];

    return $result;
}
function http_get($url){
    $oCurl = curl_init();
    if(stripos($url,"https://")!==FALSE){
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
    }
    curl_setopt($oCurl, CURLOPT_URL, $url);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($oCurl, CURLOPT_VERBOSE, 1);
    curl_setopt($oCurl, CURLOPT_HEADER, 1);

    // $sContent = curl_exec($oCurl);
    // $aStatus = curl_getinfo($oCurl);
    $sContent = execCURL($oCurl);
    curl_close($oCurl);

    return $sContent;
}
function http_post($url,$param,$IsJson = false){
    $oCurl = curl_init();

    if(stripos($url,"https://")!==FALSE){
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
    }
    $strPOST = json_encode($param);
    
    curl_setopt($oCurl, CURLOPT_URL, $url);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($oCurl, CURLOPT_POST,true);
    curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
    curl_setopt($oCurl, CURLOPT_VERBOSE, 1);
    if($IsJson){
        $header = array(
            "Content-Type: application/json",
            "Content-Length: " . strlen($strPOST) . "",
            "Accept: application/json"
        );
    }
    else{
        $header = null;
    }
    if ( !empty($header) ) {
        curl_setopt($oCurl, CURLOPT_HTTPHEADER, $header);
    }
    curl_setopt($oCurl, CURLOPT_HEADER, 1);

    // $sContent = curl_exec($oCurl);
    // $aStatus  = curl_getinfo($oCurl);

    $sContent = execCURL($oCurl);
    curl_close($oCurl);

    return($sContent);
}

function sc_send($title , $text = '',$type = 'text',$touser = '',$toparty = '',$IsJson = false)
{
    include_once "config.php";
    $content = array(
        'type'=>$type,
        'title'=>$title,
        'content'=>$text,
        'key'=>$GLOBALS['weixin_key'],
        'touser'=>$touser,
        'toparty'=>$toparty
    );
    return $result = http_post('https://send.4c43.work/sendMsg3.php', $content, $IsJson);
}

function pdo_database(){ //数据库连接建立
    $dbms = $GLOBALS['dbms'];
    $dbhost = $GLOBALS['dbhost'];
    $dbname = $GLOBALS['dbname'];
    $dsn="$dbms:host=$dbhost;dbname=$dbname;charset=utf8mb4;";
    $dbh = new PDO($dsn, $GLOBALS['dbuser'], $GLOBALS['dbpass'],array( //初始化一个PDO对象
        PDO::ATTR_PERSISTENT => true
    ));
    return $dbh;
}

function pdo_check_token($con,$token){
    $sql = 'SELECT openid,admin,nickName FROM `userinfo` WHERE login_token = :token';
    $sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->execute(array(':token' => $token));
    $result = $sth->fetch(PDO::FETCH_ASSOC);
    return [$result['openid'],$result['admin'],$result['nickName']];
}
function pdo_check_cat_owner($con,$openid,$key){
    if(is_int($key)){
        $sql = "SELECT openid FROM `catsinfo` WHERE id = :key";
    }
    else{
        $sql = "SELECT openid FROM `catsinfo` WHERE name = :key";
    }
    $sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->execute(array(':key' => $key));
    $result = $sth->fetch(PDO::FETCH_ASSOC);
    //echo $result['openid'];
    if($openid != '' && $openid == $result['openid'] ){
        return 'o';
    }
    else{
        return 'u';
    }
}
function pdo_check_image_owner($con,$openid,$key){
    $sql = "SELECT openid FROM `images` WHERE link = :key";
    $sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->execute(array(':key' => $key));
    $result = $sth->fetch(PDO::FETCH_ASSOC);
    if($openid != '' && $openid == $result['openid'] ){
        return 'o';
    }
    else{
        return 'u';
    }
}
function pdo_check_voice_owner($con,$openid,$key){
    $sql = "SELECT openid FROM `voices` WHERE link = :key";
    $sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->execute(array(':key' => $key));
    $result = $sth->fetch(PDO::FETCH_ASSOC);
    if($openid != '' && $openid == $result['openid'] ){
        return 'o';
    }
    else{
        return 'u';
    }
}

function update_once($con,$tab_name,$col_update,$data_update,$col_select,$data_select,$toAppend = '')
{
    $SCondition = "UPDATE $tab_name SET $col_update = :data_update WHERE $col_select = :data_select".$toAppend;
    $stmt = $con->prepare($SCondition);
    return $stmt->execute(array(':data_update'=>$data_update,':data_select'=>$data_select));
}