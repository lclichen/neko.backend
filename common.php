<?php
include_once __DIR__ . "/config.php";

function test()
{
    echo $GLOBALS['dbhost'];
    echo "\nThat A Test Function~\n";
    die();
}

/**
 * 网络请求通用接收函数
 * 返回接收到的数据，格式为数组，array（类似于Python中的字典，大概）
 * 具体来说，按$_GET、$_POST、json内容的顺序，依次读取并合并到$data变量中，最终返回$data变量
 */
function initPostData()
{
    $data = array();
    if (!empty($_GET)) {
        $data = array_merge($data, $_GET);
        // print_r("GET-".$data);
        return $data;
    }
    if (!empty($_POST) && $_SERVER["CONTENT_TYPE"] != 'application/json') {
        $data = array_merge($data, $_POST);
        // print_r("POST-".$_SERVER["CONTENT_TYPE"].$data);
        return $data;
    }
    $content = file_get_contents('php://input');
    //print_r($content);
    $data = array_merge($data, json_decode($content, true));
    /*if(empty($data)){
        die("Empty!");
    }*/
    return $data;
}

/**
 * 网络请求通用发送函数
 */
function execCURL($ch)
{
    $response = curl_exec($ch);
    $error    = curl_error($ch);
    $result   = array(
        'header' => '',
        'content' => '',
        'curl_error' => '',
        'http_code' => '',
        'last_url' => ''
    );

    if ($error != "") {
        $result['curl_error'] = $error;
        return $result;
    }

    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $result['header'] = str_replace(array("\r\n", "\r", "\n"), "<br/>", substr($response, 0, $header_size));
    $result['content'] = substr($response, $header_size);
    $result['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $result['last_url'] = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $result["base_resp"] = array();
    $result["base_resp"]["ret"] = $result['http_code'] == 200 ? 0 : $result['http_code'];
    $result["base_resp"]["err_msg"] = $result['http_code'] == 200 ? "ok" : $result["curl_error"];

    return $result;
}

/**
 * 发送HTTP GET请求，函数名应进行修改。
 */
function http_get($url)
{
    $oCurl = curl_init();
    if (stripos($url, "https://") !== false) {
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
    }
    curl_setopt($oCurl, CURLOPT_URL, $url);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($oCurl, CURLOPT_VERBOSE, 1);
    curl_setopt($oCurl, CURLOPT_HEADER, 1);

    // $sContent = curl_exec($oCurl);
    // $aStatus = curl_getinfo($oCurl);
    $sContent = execCURL($oCurl);
    curl_close($oCurl);

    return $sContent;
}

/**
 * 发送HTTP POST请求，函数名应进行修改。
 * $IsJson标记为，参数采用json方式发送或者不。
 */
function http_post($url, $param, $IsJson = false)
{
    $oCurl = curl_init();

    if (stripos($url, "https://") !== false) {
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
    }
    $strPOST = json_encode($param);

    curl_setopt($oCurl, CURLOPT_URL, $url);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($oCurl, CURLOPT_POST, true);
    curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
    curl_setopt($oCurl, CURLOPT_VERBOSE, 1);
    if ($IsJson) {
        $header = array(
            "Content-Type: application/json",
            "Content-Length: " . strlen($strPOST) . "",
            "Accept: application/json"
        );
    } else {
        $header = null;
    }
    if (!empty($header)) {
        curl_setopt($oCurl, CURLOPT_HTTPHEADER, $header);
    }
    curl_setopt($oCurl, CURLOPT_HEADER, 1);

    // $sContent = curl_exec($oCurl);
    // $aStatus  = curl_getinfo($oCurl);

    $sContent = execCURL($oCurl);
    curl_close($oCurl);

    return $sContent;
}

/**
 * 发送企业微信（Server酱）请求，函数名应进行修改。
 * $IsJson标记为，参数采用json方式发送或者不。
 */
function sc_send($title, $text = '', $type = 'text', $touser = '', $toparty = '', $IsJson = false)
{
    include_once "config.php";
    $content = array(
        'type' => $type,
        'title' => $title,
        'content' => $text,
        'key' => $GLOBALS['weixin_key'],
        'touser' => $touser,
        'toparty' => $toparty
    );
    return $result = http_post('https://send.4c43.work/sendMsg3.php', $content, $IsJson);
}

/**
 * 设定新的用户消息，对messages表进行操作
 * 由系统进行函数调用，暂时不开放给用户
 */
function setNewMsg($con, $openid, $msg)
{
    $sqlNewMsg = "INSERT INTO `messages` (openid,msg) VALUES (:openid, :msg)";
    $sthNewMsg = $con->prepare($sqlNewMsg, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    return $sthNewMsg->execute(array(':openid' => $openid,':msg' => $msg));
}

/**
 * 转换用户消息状态为已读，对messages表进行操作
 * 传入用户ID、消息ID、用户Token，在函数内部进行鉴权（？后面看情况决定要不要改到外部鉴权）。
 */
function readMsg($con, $openid, $msgid, $token)
{
    echo "readMsg";
    update_once($con, "messages", "msg_status", 1, "msgid", $msgid,"");
}

/**
 * 删除用户消息，对messages表进行操作
 * 传入用户ID、消息ID、用户Token，在函数内部进行鉴权（？后面看情况决定要不要改到外部鉴权）。
 */
function removeMsg($con, $openid, $msgid, $token)
{
    echo "removeMsg";
}

function cat_imgco_link($link)
{
    if ($link) {
        return "https://nekoustc.hk.ufileos.com/imgco/$link";
    }
}
function tts_marblue_link($text = "", $ls = "")
{
    if ($text) {
        return "https://voice.4c43.work/speak?text=".$text."&ls=".$ls;
    }
}

/**
 * 发送QQ群（科大猫咪群）请求，函数名应进行修改。
 * $IsJson标记为，参数采用json方式发送或者不。
 */
function qgroupSend($msgdata, $groupId = 237734741)
{
    $msgToCQHTTP = "";
    $cqSource = array("&","[","]",",");// CQ码字符转义
    $cqDisten = array("&amp;","&#91;","&#93;","&#44;");
    foreach ($msgdata as $key => $value) {
        switch ($key) {
            case 'text'://文本
                $msgToCQHTTP .= $value;
                break;
            case 'CQ_i'://CQ码，image
                $valueR = str_replace($cqSource, $cqDisten, $value);
                $msgToCQHTTP .= "[CQ:image,file=".$valueR."]";
                break;
            case 'CQ_f'://CQ码，face
                $msgToCQHTTP .= "[CQ:face,id=".$value."]";
                break;
            case 'CQ_s':
                $msgToCQHTTP .= "[CQ:record,file=".$value."]";
                break;
            default:
                # code...
                break;
        }
    }
    include_once "config.php";
    $content = array(
        'group_id' => $groupId,
        'access_token' => $GLOBALS['qqbot_access_token'],
        'message' => $msgToCQHTTP
    );
    $url = 'http://cv.4c43.work:5700/send_group_msg?access_token='.$GLOBALS['qqbot_access_token'];
    return http_post($url, $content, true);// 采用json方式发送消息至QQbot客户端
}

/**
 * 数据库连接建立通用函数，名字待修订
 */
function pdo_database()
{
    $dbms = $GLOBALS['dbms'];
    $dbhost = $GLOBALS['dbhost'];
    $dbname = $GLOBALS['dbname'];
    $dsn = "$dbms:host=$dbhost;dbname=$dbname;charset=utf8mb4;";
    return new PDO($dsn, $GLOBALS['dbuser'], $GLOBALS['dbpass'], array( //初始化一个PDO对象
        PDO::ATTR_PERSISTENT => true
    )); //dbh
}

/**
 * 通过token获得用户权限定义
 * 目前本函数是获得来自用户表的整体权限值，而不是针对某个档案、图片、或其他内容的
 * @param mixed $con 数据库连接
 * @param string $token 用户token
 * @return mixed array[用户openid,用户权限值(),用户昵称]
 * 
 * token->在userinfo表中查询用户整体权限值，超级管理员s和管理员a具有所有档案的编辑权限，并且返回用户的唯一标识：openid，普通用户需要利用openid进行后续的鉴权。
 * openid本身只在后端内部进行流转，不进行输入和输出。
 * 调用的不同情况：
 * 1.编辑，需要有catid，因此鉴权为普通用户后进入下一步流程pdoCheckCatEditPrivilege
 * 2.其他操作，比如仅限管理员进行的操作，就直接往后进行了。
 * 3.对userpower表的操作，userpower表主要针对的也是档案本身。因此和1类似
 */
function pdoCheckUserPrivilege($con, $token)
{
    $sql = 'SELECT openid,admin,nickName FROM `userinfo` WHERE login_token = :token';
    $sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->execute(array(':token' => $token));
    $result = $sth->fetch(PDO::FETCH_ASSOC);
    return [$result['openid'], $result['admin'], $result['nickName']];
}

/**
 * 通过qq.ustc.life进行统一身份认证检查，用于QQbot（依赖于home.ustc，未来可能会弃用）
 * @param string $ustcCheckNum 身份证明码
 * @return array [学号,QQ号]
 */
function ustcCheck($ustcCheckNum)
{
    $result = http_get("https://qq.ustc.life/p/"+$ustcCheckNum);
    if ($result['http_code'] == 200) {
        preg_match('<p>证明码：(\d+)<\/p>\s+<p>学号（或教工号）：(\S+)<\/p>\s+<p>QQ 号：(\d+)<\/p>', $result['content'], $reg);
        $stuid = $reg[2];
        $qqid = $reg[3];
    }
    //re.search(r"<p>证明码：(\d+)<\/p>\s+<p>学号（或教工号）：(\S+)<\/p>\s+<p>QQ 号：(\d+)<\/p>",resp.text)
    return [$stuid,$qqid];
}

/**
 * 通过userpower表鉴权，原子化授权表。
 */
function pdoCheckCatEditPrivilege($con, $openid, $catid)//新的权限检查
{
    // 需要把userpower表改一下
    // 启用需要改动：upCat增加对userpower表的更新，然后把已有的owner信息同步进去。
    // 捋捋：select catid,openid
    // INSERT INTO userpower (catid,openid) SELECT id,openid FROM catsinfo;
    // 已经执行完毕了。
    if ((int)$catid) { // ,auth_period
        $sql = "SELECT power FROM `userpower` WHERE catid = :catid AND openid = :openid";
    }
    $sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->execute(array(':catid' => $catid,':openid' => $openid));
    $result = $sth->fetch(PDO::FETCH_ASSOC);
    //echo $result['openid'];
    if (in_array($result['power'], ["o", "e"], true)){ // || $result['auth_period'] > date("Y-m-d h:i:sa")) {
        return $result['power'];
    } else {
        return 'u';
    }
}

/**
 * image表记录owner鉴权
 * 只有删除图片时需要这个鉴权
 */
function pdfCheckImageOwner($con, $openid, $key)
{
    $sql = "SELECT openid FROM `images` WHERE link = :key";
    $sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->execute(array(':key' => $key));
    $result = $sth->fetch(PDO::FETCH_ASSOC);
    if ($openid != '' && $openid == $result['openid']) {
        return 'o';
    } else {
        return 'u';
    }
}

/**
 * 可能存在的对猫的叫声的上传。
 * 只有删除voice时需要这个鉴权
 */
function pdoCheckVoiceOwner($con, $openid, $key)
{
    $sql = "SELECT openid FROM `voices` WHERE link = :key";
    $sth = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->execute(array(':key' => $key));
    $result = $sth->fetch(PDO::FETCH_ASSOC);
    if ($openid != '' && $openid == $result['openid']) {
        return 'o';
    } else {
        return 'u';
    }
}

/**
 * 数据库更新函数，用于对一个值的更新。
 * @param mixed $con 数据库连接
 * @param string $tabName 数据表名称
 * @param string $colUpdate 要更新的列名
 * @param string $dataUpdate 更新的数据
 * @param string $colSelect 用于查找的列名
 * @param string $dataSelect 用于查找的数据
 * @param string $toAppend 附加字段
 */
function update_once($con, $tabName, $colUpdate, $dataUpdate, $colSelect, $dataSelect, $toAppend = '')
{
    $sqlQuery = "UPDATE $tabName SET $colUpdate = :data_update WHERE $colSelect = :data_select" . $toAppend;
    $stmt = $con->prepare($sqlQuery);
    return $stmt->execute(array(':data_update' => $dataUpdate, ':data_select' => $dataSelect));
}
