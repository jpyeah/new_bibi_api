<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/19
 * Time: 上午11:50
 */

use Qiniu\Auth;
class UserController extends ApiYafControllerAbstract
{

    public function registerAction()
    {

        $this->required_fields = array_merge($this->required_fields, array('mobile', 'password', 'code', 'nickname'));

        $data = $this->get_request_data();

        unset($data['v3/user/register']);
        //unset($data['code']);
        $key =  $key = 'code_' . $data['mobile'] . '';
        $code = RedisDb::getValue($key);

        if($code != $data['code']){
            $this->send_error(USER_CODE_ERROR);
        }

        unset($data['code']);

        $time = time();

        $data['login_ip'] = $_SERVER['REMOTE_ADDR'];
        $data['login_time'] = $time;
        $data['created'] = $time;
        $data['updated'] = $time;

        $name = 'bibi_' . Common::randomkeys(6);

        $data['username'] = $name;

        $data['source_type']=2;

        $nickname = $data['nickname'];
        unset($data['nickname']);

        $len = strlen($nickname);

        if ($len < 4 || $len > 30) {

            $this->send_error(USER_NICKNAME_FORMAT_ERROR);

        }


        unset($data['nickname']);

        $userModel = new \UserModel;

        $user = $userModel->getInfoByMobile($data['mobile']);

        if ($user) {
            $this->send_error(USER_MOBILE_REGISTERED);
        }

        $device_identifier = $data['device_identifier'];

        /*
        //同步推正
        $device_id=Common::shiwan($device_identifier);
        if($device_id){
            $key = 'shiwan_callback' . $device_id . '';
            $callback = RedisDb::getValue($key);
             $url=urldecode($callback);
             //RedisDb::delValue($key);
            if($url){
                 $html = file_get_contents($url);
            }



        }
        */
        unset($data['device_identifier']);

        $userId = $userModel->register($data);

        if (!$userId) {

            $this->send_error(USER_REGISTER_FAIL);

        }

        $sessionData = array('device_identifier' => $device_identifier, 'user_id' => $userId);
        $sess = new SessionModel();
        $sessId = $sess->Create($sessionData);


        $profileModel = new \ProfileModel;
        $profileInfo = array();
        $profileInfo['user_id'] = $userId;
        $profileInfo['user_no'] = $name;
        $profileInfo['nickname'] = $nickname;
        $profileInfo['avatar']   = AVATAR_DEFAULT;
        $profileInfo['bibi_no']  =$userId+10000;

        $profileModel->initProfile($profileInfo);

        $userInfo = $userModel->getInfoById($userId);
        $userInfo['profile'] = $profileModel->getProfile($userId);


        $response = array();
        $response['session_id'] = $sessId;
        $response['user_info'] = $userInfo;
        $response['user_info']['chat_token'] = $this->getRcloudToken($userId,$nickname,AVATAR_DEFAULT);

        $this->send($response);


    }

    public function forgetpasswordAction(){
        $this->required_fields = array_merge($this->required_fields, array('mobile', 'password', 'code'));

        $data = $this->get_request_data();
        unset($data['v2/user/forgetpassword']);
        $device_identifier = $data['device_identifier'];
        unset($data['device_identifier']);
        //unset($data['code']);
        $key =  $key = 'code_' . $data['mobile'] . '';
        $code = RedisDb::getValue($key);

        if($code != $data['code']){
            $this->send_error(USER_CODE_ERROR);
        }
        unset($data['code']);

        $time = time();

        $data['login_ip'] = $_SERVER['REMOTE_ADDR'];
        $data['updated'] = $time;
        $mobile=$data['mobile'];

        $userModel = new \UserModel;


        $user = $userModel->getInfoByMobile($data['mobile']);

        if (!$user) {
            $this->send_error(USER_MOBILE_FORGETPASS);
        }

        $userrow= $userModel->changepass($data);

        if (!$userrow) {

            $this->send_error(USER_CHANGEPASS_FAIL);

        }

        $userId=$user[0]['user_id'];

        $response = array();

        $sessionData = array('device_identifier' => $device_identifier, 'user_id' => $userId);
        //删除sessionId
        $sess = new SessionModel();
        $sessId = $sess->Create($sessionData);

        $time = time();

        $profile = new \ProfileModel;

        $info['profile'] = $profile->getProfile($userId);
        $response['session_id'] = $sessId;
        $response['user_info'] = $info;

        $nickname = $info['profile']['nickname'];
        $response['user_info']['chat_token'] = $this->getRcloudToken($userId,$nickname,AVATAR_DEFAULT);

        $this->send($response);
    }


    public function loginAction()
    {

        $this->required_fields = array_merge($this->required_fields, array('mobile', 'password'));

        $data = $this->get_request_data();

        $user = new \UserModel;

        $info = $user->login($data['mobile'], $data['password']);

        if (!$info) {

            $this->send_error(USER_LOGIN_FAIL);
        }

        $userId = $info['user_id'];
        $device_identifier = $data['device_identifier'];



        $response = array();

        $sessionData = array('device_identifier' => $device_identifier, 'user_id' => $userId);
        //删除sessionId
        $sess = new SessionModel();
        $sessId = $sess->Create($sessionData);

        $time = time();

        $profile = new \ProfileModel;

        $info['profile'] = $profile->getProfile($userId);
        $response['session_id'] = $sessId;
        $response['user_info'] = $info;

        $nickname = $info['profile']['nickname'];
        $response['user_info']['chat_token'] = $this->getRcloudToken($userId,$nickname,AVATAR_DEFAULT);

        $this->send($response);

    }

    /**
     * @api {POST} /v1/User/updateProfile 用户资料更新
     * @apiName user updateProfile
     * @apiGroup User
     * @apiDescription 用户资料更新
     * @apiPermission anyone
     * @apiSampleRequest http://new.bibicar.cn
     * @apiVersion 1.0.0
     *
     * @apiParam {string} [device_identifier] 设备唯一标识
     * @apiParam {string} [session_id] session_id
     * @apiParam {string} [key] 键值 nickname birth avatar gender signature
     * @apiParam {string} [value] 值
     *
     * @apiParamExample {json} 请求样例
     *   POST /v1/User/updateProfile
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "key":"",
     *       "value":"",
     *
     *
     *     }
     *   }
     *
     */
    public function updateProfileAction()
    {

        $this->required_fields = array_merge($this->required_fields, array('session_id', 'key', 'value'));

        $data = $this->get_request_data();

        $user_id = $this->userAuth($data);

        $profileKey = array('nickname', 'birth', 'avatar', 'gender', 'signature');

        $key = $data['key'];

        if (!in_array($key, $profileKey)) {

            $this->send_error(USER_PROFILE_KEY_ERROR);

        }

        $value = $data['value'];
        $profile = new ProfileModel();

        $update = array();
        $update[$key] = $value;


        switch ($key) {

            case 'nickname':
                // $result = $profile->updateProfileByKey($user_id, $update);
                break;
            case 'birth':

                $date = explode('-', $value);

                list($year, $month, $day) = $date;

                unset($update['birth']);
                $update['year'] = $year;
                $update['month'] = $month;
                $update['day'] = $day;

                $cons = Common::get_constellation($month, $day);

                $update['constellation'] = $cons;
                $update['age'] = Common::birthday($value);

                break;

            case 'avatar':

                $file = new FileModel();
                $fileUrl = $file->Get($data['value']);
                $update['avatar'] = $fileUrl;

                break;

        }

        $result = $profile->updateProfileByKey($user_id, $update);


        if ($result >= 0) {

            $userM = new UserModel();
            $userInfo = $userM->getInfoById($user_id);
            $userInfo['profile'] = $profile->getProfile($user_id);
            $response['user_info'] = $userInfo;
            $this->send($response);
        } else {
            $this->send_error(USER_PROFILE_UPDATE_FAIL);
        }

    }

    /**
     * @api {POST} /v1/User/profile 用户信息
     * @apiName user profile
     * @apiGroup User
     * @apiDescription 用户信息
     * @apiPermission anyone
     * @apiSampleRequest http://new.bibicar.cn
     * @apiVersion 1.0.0
     *
     * @apiParam {string} [device_identifier] 设备唯一标识
     * @apiParam {string} [session_id] session_id
     *
     * @apiParamExample {json} 请求样例
     *   POST /v1/User/profile
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *
     *
     *     }
     *   }
     *
     */
    public function profileAction()
    {

        $this->required_fields = array_merge($this->required_fields, array('session_id'));
        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $userM = new UserModel();
        $userInfo = $userM->getInfoById($userId);

        $profileM = new ProfileModel();
        $profile = $profileM->getProfile($userId);

        $userInfo['profile'] = $profile;

        $response['user_info'] = $userInfo;

        $this->send($response);

    }

    /**
     * @api {POST} /v1/User/homepage  个人中心
     * @apiName user homepage
     * @apiGroup User
     * @apiDescription 个人中心
     * @apiPermission anyone
     * @apiSampleRequest http://new.bibicar.cn
     * @apiVersion 1.0.0
     *
     * @apiParam {string} device_identifier 设备唯一标识
     * @apiParam {string} session_id session_id
     *
     * @apiParamExample {json} 请求样例
     *   POST /v1/User/homepage
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *
     *     }
     *   }
     *
     */
    public function homepageAction(){

        $this->required_fields = array_merge($this->required_fields, array('session_id'));
        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $userM = new UserModel();
        $userInfo = $userM->getInfoById($userId);

        $profileM = new ProfileModel();
        $profile = $profileM->getProfile( $userId);

        if($userId){

            $updateProfile['current_version'] = isset($data['current_version'])?$data['current_version']:1 ;

            $profileM->updateProfileByKey($userId, $updateProfile);

        }

        $userInfo['profile'] = $profile;

        $response['user_info'] =$userInfo;

        $this->send($response);
    }

    /**
     * @api {POST} /v1/User/quicklogin 用户登录/注册
     * @apiName user quicklogin
     * @apiGroup User
     * @apiDescription 用户登录/注册
     * @apiPermission anyone
     * @apiSampleRequest http://new.bibicar.cn
     * @apiVersion 1.0.0
     *
     * @apiParam {string} [device_identifier] 设备唯一标识
     * @apiParam {string} [mobile] 手机号码
     * @apiParam {string} [code] 验证码
     *
     * @apiParamExample {json} 请求样例
     *   POST /v1/User/quicklogin
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "mobile":"",
     *       "code":"",
     *
     *
     *     }
     *   }
     *
     */
    public function quickloginAction()
    {
        $this->required_fields = array_merge($this->required_fields, array('mobile','code'));

        $data = $this->get_request_data();

        $key ='code_' . $data['mobile'] . '';
        $code = RedisDb::getValue($key);

        if($code != $data['code']){
              $this->send_error(USER_CODE_ERROR);
        }
        RedisDb::delValue($key);
        unset($data['code']);
        $userModel = new \UserModel;
        $user = $userModel->getInfoByMobile($data['mobile']);
        if ($user) {
            $userId = $user[0]['user_id'];
            $device_identifier = $data['device_identifier'];
            $response = array();
            $sessionData = array('device_identifier' => $device_identifier, 'user_id' => $userId);
            //删除sessionId
            $sess = new SessionModel();
            $sessId = $sess->Create($sessionData);

            $profileModel = new \ProfileModel;

            $updateProfile['current_version'] = isset($data['current_version'])?$data['current_version']:1;

            $profileModel->updateProfileByKey($userId, $updateProfile);
        }else{
            $time = time();
            $data['login_ip'] = $_SERVER['REMOTE_ADDR'];
            $data['login_time'] = $time;
            $data['created'] = $time;
            $data['updated'] = $time;
            $data['username']= 'bibi_' . Common::randomkeys(6);
            $data['password']=md5('12345');
            $data['source_type']=2;
            $device_identifier = $data['device_identifier'];
            unset($data['device_identifier']);
            $userId = $userModel->register($data);
            if (!$userId) {
                $this->send_error(USER_REGISTER_FAIL);
            }
            $sessionData = array('device_identifier' => $device_identifier, 'user_id' => $userId);
            $sess = new SessionModel();
            $sessId = $sess->Create($sessionData);
            $profileModel = new \ProfileModel;
            $profileInfo = array();
            $profileInfo['user_id'] = $userId;
            $profileInfo['user_no'] = $data['username'];
            $profileInfo['nickname'] = $data['mobile']."手机用户";
            $profileInfo['avatar']   = AVATAR_DEFAULT;
            $profileInfo['bibi_no']  =$userId+10000;
            $profileInfo['current_version'] = isset($data['current_version'])?$data['current_version']:1;
            $profileModel->initProfile($profileInfo);

        }
        $profileModel = new \ProfileModel;
        $userInfo = $userModel->getInfoById($userId);
        $userInfo['profile'] = $profileModel->getProfile($userId);
        $response = array();
        $response['session_id'] = $sessId;
        $response['user_info'] = $userInfo;
        $response['user_info']['chat_token'] = $this->getRcloudToken($userId,$userInfo['profile']['nickname'],AVATAR_DEFAULT);
        $this->send($response);

    }


    /**
     * @api {POST} /v1/User/oauthlogin 第三方登录
     * @apiName user oauthlogin
     * @apiGroup User
     * @apiDescription 第三方登录
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.0.0
     * @apiParam (request) {string} device_identifier 设备唯一标识
     * @apiParam (request) {string} [wx_open_id] 微信识别ID
     * @apiParam (request) {string} [weibo_open_id]  微博识别ID
     * @apiParam (request) {string} nickname  昵称
     * @apiParam (request) {string} avatar 头像
     *
     * @apiParam (response) {number} is_bind_mobile 是否绑定手机 1：是 2：否
     *
     * @apiParamExample {json} 请求样例
     *   POST /v1/User/oauthlogin
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *
     *
     *     }
     *   }
     *
     */
    public function oauthloginAction(){

        $this->required_fields = array_merge(
            $this->required_fields,
            array('wx_open_id','weibo_open_id','nickname','avatar')
        );

        $data = $this->get_request_data();

        $userModel = new \UserModel;
        $profileModel = new \ProfileModel;

        $wx_open_id = $data['wx_open_id'];
        $weibo_open_id = $data['weibo_open_id'];

        $avatar = $data['avatar'];
        $nickname = $data['nickname'];

        $oauth['wx_open_id'] =  preg_match("/[A-Za-z0-9]+/", $wx_open_id) ? $wx_open_id : '';
        $oauth['weibo_open_id'] = preg_match("/[A-Za-z0-9]+/", $weibo_open_id) ? $weibo_open_id : '';

        $info = $userModel->loginByOauth($oauth);

        $time=time();
        $response = array();

        if (!$info) {

            $insert = array();
            $insert['login_ip'] = $_SERVER['REMOTE_ADDR'];
            $insert['login_time'] = $time;
            $insert['created'] = $time;
            $insert['updated'] = $time;

            $name = 'bibi_' . Common::randomkeys(6);

            $insert['username'] = $name;
            $insert['wx_open_id'] = $data['wx_open_id'];
            $insert['weibo_open_id'] = $data['weibo_open_id'];
            $insert['source_type']=2;
            $userId = $userModel->register($insert);
            $profileInfo = array();
            $profileInfo['user_id'] = $userId;
            $profileInfo['user_no'] = $name;
            $profileInfo['nickname'] = $nickname;
            $profileInfo['avatar']   = $avatar;
            $profileInfo['bibi_no']  =$userId+10000;
            $profileInfo['current_version'] = isset($data['current_version'])?$data['current_version']:1;
            $profileModel->initProfile($profileInfo);

            $response['is_bind_mobile'] =2;

            //$this->send_error(USER_OAUTH_UPDATE_PROFILE);
        }else{
            $userId = $info['user_id'];

            $update['updated'] = $time;

            $userModel->update(array('user_id'=>$userId),$update);

          //  $updateProfile['nickname'] = $data['nickname'];

          //  $updateProfile['avatar']   = $data['avatar'];

            $updateProfile['current_version'] = isset($data['current_version'])?$data['current_version']:1;

            $profileModel->updateProfileByKey($userId, $updateProfile);

            $info['mobile'] ? $response['is_bind_mobile'] = 1 : $response['is_bind_mobile'] =2;

        }

        $device_identifier = $data['device_identifier'];

        $sessionData = array('device_identifier' => $device_identifier, 'user_id' => $userId);
        //删除sessionId
        $sess = new SessionModel();
        $sessId = $sess->Create($sessionData);

        $userInfo = $userModel->getInfoById($userId);
        $userInfo['profile'] = $profileModel->getProfile($userId);

        $response['session_id'] = $sessId;
        $response['user_info'] = $userInfo;
        $response['user_info']['chat_token'] = $this->getRcloudToken($userId,$nickname,AVATAR_DEFAULT);

        $this->send($response);
    }

    /**
     * @api {POST} /v1/user/oauthbindmobile 第三方登录绑定手机号
     * @apiName User  oauthbindmobile
     * @apiGroup User
     * @apiDescription  第三方登录绑定手机号
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.0.0
     * @apiParam {string} device_identifier device_identifier
     * @apiParam {string} session_id session_id
     * @apiParam {string}  mobile 手机号码
     * @apiParam {number} code 验证码
     *
     * @apiParamExample {json} 请求样例
     *   POST /v1/user/oauthbindmobile
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "mobile":"",
     *       "code":"",
     *
     *
     *     }
     *   }
     *
     */
    public function oauthbindmobileAction(){

        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id', 'mobile','code')
        );

        $data = $this->get_request_data();

        $key =  $key = 'code_' . $data['mobile'] . '';
        $code = RedisDb::getValue($key);

        if($code != $data['code']){
            $this->send_error(USER_CODE_ERROR);
        }

        unset($data['code']);

        if(@$data['session_id']){
            $sess = new SessionModel();
            $userId = $sess->Get($data);
        }

        if(!$userId){
            $this->send_error('USER_AUTH_FAIL');
        }

        $userModel = new \UserModel;
        $profileModel = new \ProfileModel;

        $user = $userModel->getInfoByMobile($data['mobile']);

        if ($user) {
            $this->send_error(USER_MOBILE_REGISTERED);
        }

        $update['mobile'] = $data['mobile'];

        $userModel->update(array('user_id'=>$userId),$update);

        $userInfo = $userModel->getInfoById($userId);
        $userInfo['profile'] = $profileModel->getProfile($userId);

        $response['userinfo']=$userInfo;

        $this->send($response);
    }

    /**
     * @api {POST} /v1/User/chattoken 融云token刷新
     * @apiName user chattoken
     * @apiGroup User
     * @apiDescription 融云消息刷新
     * @apiPermission anyone
     * @apiSampleRequest http://new.bibicar.cn
     * @apiVersion 1.0.0
     * @apiParam {string} [device_identifier] 设备唯一标识
     * @apiParam {string} [session_id] session_id
     *
     * @apiParamExample {json} 请求样例
     *   POST /v1/User/chattoken
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *
     *
     *     }
     *   }
     *
     */
    public function chattokenAction(){

        $this->required_fields = array_merge($this->required_fields, array('session_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $profileModel = new ProfileModel();

        $profile = $profileModel->getProfile($userId);

        $chatToken = $this->getRcloudToken($userId, $profile['nickname'],$profile['avatar']);

        $response['chat_token'] = $chatToken;

        $this->send($response);
    }


    public function loginbymobileAction()
    {

        $this->required_fields = array_merge($this->required_fields, array('mobile', 'code'));

        $data = $this->get_request_data();

        $key =  $key = 'code_' . $data['mobile'] . '';
        $code = RedisDb::getValue($key);

        if(!$data['code']){

            $this->send_error(USER_CODE_ERROR);
        }

        if($code != $data['code']){

            $this->send_error(USER_CODE_ERROR);
        }

        if(!$data['mobile']){
            $this->send_error(USER_LOGIN_FAIL);

        }

        $userModel = new \UserModel;

        $info = $userModel->getInfoByMobile($data['mobile']);

        if (!$info) {

            $this->send_error(USER_LOGIN_FAIL);
        }

        $userId = $info[0]['user_id'];

        $device_identifier = $data['device_identifier'];

        $response = array();

        $sessionData = array('device_identifier' => $device_identifier, 'user_id' => $userId);
        //删除sessionId
        $sess = new SessionModel();
        $sessId = $sess->Create($sessionData);

        $profileModel = new \ProfileModel;

        $userInfo = $userModel->getInfoById($userId);
        $userInfo['profile'] = $profileModel->getProfile($userId);

        $response['session_id'] = $sessId;
        $response['user_info'] = $userInfo;

        $nickname = $userInfo['profile']['nickname'];
        $response['user_info']['chat_token'] = $this->getRcloudToken($userId,$nickname,$userInfo['profile']['avatar']);

        $this->send($response);

    }

    /**
     * @api {POST} /v1/user/bindmobile 绑定手机号
     * @apiName User  bandmobile
     * @apiGroup User
     * @apiDescription  绑定手机号
     * @apiPermission anyone
     * @apiSampleRequest http://new.bibicar.cn
     * @apiVersion 1.0.0
     * @apiParam {string} device_identifier device_identifier
     * @apiParam {string} session_id session_id
     * @apiParam {string}  mobile 手机号码
     * @apiParam {number} code 验证码
     *
     * @apiParamExample {json} 请求样例
     *   POST /v1/user/bindmobile
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "mobile":"",
     *       "code":"",
     *
     *
     *     }
     *   }
     *
     */
    public function bindmobileAction(){

        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id', 'mobile','code')
        );

        $data = $this->get_request_data();

        $key =  $key = 'code_' . $data['mobile'] . '';
        $code = RedisDb::getValue($key);


        if($code != $data['code']){
            $this->send_error(USER_CODE_ERROR);
        }

        unset($data['code']);

        if(@$data['session_id']){
            $sess = new SessionModel();
            $userId = $sess->Get($data);
        }

        if(!$userId){
            $this->send_error('USER_AUTH_FAIL');
        }

        $userModel = new \UserModel;
        $profileModel = new \ProfileModel;

        $user = $userModel->getInfoByMobile($data['mobile']);

        if ($user && ($user[0]['user_id'] != $userId) ) {
            $this->send_error(USER_MOBILE_REGISTERED);
        }

        $update['mobile'] = $data['mobile'];

        $userModel->update(array('user_id'=>$userId),$update);

        $userInfo = $userModel->getInfoById($userId);
        $userInfo['profile'] = $profileModel->getProfile($userId);

        $response['userinfo']=$userInfo;

        $this->send($response);
    }














}