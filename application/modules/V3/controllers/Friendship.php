<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/12
 * Time: 下午8:48
 */


class FriendshipController extends ApiYafControllerAbstract {

/**
 * @apiDefine Data
 *
 * @apiParam (data) {string} [device_identifier]  设备唯一标示.
 * @apiParam (data) {string} [session_id]     用户session_id.
 * 
 * 
 */

/**
 * @api {POST} /v3/Friendship/create 添加好友
 * @apiName friendship create
 * @apiGroup Friend
 * @apiDescription 添加好友
 * @apiPermission anyone
 * @apiSampleRequest http://www.bibicar.cn:8090
 *
 * @apiParam {string} [device_identifier] 设备唯一标识
 * @apiParam {string} [session_id] session_id
 * @apiParam {number} [user_id] 用户id
 * 
 * @apiParam {json} data object
 * @apiUse DreamParam
 * @apiParamExample {json} 请求样例
 *   POST /v3/Friendship/create
 *   {
 *     "data": {
 *       "device_identifier":"",
 *       "session_id":"",
 *       "user_id":"",
 *       
 *       
 *     }
 *   }
 *
 */
    public function createAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id','user_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $friendShipM = new FriendShipModel();

        $time = time();

        $friendShip = $friendShipM->getMyFriendShip($userId, $data['user_id']);

        if(!$friendShip){

            $friendShipM->friend_id = $data['user_id'];
            $friendShipM->user_id   = $userId;
            $friendShipM->created   = $time;

            $friendShipM->saveProperties();
            $friendShipM->CreateM();

            /*
            $mh = new MessageHelper;
            $userM = new ProfileModel();
            $profile = $userM->getProfile($userId);
            $content = ''.$profile["nickname"].'关注了你';
            $mh->systemNotify($data['user_id'], $content);
           */
        }

        $friendShip = $friendShipM->getMyFriendShip($userId, $data['user_id']);

        $this->send($friendShip);

    }
/**
 * @api {POST} /v3/Friendship/delete 删除好友
 * @apiName friendship delete
 * @apiGroup Friend
 * @apiDescription 添加好友
 * @apiPermission anyone
 * @apiSampleRequest http://www.bibicar.cn:8090
 *
 * @apiParam {string} [device_identifier] 设备唯一标识
 * @apiParam {string} [session_id] session_id
 * @apiParam {number} [user_id] 用户id
 * 
 * @apiParam {json} data object
 * @apiUse DreamParam
 * @apiParamExample {json} 请求样例
 *   POST /v3/Friendship/delete
 *   {
 *     "data": {
 *       "device_identifier":"",
 *       "session_id":"",
 *       "user_id":"",
 *       
 *       
 *     }
 *   }
 *
 */
    public function deleteAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id','user_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);


        $friendShipM = new FriendShipModel();

       // $friendShip = $friendShipM->getMyFriendShip($userId, $data['user_id']);


        $friendShipM->deleteFriendShip($data['user_id'] , $userId);

        $this->send($friendShipM);


    }
/**
 * @api {POST} /v3/Friendship/list 好友列表
 * @apiName friendship list
 * @apiGroup Friend
 * @apiDescription 好友列表
 * @apiPermission anyone
 * @apiSampleRequest http://www.bibicar.cn:8090
 *
 * @apiParam {string} [device_identifier] 设备唯一标识
 * @apiParam {string} [session_id] session_id
 * @apiParam {number} [user_id] 用户id
 * @apiParam {number} [page] 页数
 * @apiParam {number} [action] 1 : 为关注列表 2: 粉丝列表
 * 
 * @apiParam {json} data object
 * @apiUse DreamParam
 * @apiParamExample {json} 请求样例
 *   POST /v3/Friendship/list
 *   {
 *     "data": {
 *       "device_identifier":"",
 *       "session_id":"",
 *       "user_id":"",
 *       "action":"",
 *       "page":"",
 *       
 *       
 *     }
 *   }
 *
 */
    public function listAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id','page','action','user_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $objectId = $data['user_id'];

        $friendShipM = new FriendShipModel();

        //$time = time();

        $data['page'] = $data['page'] ? ($data['page']+1) : 1;

        $data['action'] = isset($data['action']) ? $data['action'] : 1;

        //action 1 : 为关注列表 2: 粉丝列表
        if($data['action'] == 1){

            $friendShips = $friendShipM->getMyFriendShip($objectId, 0, $data['page']);

        }
        else{

            $friendShips = $friendShipM->getFriendShipToMe($objectId, 0, $data['page']);

        }

        $response = $friendShips;

        $this->send($response);


    }




}