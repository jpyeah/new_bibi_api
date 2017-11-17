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
            $profileInfo['nickname'] = $data['mobile'];
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


    public function testloginAction(){

        $data = $this->get_request_data();

        $response['handle']=$data;

        $response['request']=$_REQUEST;
        $response['post']=$_POST;
        $response['get']=$_GET;
        $response['sever']=$_SERVER;

        $this->send($response);

    }










}