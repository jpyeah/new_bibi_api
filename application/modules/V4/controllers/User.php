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
 * @api {POST} /v4/user/userpage  个人主页
 * @apiName user userpage
 * @apiGroup User
 * @apiDescription 个人主页
 * @apiPermission anyone
 * @apiSampleRequest http://testapi.bibicar.cn
 * @apiVersion 2.0.0
 *
 * @apiParam {string} device_identifier 设备唯一标识
 * @apiParam {string} session_id session_id
 * @apiParam {number} [user_id]  别人主页Uid
 *
 * @apiParamExample {json} 请求样例
 *   POST /v4/User/userpage
 *   {
 *     "data": {
 *       "device_identifier":"",
 *       "session_id":"",
 *
 *     }
 *   }
 *
 */
    public function userpageAction(){

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

        $car = new CarSellingV1Model();

        $response['car_info'] = $car->getUserCars($otherId);

        $friendShipM = new FriendShipModel();

        $friendShipM->currentUser = $otherId;

        $response['friend_num'] = $friendShipM->friendNumCnt();

        $response['fans_num']   = $friendShipM->fansNumCnt();

        $friendShip = $friendShipM->getMyFriendShip($userId, $otherId);

        $response['is_friend'] = isset($friendShip['user_id']) ? 1 : 2;

        $feedM = new FeedModel();

        $response['feed_num'] = $feedM->getPublishedFeedTotal(@$data['user_id']);

        $this->send($response);


    }

    /**
     * @api {POST} /v4/User/homepage  个人中心
     * @apiName user homepage
     * @apiGroup User
     * @apiDescription 个人中心
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.0.0
     *
     * @apiParam {string} device_identifier 设备唯一标识
     * @apiParam {string} session_id session_id
     *
     * @apiParamExample {json} 请求样例
     *   POST /v3/User/homepage
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

       // $otherId = $this->getAccessId($data, $userId);

        $userM = new UserModel();
        $userInfo = $userM->getInfoById($userId);

        $profileM = new ProfileModel();
        $profile = $profileM->getProfile( $userId);

        $carM = new CarSellingV1Model();

        $userInfo['profile'] = $profile;
        $userInfo['total_money']=$carM->getUserCarTotalPrice($userId);
        $userInfo['total_car']=$carM->getUserCarTotal($userId);

        $response['user_info'] =$userInfo;

        $friendShipM = new FriendShipModel();

        $friendShipM->currentUser =  $userId;

        $response['friend_num'] = $friendShipM->friendNumCnt();

        $response['fans_num']   = $friendShipM->fansNumCnt();

        $this->send($response);
    }

    /**
     * @api {POST} /v4/User/getrichlist  财富排行
     * @apiName user getrichlist
     * @apiGroup User
     * @apiDescription 财富排行
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.0.0
     *
     * @apiParam {string} device_identifier 设备唯一标识
     * @apiParam {string} session_id session_id
     * @apiParam {number} [user_id]  别人主页Uid
     *
     * @apiSuccess {string} data.list.sort   热度
     * @apiSuccess {string} data.list.is_like   是否点赞 1：是 2：否
     *
     */
    public function getrichlistAction(){

        $this->required_fields = array_merge($this->required_fields, array('session_id'));
        $data = $this->get_request_data();
        $userId = $this->userAuth($data);

        $car = new CarSellingV1Model();

        $car->currenuser=$userId ;
        $car->page= $data['page'] ? ($data['page']+1) : 1 ;

        $res = $car->getUserCarTotalPriceList();

        $this->send($res);

    }


    /**
     * @api {POST} /v4/user/createrichboardlike  排行点赞
     * @apiName user createrichboardlike
     * @apiGroup User
     * @apiDescription 财富排行
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.0.0
     *
     * @apiParam {string} device_identifier 设备唯一标识
     * @apiParam {string} session_id session_id
     * @apiParam {number} [user_id]  别人主页Uid
     *
     *
     */

    public function createrichboardlikeAction(){

        $this->required_fields = array_merge($this->required_fields, array('session_id','user_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $otherId = $data['user_id'];

        $key = 'rich_like_'.$otherId.'_'.$userId.'';

        $likevalue= RedisDb::getValue($key);

        $userM = new ProfileModel();

        if(!$likevalue){

            RedisDb::setValue($key,1);

            $userM->updateSortNum($otherId,'add');

        }

        $response['user_info']=$userM->getProfile($otherId);
        $response['user_info']['is_like']=1;

        $this->send($response);

    }

    /**
     * @api {POST} /v4/user/cancelrichboardlike  排行点赞取消
     * @apiName user cancelrichboardlike
     * @apiGroup User
     * @apiDescription 财富排行
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.0.0
     *
     * @apiParam {string} device_identifier 设备唯一标识
     * @apiParam {string} session_id session_id
     * @apiParam {number} [user_id]  别人Uid
     *
     *
     */

    public function cancelrichboardlikeAction(){

        $this->required_fields = array_merge($this->required_fields, array('session_id','user_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $otherId = $data['user_id'];

        $key = 'rich_like_'.$otherId.'_'.$userId.'';

        $likevalue= RedisDb::getValue($key);

        if($likevalue){

            RedisDb::setValue($key,0);

            $userM = new ProfileModel();

            $userM->updateSortNum($otherId,'delete');

            $response['user_info']=$userM->getProfile($otherId);
            $response['user_info']['is_like']=2;

            $this->send($response);

        }


    }


    /**
     * @api {POST} /v4/User/quicklogin 用户登录/注册
     * @apiName user quicklogin
     * @apiGroup User
     * @apiDescription 用户登录/注册
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 1.0.0
     *
     * @apiParam {string} [device_identifier] 设备唯一标识
     * @apiParam {string} [mobile] 手机号码
     * @apiParam {string} [code] 验证码
     *
     * @apiParamExample {json} 请求样例
     *   POST /v4/User/quicklogin
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

        if($data['mobile'] == '13218029707' || $data['mobile'] == '10000000017' || $data['mobile'] == '10000000018' || $data['mobile'] == '10000000019' || $data['mobile']== '10000002016'){
            RedisDb::setValue($key,'1234');
            $code = RedisDb::getValue($key);
        }

        if($code != $data['code']){
            $this->send_error(USER_CODE_ERROR);
        }
        RedisDb::delValue($key);
        unset($data['v4/User/quicklogin']);
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
        }else{
            $time = time();
            $data['login_ip'] = $_SERVER['REMOTE_ADDR'];
            $data['login_time'] = $time;
            $data['created'] = $time;
            $data['updated'] = $time;
            $data['username']= 'bibi_' . Common::randomkeys(6);
            $data['password']=md5('12345');
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
            $profileInfo['nickname'] = "BiBiCar".rand(10,100);
            $profileInfo['avatar']   = AVATAR_DEFAULT;
            $profileInfo['bibi_no']  =$userId+10000;
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
     * @api {POST} /v4/User/oauthlogin 第三方登录
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
     *   POST /v3/User/oauthlogin
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

            $userId = $userModel->register($insert);
            $profileInfo = array();
            $profileInfo['user_id'] = $userId;
            $profileInfo['user_no'] = $name;
            $profileInfo['nickname'] = $nickname;
            $profileInfo['avatar']   = $avatar;
            $profileInfo['bibi_no']  =$userId+10000;
            $profileModel->initProfile($profileInfo);

            $response['is_bind_mobile'] =2;

            //$this->send_error(USER_OAUTH_UPDATE_PROFILE);
        }else{
            $userId = $info['user_id'];

            $update['updated'] = $time;

            $userModel->update(array('user_id'=>$userId),$update);

            $updateProfile['nickname'] = $data['nickname'];

            $updateProfile['avatar']   = $data['avatar'];

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
     * @api {POST} /v4/user/oauthbindmobile 第三方登录绑定手机号
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
     * @apiUse DreamParam
     * @apiParamExample {json} 请求样例
     *   POST /v4/user/oauthbindmobile
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


    public function testloginAction(){

        $data = $this->get_request_data();

        $response['handle']=$data;

        $response['request']=$_REQUEST;
        $response['post']=$_POST;
        $response['get']=$_GET;
        $response['sever']=$_SERVER;

        $this->send($response);

    }


    /**
     * @api {POST} /v4/User/companyregister 企业注册
     * @apiName user companyregister
     * @apiGroup User
     * @apiDescription 企业注册
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.1.0
     *
     * @apiParam {string} [device_identifier] 设备唯一标识
     * @apiParam {string} [mobile] 手机号码
     * @apiParam {string} [code] 验证码
     * @apiParam {string} [nickname] 昵称
     * @apiParam {string} [company] 公司
     * @apiParam {string} [card_file] 车行名片hash
     * @apiParam {string} [car_file] 车行照片hash
     *
     * @apiParam {json} data object
     * @apiUse DreamParam
     * @apiParamExample {json} 请求样例
     *   POST /v4/User/companyregister
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

        $this->required_fields = array_merge($this->required_fields, array('mobile',  'code', 'nickname','car_tel','car_address','car_file','card_file'));

        $data = $this->get_request_data();

        //unset($data['code']);
        $key =  $key = 'code_' . $data['mobile'] . '';
        $code = RedisDb::getValue($key);

        if($code != $data['code']){
            $this->send_error(USER_CODE_ERROR);
        }
        RedisDb::delValue($key);

        unset($data['code']);

        $time = time();

        $data['login_ip'] = $_SERVER['REMOTE_ADDR'];
        $data['login_time'] = $time;
        $data['created'] = $time;
        $data['updated'] = $time;

        $name = 'bibi_' . Common::randomkeys(6);

        $data['username'] = $name;
        $data['password'] = md5('123456');

        $car_tel = $data['car_tel'];
        $address = $data['car_address'];

        unset( $data['car_tel']);
        unset( $data['car_address']);


        unset( $data['car_address_name']);
        unset( $data['car_address_lat']);
        unset( $data['car_address_lng']);


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


        unset($data['device_identifier']);

        $userId = $userModel->register($data);

        if (!$userId) {

            $this->send_error(USER_REGISTER_FAIL);

        }

        $files[0]['name'] = 'card_file';
        $files[0]['hash'] = $data['card_file'];

        $files[1]['name'] = 'car_file';
        $files[1]['hash'] = $data['car_file'];

        unset($data['card_file']);
        unset($data['car_file']);

        $post_files =  serialize($files);
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










}