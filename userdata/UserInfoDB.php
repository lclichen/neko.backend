<?php

class UserInfoDB
{
    private $openid;
	private $sessionKey;

    //保存登录用户信息
    public function __construct($sessionKey,$openid)
	{
		$this->openid = $openid;
		$this->sessionKey = $sessionKey;
	}
    

    public function addUserInfo($sessionKey,$openid)
    {
        //连接数据库
        include_once __DIR__."/../common.php";
        
        $con = pdo_database(); //连接mysql服务并选择数据库
        
        $sth = $con->prepare("SELECT openid FROM userinfo WHERE openid = :openid");
        $sth->execute(array(':openid'=>$openid));
        $matchid = $sth->fetch(PDO::FETCH_ASSOC)['openid'];
        $timestamp = time();

        $token = sha1($openid.$timestamp);
        if($matchid !== null){//数据库中已有该用户
            update_once($con,"userinfo","login_sessionkey",$sessionKey,"openid",$openid);
            update_once($con,"userinfo","login_timestamp",$timestamp,"openid",$openid);
            update_once($con,"userinfo","login_token",$token,"openid",$openid);
        }
        else{//数据库中没有该用户
            $voidAvatarUrl = "https://thirdwx.qlogo.cn/mmopen/vi_32/POgEwh4mIHO4nibH0KlMECNjjGxQUq24ZEaGT4poC6icRiccVGKSyXwibcPq4BWmiaIGuG1icwxaQX6grC9VemZoJ8rg/132";
            $stmt = $con->prepare(
                "INSERT INTO userinfo (avatarUrl,nickName,openid,login_sessionkey,login_timestamp,login_token,admin)".
                " VAlUES (:avatarUrl,:nickName,:openid,:login_sessionkey,:login_timestamp,:login_token,:admin)");
            $dataInsert = array(":avatarUrl"=>$voidAvatarUrl,":nickName"=>"微信用户",":openid"=>$openid,
            ":login_sessionkey"=>$sessionKey,":login_timestamp"=>$timestamp,":login_token"=>$token,
            ":admin"=>'u',);
            $stmt->execute($dataInsert);
        }
        $con=null;
        return $token;
    }
}

