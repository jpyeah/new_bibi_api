<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/19
 * Time: 下午11:11
 */

class UserModel extends PdoDb {

    static public $table = 'bibi_user';

    public function __contsruct(){

        parent::__construct();

    }

    public function register($data){

        $id = $this->insert(self::$table , $data);

        return $id;

    }

    public function changepass($data){
        $where=array('mobile'=>$data['mobile']);
        unset($data['mobile']);
        $id =$this->updateByPrimaryKey(self::$table,$where,$data);
        return $id;
    }

    public function getInfoByMobile($mobile){

        $table = self::$table;
        $sql = "SELECT * FROM {$table} WHERE `mobile` = :mobile";
        $param = array(':mobile'=>$mobile);
        $info = $this->query($sql,$param);
        return $info;
    }

    public function getAllInfoById($userId){

        $sql = 'SELECT * FROM bibi_user WHERE `user_id` = :user_id';
        $param = array(':user_id'=>$userId);
        $info = $this->query($sql,$param);

        return isset($info[0]) ? $info[0] : null;

    }

    public function getInfoById($userId){

        $sql = 'SELECT `user_id` ,`username`, `mobile`, `created` FROM '.self::$table.' WHERE `user_id` = :user_id';
        $param = array(':user_id'=>$userId);
        $info = $this->query($sql,$param);

        return isset($info[0]) ? $info[0] : null;

    }

    public function login($mobile , $password){

        $table = self::$table;
        $sql = "SELECT `user_id` ,`username`, `mobile`, `created` FROM {$table} WHERE `mobile` = :mobile AND `password` = :password ";
        $param = array(':mobile'=>$mobile, ':password'=>$password);
        $info = $this->query($sql,$param);

        return isset($info[0]) ? $info[0] : null;

    }

    public function loginByOauth($data){

        $weibo_open_id = $data['weibo_open_id'];
        $wx_open_id = $data['wx_open_id'];
        $table = self::$table;

        $param = array();

        if($wx_open_id){

            $where = '`wx_open_id` = :wx_open_id';
            $param[':wx_open_id'] = $wx_open_id;
        }

        if($weibo_open_id){

            $where = '`weibo_open_id` = :weibo_open_id';
            $param[':weibo_open_id'] = $weibo_open_id;
        }

        $sql = "SELECT `user_id` ,`username`, `mobile`, `created` FROM
                {$table} WHERE {$where}";


        $info = $this->query($sql,$param);

        return isset($info[0]) ? $info[0] : null;

    }


    public static function setUserKeyCache($device_identifier , $user_id){

        $session_id = uniqid('session');

        $keyToUser = 'auth_'.$device_identifier.'_'.$session_id.'';
        $userToKey = 'key_'.$user_id.'';


        $oldAuth =  'auth_' . RedisDb::getValue($userToKey);

        RedisDb::delValue($oldAuth);
        RedisDb::delValue($keyToUser);
        RedisDb::delValue($userToKey);

        RedisDb::setValue($keyToUser, $user_id);
        RedisDb::setValue($userToKey, ''.$device_identifier.'_'.$session_id.'');



        return $session_id;

    }

    public function update($where, $data){

        $this->updateByPrimaryKey(self::$table, $where, $data);

    }

    public function getProfileInfoById($userId){


        $userInfo = $this->getInfoById($userId);

        $profileM = new ProfileModel();
        $profile = $profileM->getProfile($userId);

        $userInfo['profile'] = $profile;

        return $userInfo;

    }

    public function updateGeoById($userId, $lat , $lng){

        $geohashM = new GeoHash();
        $geohash = $geohashM->encode($lat, $lng);

        $sql = '
            UPDATE
            `bibi_user`
            SET
            `lat` = '.$lat.',
            `lng` = '.$lng.',
            `geohash` = "'.$geohash.'"
            WHERE
            `user_id` = '.$userId.'
        ';

        $this->exec($sql);

        return $geohash;
    }


    public function isregister($mobile,$fromuser){
           
        $table = self::$table;
        $sql = "SELECT mobile,user_id,created FROM {$table} WHERE `mobile` = :mobile";
        $param = array(':mobile'=>$mobile);
        $info = $this->query($sql,$param);

        if(isset($info[0])){
            $profileM    = new ProfileModel();
            $friendShipM = new FriendShipModel();
            $profile = $profileM->getProfile($info[0]['user_id']);
            $isfriend = $friendShipM->isFriend($fromuser,$info[0]['user_id']);
            $isfriend ? $info[0]["is_friend"] = 1 : $info[0]["is_friend"]=2;
            $info[0]['profile'] = $profile;
        }
        return isset($info[0]) ? $info[0] : array();

        
    }

    public function getRecommentUser($userId){

        $sql = "SELECT friend_id FROM `bibi_friendship` WHERE  user_id=".$userId;

        $result = $this->query($sql);

        $result = $this->implodeArrayByKey( 'friend_id', $result);


        $sqlrecomuser ="SELECT user_id FROM `bibi_feeds`";

        $sqlrecomuser .=" WHERE user_id not in (".$result.") ORDER BY `feed_id` DESC ,`like_num` DESC limit 40";


        $recomuser = $this->query($sqlrecomuser);

        $recomuser = $this->implodeArrayByKey( 'user_id', $recomuser);


        $sqlrecom ="SELECT user_id FROM `bibi_feeds`";

        $sqlrecom .=" WHERE user_id  in (".$recomuser.") GROUP BY user_id  ORDER BY `user_id` DESC limit 10";

        $data = $this->query($sqlrecom);

        $profileM = new ProfileModel();

        $items=array();

        foreach($data as $k => $val){
            $items[$k]['user_info']=$profileM->getProfile($val['user_id']);
            $items[$k]['user_info']['user_id']=$val['user_id'];
        }
        return $items;

    }

}

