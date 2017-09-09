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


    /**
     * @apiDefine Data
     *
     * @apiParam (data) {string}  [device_identifier=ce32eaab37220890a063845bf6b6dc1a]  设备唯一标示.
     * @apiParam (data) {string}  [session_id=session5845346a59a31]     用户session_id.
     * @apiParam (data) {json}    [mobile_list=18]     车型id默认值是18.
     *
     *
     */

    /**
     * @api {POST} /v3/User/register 用户注册
     * @apiName user register
     * @apiGroup User
     * @apiDescription 用户注册
     * @apiPermission anyone
     * @apiSampleRequest http://www.bibicar.cn:8090
     * @apiVersion 1.0.0
     *
     * @apiParam {string} [device_identifier] 设备唯一标识
     * @apiParam {string} [mobile] 手机号码
     * @apiParam {string} [password] 密码
     * @apiParam {string} [code] 验证码
     * @apiParam {string} [nickname] 昵称
     *
     * @apiParam {json} data object
     * @apiUse DreamParam
     * @apiParamExample {json} 请求样例
     *   POST /v3/User/register
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "mobile":"",
     *       "password":"",
     *       "code":"",
     *       "nickname":"",
     *
     *
     *     }
     *   }
     *
     */
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

    /**
     * @apiDefine Data
     *
     * @apiParam (data) {string}  [device_identifier=ce32eaab37220890a063845bf6b6dc1a]  设备唯一标示.
     * @apiParam (data) {string}  [session_id=session5845346a59a31]     用户session_id.
     * @apiParam (data) {json}    [mobile_list=18]     车型id默认值是18.
     *
     *
     */

    /**
     * @api {POST} /v3/User/companyregister 企业注册
     * @apiName user companyregister
     * @apiGroup User
     * @apiDescription 企业注册
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 1.0.0
     *
     * @apiParam {string} [device_identifier] 设备唯一标识
     * @apiParam {string} [mobile] 手机号码
     * @apiParam {string} [password] 密码
     * @apiParam {string} [code] 验证码
     * @apiParam {string} [nickname] 昵称
     * @apiParam {string} [company] 公司
     *
     * @apiParam {json} data object
     * @apiUse DreamParam
     * @apiParamExample {json} 请求样例
     *   POST /v3/User/companyregister
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "mobile":"",
     *       "password":"",
     *       "code":"",
     *       "nickname":"",
     *       "company":"",
     *
     *
     *     }
     *   }
     *
     */
    public function companyregisterAction()
    {

        $this->required_fields = array_merge($this->required_fields, array('mobile', 'password', 'code', 'nickname','car_tel','car_address'));

        $data = $this->get_request_data();

        unset($data['v3/User/companyregister']);
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
        $data['password'] = $data['password'];

        $car_tel = $data['car_tel'];
        $address = $data['car_address'];

        unset( $data['car_tel']);
        unset( $data['car_address']);

        $nickname = $data['nickname'];
        unset($data['nickname']);

        $company = $data['company'];
        unset($data['company']);

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

        $post_files=$this->uploadfiles($userId,$_FILES);



        $sessionData = array('device_identifier' => $device_identifier, 'user_id' => $userId);
        $sess = new SessionModel();
        $sessId = $sess->Create($sessionData);

        $CompanyUserModel = new CompanyUserModel;
        $CompanyUserInfo = array();
        $CompanyUserInfo['user_id']=$userId;
        $CompanyUserInfo['name']=$nickname;
        $CompanyUserInfo['files']=$post_files;
        $CompanyUserInfo['company']=$company;
        $CompanyUserInfo['created']=time();
        $CompanyUserInfo['telenumber']=$car_tel;
        $CompanyUserInfo['address']=$address;
        $CompanyUserModel->initCompanyUser($CompanyUserInfo);

        $profileModel = new \ProfileModel;
        $profileInfo = array();
        $profileInfo['user_id'] = $userId;
        $profileInfo['user_no'] = $name;
        $profileInfo['nickname'] = $nickname;
        $profileInfo['avatar']   = AVATAR_DEFAULT;
        $profileInfo['bibi_no']  =$userId+10000;
        $profileInfo['type']  =2;

        $profileModel->initProfile($profileInfo);

        $userInfo = $userModel->getInfoById($userId);
        $userInfo['profile'] = $profileModel->getProfile($userId);


        $response = array();
        $response['session_id'] = $sessId;
        $response['user_info'] = $userInfo;
        $response['user_info']['chat_token'] = $this->getRcloudToken($userId,$nickname,AVATAR_DEFAULT);

        $this->send($response);


    }
    /*
    public function uploadtestAction(){

        $result=$this->uploadfiles(1,$_FILES);
        print_r($result);exit;
    }
    */

    public function uploadfiles($userId,$files){

        //上传企业图片
        $accessKey = QI_NIU_AK;
        $secretKey = QI_NIU_SK;

        // 构建鉴权对象
        $auth = new Auth($accessKey, $secretKey);

        // 要上传的空间
        $bucket = 'bibi';

        // 生成上传 Token
        //$token = $auth->uploadToken($bucket);
        $expire = 3600;

        $key = 'uploadToken_' . $userId;

        $policy = array(
            'callbackUrl' => 'http://api.bibicar.cn/index/callback',
            'callbackBody' => '{"fname":"$(fname)", "hash":"$(key)",  "user_id":' . $userId . '}'
        );

        $uploadToken = $auth->uploadToken($bucket, null, $expire, $policy);


        $items = array();

        if($files){

            foreach($files as $k => $file){

                $filePath = $file['tmp_name'];

                // 上传到七牛后保存的文件名
                $key = base64_encode(uniqid('bibi-file'));

                // 初始化 UploadManager 对象并进行文件的上传。
                $uploadMgr = new \Qiniu\Storage\UploadManager();

                list($ret, $err) = $uploadMgr->putFile($uploadToken, $key, $filePath);


                $hash = $ret['data']['hash'];

                if($err != null){

                    $this->send_error(QINIU_UPLOAD_ERROR);
                }

                $items[$k]['name'] = $file['name'];
                $items[$k]['hash'] = $hash;
            }

            return serialize($items);


        }
        else{

            $this->send_error(QINIU_UPLOAD_ERROR);

        }

    }

    /**
     * @api {POST} /v3/User/forgetpassword 修改密码／忘记密码
     * @apiName user forgetpassword
     * @apiGroup User
     * @apiDescription 修改密码
     * @apiPermission anyone
     * @apiSampleRequest http://www.bibicar.cn:8090
     * @apiVersion 1.0.0
     *
     * @apiParam {string} [device_identifier] 设备唯一标识
     * @apiParam {string} [mobile] 手机号码
     * @apiParam {string} [password] 密码
     * @apiParam {string} [code] 验证码
     *
     * @apiParam {json} data object
     * @apiUse DreamParam
     * @apiParamExample {json} 请求样例
     *   POST /v3/User/forgetpassword
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "mobile":"",
     *       "password":"",
     *       "code":"",
     *
     *
     *     }
     *   }
     *
     */
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

    /**
     * @api {POST} /v3/User/sendCode 发送验证码
     * @apiName user send mobile
     * @apiGroup User
     * @apiDescription 发送验证码
     * @apiPermission anyone
     * @apiSampleRequest http://www.bibicar.cn:8090
     * @apiVersion 1.0.0
     *
     * @apiParam {string} [device_identifier] 设备唯一标识
     * @apiParam {string} [mobile] 手机号码
     *
     * @apiParam {json} data object
     * @apiUse DreamParam
     * @apiParamExample {json} 请求样例
     *   POST /v3/User/sendCode
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "mobile":"",
     *
     *
     *     }
     *   }
     *
     */
    public function sendCodeAction()
    {

        $this->required_fields = array_merge($this->required_fields, array('mobile'));

        $code = rand(1000,9999);

        $data = $this->get_request_data();

        $key = 'code_' . $data['mobile'] . '';

        RedisDb::setValue($key, $code);

        RedisDb::getInstance()->expire($key, 60);

        $response = array(
            'code' => $code
        );

        Common::sendSMS($data['mobile'],array($code),"74511");

        $this->send($response);

    }
    /**
     * @api {POST} /v3/User/login 用户登陆
     * @apiName user login
     * @apiGroup User
     * @apiDescription 用户登录
     * @apiPermission anyone
     * @apiSampleRequest http://www.bibicar.cn:8090
     * @apiVersion 1.0.0
     *
     * @apiParam {string} [device_identifier] 设备唯一标识
     * @apiParam {string} [mobile] 手机号码
     * @apiParam {string} [password] 密码
     *
     * @apiParam {json} data object
     * @apiUse DreamParam
     * @apiParamExample {json} 请求样例
     *   POST /v3/User/login
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "mobile":"",
     *       "password":"",
     *
     *
     *     }
     *   }
     *
     */
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

    /*
     * @nickname
     * @birth
     * @signature
     * @user_no
     * @constellationUSER_PROFILE_UPDATE_FAIL
     *
     */
    /**
     * @api {POST} /v3/User/updateProfile 用户资料更新
     * @apiName user updateProfile
     * @apiGroup User
     * @apiDescription 用户资料更新
     * @apiPermission anyone
     * @apiSampleRequest http://www.bibicar.cn:8090
     * @apiVersion 1.0.0
     *
     * @apiParam {string} [device_identifier] 设备唯一标识
     * @apiParam {string} [session_id] session_id
     * @apiParam {string} [key] 键值 nickname birth avatar gender signature
     * @apiParam {string} [value] 值
     *
     * @apiParam {json} data object
     * @apiUse DreamParam
     * @apiParamExample {json} 请求样例
     *   POST /v3/User/updateProfile
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
//            case 'signature':
//
//                break;

            case 'avatar':

                $file = new FileModel();
                $fileUrl = $file->Get($data['value']);
                $update['avatar'] = $fileUrl;

                break;
//
//            case 'gender':
//                break;

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
     * @api {POST} /v3/User/updateAll 用户资料全部更新
     * @apiName user updateAll
     * @apiGroup User
     * @apiDescription 用户资料更新
     * @apiPermission anyone
     * @apiSampleRequest http://www.bibicar.cn:8090
     * @apiVersion 1.0.0
     *
     * @apiParam {string} [device_identifier] 设备唯一标识
     * @apiParam {string} [session_id] session_id
     * @apiParam {string} [nickname] 昵称
     * @apiParam {string} [birth] 生日
     * @apiParam {string} [avatar] 头像
     * @apiParam {string} [gender] session_id
     * @apiParam {string} [signature] 签名
     *
     * @apiParam {json} data object
     * @apiUse DreamParam
     * @apiParamExample {json} 请求样例
     *   POST /v3/User/updateAll
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "nickname":"",
     *       "birth":"",
     *       "avatar":"",
     *       "signature":"",
     *       "gender":"",
     *
     *
     *     }
     *   }
     *
     */
    public function updateAllAction()
    {

        $this->optional_fields = array('nickname', 'birth', 'avatar', 'gender', 'signature');

        $this->required_fields = array_merge($this->required_fields, array('session_id'));

        $data = $this->get_request_data();

        $user_id = $this->userAuth($data);

        $update = array();

        foreach ($data as $k => $pk) {

            if (!in_array($k, $this->optional_fields)) {

                continue;
            }



            switch ($k) {

                case 'birth':

                    if ($data['birth']) {

                        $birth = $data['birth'];
                        $date = explode('-', $birth);

                        if (is_array($date)) {

                            list($year, $month, $day) = $date;

                            $update['year'] = $year;
                            $update['month'] = $month;
                            $update['day'] = $day;

                            $cons = Common::get_constellation($month, $day);

                            $update['constellation'] = $cons;
                            $update['age'] = Common::birthday($birth);
                        }
                    }


                    break;

                case 'avatar':

                    if($data['avatar']){

                        $file = new FileModel();
                        $fileUrl = $file->Get($data['avatar']);
                        $update['avatar'] = $fileUrl;
                    }

                    break;

                case 'gender':
                    $update['gender'] = $data['gender'] ? $data['gender'] : 0;
                    break;

                case 'nickname':

                    if($data['nickname']){

                        $update['nickname'] = $data['nickname'];

                    }

                    break;

                case 'signature':

                    if($data['signature']){

                        $update['signature'] = $data['signature'];

                    }

                    break;
//
//                default:
//
//                    $update[$k] = $data[$k];
//
//                    break;


            }

        }

        $profile = new ProfileModel();

        $profile->updateProfileByKey($user_id, $update);

        $userM = new UserModel();

        $userInfo = $userM->getProfileInfoById($user_id);

        $response = array();
        $response['user_info'] = $userInfo;

        $this->send($response);

    }

    /**
     * @api {POST} /v3/User/profile 用户信息
     * @apiName user profile
     * @apiGroup User
     * @apiDescription 用户信息
     * @apiPermission anyone
     * @apiSampleRequest http://www.bibicar.cn:8090
     * @apiVersion 1.0.0
     *
     * @apiParam {string} [device_identifier] 设备唯一标识
     * @apiParam {string} [session_id] session_id
     *
     * @apiUse DreamParam
     * @apiParamExample {json} 请求样例
     *   POST /v3/User/profile
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

        $car = new CarSellingModel();

        $response['car_info'] = $car->getUserCar($userId);

        $friendShipM = new FriendShipModel();

        $friendShipM->currentUser = $userId;

        $response['friend_num'] = $friendShipM->friendNumCnt();

        $response['fans_num']   = $friendShipM->fansNumCnt();

        $this->send($response);

    }
    /**
     * @api {POST} /v3/User/homepage个人中心
     * @apiName user homepage
     * @apiGroup User
     * @apiDescription 个人中心
     * @apiPermission anyone
     * @apiSampleRequest http://www.bibicar.cn:8090
     * @apiVersion 1.0.0
     * @apiParam {string} device_identifier 设备唯一标识
     * @apiParam {string} session_id session_id
     * @apiParam {number} [user_id]  别人Uid
     *
     * @apiUse DreamParam
     * @apiParamExample {json} 请求样例
     *   POST /v3/User/homepage
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
    public function homepageAction(){

        $this->required_fields = array_merge($this->required_fields, array('session_id'));
        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $otherId = $this->getAccessId($data, $userId);

        $userM = new UserModel();
        $userInfo = $userM->getInfoById($otherId);

        $profileM = new ProfileModel();
        $profile = $profileM->getProfile($otherId);

        $userInfo['profile'] = $profile;

        $response['user_info'] = $userInfo;

        $car = new CarSellingModel();

        $response['car_info'] = $car->getUserCar($otherId);

        $friendShipM = new FriendShipModel();

        $friendShipM->currentUser = $otherId;

        $response['friend_num'] = $friendShipM->friendNumCnt();

        $response['fans_num']   = $friendShipM->fansNumCnt();

        $friendShip = $friendShipM->getMyFriendShip($userId, $otherId);

        $response['is_friend'] = isset($friendShip['user_id']) ? 1 : 2;

        $feedM = new FeedModel();

        $response['feed_num'] = $feedM->getPublishedFeedTotal($data['user_id']);


        // $response['share_title'] = $feed['post_user_info']['profile']['nickname'] . '的车友圈';
        // $response['share_url'] = 'http://share.bibicar.cn/carshare?feed_id='.$data['feed_id'].'';
        // //$response['share_url'] = 'http://wx.bibicar.cn/post/index/feed_id/'.$data['feed_id'].'';

        // $response['share_txt'] = '更多精彩内容尽在bibi,期待您的加入!';
        // $response['share_img'] = $feed['post_files'][0]['file_url'];

//        $publishCar = $car->getUserPublishCar($otherId);
//
//        foreach($publishCar['car_list'] as $k => $car){
//
//            $response['publish_car_list'][] = $car['car_info'];
//        }

        // $response['publish_car_list'] = $publishCar['car_list'];

        $this->send($response);


    }

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

            $userId = $userModel->register($insert);
            $profileInfo = array();
            $profileInfo['user_id'] = $userId;
            $profileInfo['user_no'] = $name;
            $profileInfo['nickname'] = $nickname;
            $profileInfo['avatar']   = $avatar;
            $profileInfo['bibi_no']  =$userId+10000;
            $profileModel->initProfile($profileInfo);

            //$this->send_error(USER_OAUTH_UPDATE_PROFILE);
        }else{
            $userId = $info['user_id'];

            $update['updated'] = $time;

            $userModel->update(array('user_id'=>$userId),$update);

            $updateProfile['nickname'] = $data['nickname'];
            $updateProfile['avatar']   = $data['avatar'];

            $profileModel->updateProfileByKey($userId, $updateProfile);
        }

        $device_identifier = $data['device_identifier'];

        $response = array();

        $sessionData = array('device_identifier' => $device_identifier, 'user_id' => $userId);
        //删除sessionId
        $sess = new SessionModel();
        $sessId = $sess->Create($sessionData);

        $userInfo = $userModel->getInfoById($userId);
        $userInfo['profile'] = $profileModel->getProfile($userId);

        $response = array();
        $response['session_id'] = $sessId;
        $response['user_info'] = $userInfo;
        $response['user_info']['chat_token'] = $this->getRcloudToken($userId,$nickname,AVATAR_DEFAULT);

        $this->send($response);
    }
    /**
     * @api {POST} /v3/User/oauthregister 第三方注册登录
     * @apiName user oauthregister
     * @apiGroup User
     * @apiDescription 用户资料更新
     * @apiPermission anyone
     * @apiSampleRequest http://www.bibicar.cn:8090
     * @apiVersion 1.0.0
     * @apiParam {string} [device_identifier] 设备唯一标识
     * @apiParam {string} [session_id] session_id
     * @apiParam {string} [nickname] 昵称
     * @apiParam {string} [avatar] 头像
     * @apiParam {string} [mobile] 手机号码
     * @apiParam {string} [password] 密码
     * @apiParam {string} [wx_open_id] 微信关联id
     * @apiParam {string} [weibo_open_id] 微博关联ID
     *
     * @apiParam {json} data object
     * @apiUse DreamParam
     * @apiParamExample {json} 请求样例
     *   POST /v3/User/oauthregister
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "nickname":"",
     *       "avatar":"",
     *       "mobile":"",
     *       "password":"",
     *       "wx_open_id":"",
     *       "weibo_open_id":"",
     *
     *
     *     }
     *   }
     *
     */

    public function oauthregisterAction(){

        $this->required_fields = array_merge(
            $this->required_fields,
            array('mobile', 'password', 'code', 'nickname','avatar','wx_open_id','weibo_open_id')
        );

        $data = $this->get_request_data();

        $key =  $key = 'code_' . $data['mobile'] . '';
        $code = RedisDb::getValue($key);

        if($code != $data['code']){

            $this->send_error(USER_CODE_ERROR);
        }

        unset($data['code']);

        $time = time();

        $nickname = $data['nickname'];

        $avatar = $data['avatar'];

        $userModel = new \UserModel;
        $profileModel = new \ProfileModel;

        $info = $userModel->getInfoByMobile($data['mobile']);


        if($info){

            $userId = $info[0]['user_id'];

            $update['password'] = $data['password'];

            if($data['wx_open_id']){

                $update['wx_open_id'] = $data['wx_open_id'];
            }

            if( $data['weibo_open_id']){

                $update['weibo_open_id'] = $data['weibo_open_id'];

            }


            $update['updated'] = $time;

            $userModel->update(array('user_id'=>$userId),$update);

            $updateProfile['nickname'] = $data['nickname'];
            $updateProfile['avatar']   = $data['avatar'];

            $profileModel->updateProfileByKey($userId, $updateProfile);

        }else{

            $insert = array();
            $insert['login_ip'] = $_SERVER['REMOTE_ADDR'];
            $insert['login_time'] = $time;
            $insert['created'] = $time;
            $insert['updated'] = $time;

            $name = 'bibi_' . Common::randomkeys(6);

            $insert['username'] = $name;
            $insert['wx_open_id'] = $data['wx_open_id'];
            $insert['weibo_open_id'] = $data['weibo_open_id'];
            $insert['mobile'] = $data['mobile'];
            $insert['password'] = $data['password'];

            $userId = $userModel->register($insert);

            $profileInfo = array();
            $profileInfo['user_id'] = $userId;
            $profileInfo['user_no'] = $name;
            $profileInfo['nickname'] = $nickname;
            $profileInfo['avatar']   = $avatar;
            $profileInfo['bibi_no']  =$userId+10000;
            $profileModel->initProfile($profileInfo);
        }

        $device_identifier = $data['device_identifier'];


        $sessionData = array('device_identifier' => $device_identifier, 'user_id' => $userId);
        $sess = new SessionModel();
        $sessId = $sess->Create($sessionData);

        $userInfo = $userModel->getInfoById($userId);
        $userInfo['profile'] = $profileModel->getProfile($userId);

        $response = array();
        $response['session_id'] = $sessId;
        $response['user_info'] = $userInfo;
        $response['user_info']['chat_token'] = $this->getRcloudToken($userId,$nickname,AVATAR_DEFAULT);


        $this->send($response);


    }
    /**
     * @api {POST} /v3/User/chattoken 融云消息刷新
     * @apiName user chattoken
     * @apiGroup User
     * @apiDescription 用户资料更新
     * @apiPermission anyone
     * @apiSampleRequest http://www.bibicar.cn:8090
     * @apiVersion 1.0.0
     * @apiParam {string} [device_identifier] 设备唯一标识
     * @apiParam {string} [session_id] session_id
     *
     * @apiParam {json} data object
     * @apiUse DreamParam
     * @apiParamExample {json} 请求样例
     *   POST /v3/User/chattoken
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
    /**
     * @api {POST} /v3/User/search 搜索用户
     * @apiName user search
     * @apiGroup User
     * @apiDescription 搜索用户
     * @apiPermission anyone
     * @apiSampleRequest http://www.bibicar.cn:8090
     * @apiVersion 1.0.0
     * @apiParam {string} [device_identifier] 设备唯一标识
     * @apiParam {string} [session_id] session_id
     * @apiParam {string} [nickname] 昵称
     * @apiParam {string} [page] 页数
     *
     * @apiParam {json} data object
     * @apiUse DreamParam
     * @apiParamExample {json} 请求样例
     *   POST /v3/User/search
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "nickname":"",
     *       "page":"",
     *
     *
     *     }
     *   }
     *
     */
    public function searchAction(){


        $this->required_fields = array_merge($this->required_fields, array('session_id','nickname','page'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $nickname = $data['nickname'];

        $page = $data['page'];

        $userModel = new Model('bibi_user_profile');
        $userModel = new ProfileModel();

        $pageSize = 10;
        $number = ($page) * $pageSize;

        $sql = 'SELECT 
                  t2.user_id,t2.nickname,t2.avatar FROM `bibi_user_profile` AS t2 
                  LEFT JOIN `bibi_user` AS t1 ON t1.user_id = t2.user_id
                  WHERE t2.`nickname` LIKE "%'.$nickname.'%" OR t2.`bibi_no`="'.$nickname.'" LIMIT ' . $number . ' , ' . $pageSize;

        $sqlCnt = 'SELECT 
                  count(*) as total
                   FROM `bibi_user_profile` AS t2 
                  LEFT JOIN `bibi_user` AS t1 ON t1.user_id = t2.user_id
                  WHERE t2.`nickname` LIKE "%'.$nickname.'%" OR t2.`bibi_no`='."'".$nickname."'" ;

        $users = $userModel->query($sql);

        $total = $userModel->query($sqlCnt)[0]["total"];

        $count=count($users);

        $response['has_more'] = (($number+$count) < $total) ? 1 : 2;

        $response['total'] =  $total;


        foreach($users as $key =>$value){

            $friendShipM = new FriendShipModel();

            $friendShipM->currentUser = $value['user_id'];

            $users[$key]['friend_num'] = $friendShipM->friendNumCnt();

            $users[$key]['fans_num']   = $friendShipM->fansNumCnt();

            $friendShip = $friendShipM->getMyFriendShip($userId, $value['user_id']);

            $users[$key]['is_friend'] = isset($friendShip['user_id']) ? 1 : 2;

        }

        $response['list'] = $users;

        $this->send($response);

    }

    /**
     * @api {POST} /v3/User/gethotgirl 用户排行
     * @apiName user gethotgirl
     * @apiGroup User
     * @apiDescription 搜索用户
     * @apiPermission anyone
     * @apiSampleRequest http://www.bibicar.cn:8090
     * @apiVersion 1.0.0
     *
     * @apiParam {string} [device_identifier] 设备唯一标识
     * @apiParam {string} [session_id] session_id
     * @apiParam {string} [page] 页数
     *
     * @apiParam {json} data object
     * @apiUse DreamParam
     * @apiParamExample {json} 请求样例
     *   POST /v3/User/gethotgirl
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "page":"",
     *
     *
     *     }
     *   }
     *
     */

    public function gethotgirlAction(){


        $this->required_fields = array_merge($this->required_fields, array('page'));

        $data = $this->get_request_data();

        if(@$data['session_id']){
            $sess = new SessionModel();
            $userId = $sess->Get($data);
        }
        else{
            $userId = 0;
        }
        $data['page']     = $data['page'] ? ($data['page']+1) : 1;
        $profile =  new \ProfileModel;
        $response  =  $profile->gethotgirl($data['page'],$userId);
        $this->send($response);

    }

    public function changesortAction(){

        $userpro=new UserSortModel();
        $active="like";
        $type_id=151;
        $fromId=544;
        $toId=389;
        $result=$userpro->updateSortByKey($active,$type_id,$fromId,$toId);

    }


    /**
     * @api {POST} /v3/user/checkreport 通讯录邀请好友
     * @apiName 通讯录邀请好友
     * @apiGroup User
     * @apiDescription 通讯录邀请好友
     * @apiPermission anyone
     * @apiSampleRequest http://www.bibicar.cn:8090
     * @apiVersion 1.0.0
     *
     * @apiParam {string} [device_identifier] 设备唯一标识
     * @apiParam {string} [session_id] session_id
     * @apiParam {json}   [mobile_list] 通讯录列表
     *
     * @apiParam {json} data object
     * @apiUse Data
     * @apiParamExample {json} 请求样例
     *   POST /v3/user/checkreport
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "mobile_list":"",
     *     }
     *   }
     *
     */


    public function checkreportAction(){


        $this->required_fields = array_merge($this->required_fields, array('session_id'));
        $data = $this->get_request_data();
        $arrt=json_decode(str_replace('\\', '', $data['mobile_list']));
        $mobile=$this->object_array($arrt);
        $userId = $this->userAuth($data);
        $user = new \UserModel;

        $arr=array();
        // $arr['register']    =array();
        //  $arr['no_register'] =array();
        foreach($mobile as $key => $value){
            // echo $value['phone'];
            @ $attr=$value['phone'];
            $phone=str_replace("+86 ",'',$attr);
            $phone=str_replace("-",'',$phone);

            $info=$user->isregister($phone,$userId);

            @$arr['list'][$key]['phone']=$value['phone'];
            $arr['list'][$key]['name'] =$value['name'];
            if($info){
                $arr['list'][$key]['userinfo']=array();
                $arr['list'][$key]['userinfo']=$info;
                $arr['list'][$key]['userinfo']['avatar']=$arr['list'][$key]['userinfo']['profile']['avatar'];
                $arr['list'][$key]['userinfo']['nickname']=$arr['list'][$key]['userinfo']['profile']['nickname'];

                unset($arr['list'][$key]['userinfo']['profile']);
            }else{
                $arr['list'][$key]['userinfo']= new stdClass();;
            }



        }
        //$arr['register']=array_merge($arr['register']);
        // $arr['no_register']=array_merge($arr['no_register']);


        $response=$arr;

        $this->send($response);


    }
    public function  getmessageAction(){

        $this->required_fields = array_merge($this->required_fields, array('session_id'));
        $data = $this->get_request_data();
        $response['share_title'] = REPORT_MESSAGE_TITLE;
        $response['share_url'] = "http://a.app.qq.com/o/simple.jsp?pkgname=com.wiserz.pbibi";
        //$response['share_url'] = 'http://wx.bibicar.cn/post/index/feed_id/'.$data['feed_id'].'';
        $response['share_txt'] = REPORT_MESSAGE_WEI;
        $response['share_img'] = 'http://img.bibicar.cn/bibilogo.png';
        $response['share_message'] =  REPORT_MESSAGE;

        $this->send($response);
    }

    function object_array($array) {
        if(is_object($array)) {
            $array = (array)$array;
        } if(is_array($array)) {
            foreach($array as $key=>$value) {
                $array[$key] =$this->object_array($value);
            }
        }
        return $array;
    }

    /**
     * @apiDefine Data
     *
     * @apiParam (data) {string} [device_identifier]  设备唯一标示.
     * @apiParam (data) {string} [session_id]     用户session_id.
     *
     *
     */

    /**
     * @api {POST} /v3/user/gettaguserlist 大咖－娱乐－职业用户列表
     * @apiName User  taguser
     * @apiGroup User
     * @apiDescription 大咖－娱乐－职业用户列表
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 1.0.0
     * @apiParam {string} [device_identifier] device_identifier
     * @apiParam {string} [session_id] session_id
     * @apiParam {number} [page] 页码
     * @apiParam {number} [tag] 标签 1: 所有 2大咖 3 职业 4 娱乐 5 顾问
     *
     * @apiParam {json} data object
     * @apiUse DreamParam
     * @apiParamExample {json} 请求样例
     *   POST /v3/user/gettaguserlist
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "post_content":"",
     *       "page":"",
     *       "tag":"",
     *
     *
     *     }
     *   }
     *
     */
    public function gettaguserlistAction(){

        $this->required_fields = array_merge(
            $this->required_fields,
            array('page', 'tag')
        );

        $data = $this->get_request_data();

        if(@$data['session_id']){
            $sess = new SessionModel();
            $userId = $sess->Get($data);
        }
        else{
            $userId = 0;
        }
        // $data['tag']=2;
        // $data['page']=1;
        $Profile=new ProfileModel();
        $tag=$data['tag'];
        $page     = $data['page'] ? ($data['page']+1) : 1;
        $user=$Profile->gettypeofuser($tag,$page,$userId);
        $response['user_list']=$user;
        $this->send($response);
    }

    /**
     * @apiDefine Data
     *
     * @apiParam (data) {string} [device_identifier]  设备唯一标示.
     * @apiParam (data) {string} [session_id]     用户session_id.
     *
     *
     */

    /**
     * @api {POST} /v3/user/getsaleslist 销售顾问
     * @apiName User  salesuser
     * @apiGroup User
     * @apiDescription 销售顾问列表
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 1.0.0
     * @apiParam {string} device_identifier device_identifier
     * @apiParam {string} session_id session_id
     * @apiParam {string}  company_id 当前公司的user_id
     * @apiParam {number} page 页码
     *
     * @apiUse DreamParam
     * @apiParamExample {json} 请求样例
     *   POST /v3/user/getsaleslist
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "company_id":"",
     *       "page":"",
     *
     *
     *     }
     *   }
     *
     */
    public function getsaleslistAction(){

        $this->required_fields = array_merge(
            $this->required_fields,
            array('page', 'company_id')
        );

        $data = $this->get_request_data();

        if(@$data['session_id']){
            $sess = new SessionModel();
            $userId = $sess->Get($data);
        }
        else{
            $userId = 0;
        }
        $Profile=new ProfileModel();
        $page     = $data['page'] ? ($data['page']+1) : 1;
        $user=$Profile->getcompanyuserlist($page,$data['company_id']);
        $response['list']=$user;
        $this->send($response);
    }


    /**
     * @api {POST} /v3/user/bindmobile 绑定手机号
     * @apiName User  bandmobile
     * @apiGroup User
     * @apiDescription  绑定手机号
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 1.0.0
     * @apiParam {string} device_identifier device_identifier
     * @apiParam {string} session_id session_id
     * @apiParam {string}  mobile 手机号码
     * @apiParam {number} code 验证码
     *
     * @apiUse DreamParam
     * @apiParamExample {json} 请求样例
     *   POST /v3/user/bindmobile
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
     * @api {POST} /v3/User/loginbymobile 用户登陆(手机验证码)
     * @apiName user loginbymobile
     * @apiGroup User
     * @apiDescription 用户登录(手机验证码)
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 1.0.0
     * @apiParam {string} [device_identifier] 设备唯一标识
     * @apiParam {string} [mobile] 手机号码
     * @apiParam {string} [code] 验证码
     *
     * @apiParamExample {json} 请求样例
     *   POST /v3/User/loginbymobile
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


}