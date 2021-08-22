<?php

class UserInfoDB
{
    private $openid;
	private $sessionKey;
    private $data;

    //保存登录用户信息
    public function __construct($data,$sessionKey,$openid,$isProfile)
	{
		$this->openid = $openid;
		$this->sessionKey = $sessionKey;
		$this->data = $data;
        $this->isProfile = $isProfile;
	}
    

    public function addUserInfo($data,$sessionKey,$openid,$isProfile)
    {
        //连接数据库
        include_once(__DIR__."/../common.php");
        
        $con = pdo_database(); //连接mysql服务并选择数据库
        
        $sth = $con->prepare("SELECT openid FROM userinfo WHERE openid = :openid");
        $sth->execute(array(':openid'=>$openid));
        $matchid = $sth->fetch(PDO::FETCH_ASSOC)['openid'];

        $token = sha1($openid.$data['watermark']['timestamp']);
        if($matchid !== null){//数据库中已有该用户
            update_once($con,"userinfo","login_sessionkey",$sessionKey,"openid",$openid);
            update_once($con,"userinfo","login_timestamp",$data['watermark']['timestamp'],"openid",$openid);
            update_once($con,"userinfo","login_token",$token,"openid",$openid);

            if($isProfile || $data['nickName'] != '微信用户'){
                update_once($con,"userinfo","avatarUrl",$data['avatarUrl'],"openid",$openid);
                update_once($con,"userinfo","nickName",$data['nickName'],"openid",$openid);
                update_once($con,"userinfo","gender",$data['gender'],"openid",$openid);
                update_once($con,"userinfo","province",$data['province'],"openid",$openid);
                update_once($con,"userinfo","city",$data['city'],"openid",$openid);
                update_once($con,"userinfo","country",$data['country'],"openid",$openid);
            }
        }
        else{//数据库中没有该用户
            $stmt = $con->prepare("INSERT INTO userinfo (avatarUrl,nickName,gender,province,city,country,openid,login_sessionkey,login_timestamp,login_token,admin) VAlUES (:avatarUrl,:nickName,:gender,:province,:city,:country,:openid,:login_sessionkey,:login_timestamp,:login_token,:admin)");
            $data_insert = array(":avatarUrl"=>$data['avatarUrl'],":nickName"=>$data['nickName'],":gender"=>$data['gender'],":province"=>$data['province'],":city"=>$data['city'],":country"=>$data['country'],":openid"=>$openid,":login_sessionkey"=>$sessionKey,":login_timestamp"=>$data['watermark']['timestamp'],":login_token"=>$token,":admin"=>'u',);
            $stmt->execute($data_insert);
        }
        $con=null;
        return $token;
    }
}

