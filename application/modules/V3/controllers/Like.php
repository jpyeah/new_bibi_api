<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/11
 * Time: 下午12:21
 */

class LikeController extends ApiYafControllerAbstract {
/**
 * @apiDefine Data
 *
 * @apiParam (data) {string} [device_identifier]  设备唯一标示.
 * @apiParam (data) {string} [session_id]     用户session_id.
 * 
 * 
 */

/**
 * @api {POST} /v3/Like/create 朋友圈点赞
 * @apiName feed like create
 * @apiGroup Feed
 * @apiDescription 添加好友
 * @apiPermission anyone
 * @apiSampleRequest http://www.bibicar.cn:8090
 *
 * @apiParam {string} [device_identifier] 设备唯一标识
 * @apiParam {string} [session_id] session_id
 * @apiParam {number} [feed_id] 朋友圈id
 * 
 * @apiParam {json} data object
 * @apiUse DreamParam
 * @apiParamExample {json} 请求样例
 *   POST /v3/Like/create
 *   {
 *     "data": {
 *       "device_identifier":"",
 *       "session_id":"",
 *       "feed_id":"",
 *       
 *       
 *     }
 *   }
 *
 */
    public function createAction(){

        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id', 'feed_id')
        );

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $time = time();

        $feedM = new FeedModel();
        $feed = $feedM->getFeeds($data['feed_id']);

        //$feed['post_user_info']['profile']['nickname']
        $toId = $feed['post_user_info']['user_id'];

        $likeM = new LikeModel();

        $like = $likeM->getLike($userId, $data['feed_id']);
        
        //相关的人
        $feedrelatedM = new FeedrelatedModel();
        $data['feed_id']=$data['feed_id'];
        $data['user_id']=$userId;
        $data['likes'] ='1';
        $data['create_time']=time();
        $feedrelatedM->savefeed($data);

        if(!$like){

            $likeM = new LikeModel();
            $likeM->user_id = $userId;
            $likeM->feed_id = $data['feed_id'];
            $likeM->created = $time;

            $likeM->saveProperties();
            $id = $likeM->CreateM();

            if($id){

                $feedM->updateLikeNum($data['feed_id']);

                $key = 'like_'.$data['feed_id'].'_'.$userId.'';

                RedisDb::setValue($key,1);

                $like = $likeM->getLike($userId, $data['feed_id']);

                if($userId != $toId){

                    $mh = new MessageHelper;
                    $userM = new ProfileModel();
                    $profile = $userM->getProfile($userId);
                    $content = ''.$profile["nickname"].'赞了你';
                    $mh->likeNotify($toId, $content);
                }

                $this->send($like['user_info']);
            }


        }
        else{

            $this->send_error(FEED_HAS_LIKED);
        }




    }
/**
 * @api {POST} /v3/Like/list 点赞列表
 * @apiName feed like list
 * @apiGroup Feed
 * @apiDescription 点赞列表
 * @apiPermission anyone
 * @apiSampleRequest http://www.bibicar.cn:8090
 *
 * @apiParam {string} [device_identifier] 设备唯一标识
 * @apiParam {string} [session_id] session_id
 * @apiParam {number} [feed_id] 朋友圈id
 * @apiParam {number} [page] 页数
 * 
 * @apiParam {json} data object
 * @apiUse DreamParam
 * @apiParamExample {json} 请求样例
 *   POST /v3/Like/list
 *   {
 *     "data": {
 *       "device_identifier":"",
 *       "session_id":"",
 *       "feed_id":"",
 *       "page":"",
 *       
 *       
 *     }
 *   }
 *
 */
    public function listAction(){


        $this->required_fields = array_merge($this->required_fields,array('session_id','feed_id','page'));

        $data = $this->get_request_data();
        $data['page']     = $data['page'] ? ($data['page']+1) : 1;


        $sess = new SessionModel();
        $userId = $sess->Get($data);

        $likeM = new LikeModel();
        $likeM->currentUser = $userId;
        $likes = $likeM->getLike(0,$data['feed_id'],$data['page']);


        $this->send($likes);

    }
/**
 * @api {POST} /v3/Like/tome 赞我到人
 * @apiName feed like tome list
 * @apiGroup Feed
 * @apiDescription 赞我的人
 * @apiPermission anyone
 * @apiSampleRequest http://www.bibicar.cn:8090
 *
 * @apiParam {string} [device_identifier] 设备唯一标识
 * @apiParam {string} [session_id] session_id
 * @apiParam {number} [page] 页数
 * 
 * @apiParam {json} data object
 * @apiUse DreamParam
 * @apiParamExample {json} 请求样例
 *   POST /v3/Like/tome
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

    public function tomeAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id', 'page'));

        $data = $this->get_request_data();
        $data['page']     = $data['page'] ? ($data['page']+1) : 1;

        $userId = $this->userAuth($data);

        $likeM = new LikeModel();
        $likeM->currentUser = $userId;

        $likes = $likeM->getLikeToMe($data['page']);

        $this->send($likes);
    }
/**
 * @api {POST} /v3/Like/delete 点赞取消
 * @apiName feed like delete
 * @apiGroup Feed
 * @apiDescription 点赞取消
 * @apiPermission anyone
 * @apiSampleRequest http://www.bibicar.cn:8090
 *
 * @apiParam {string} [device_identifier] 设备唯一标识
 * @apiParam {string} [session_id] session_id
 * @apiParam {number} [feed_id] 页数
 * 
 * @apiParam {json} data object
 * @apiUse DreamParam
 * @apiParamExample {json} 请求样例
 *   POST /v3/Like/delete
 *   {
 *     "data": {
 *       "device_identifier":"",
 *       "session_id":"",
 *       "feed_id":"",
 *       
 *       
 *     }
 *   }
 *
 */

    public function deleteAction(){

        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id', 'feed_id')
        );

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $likeM = new LikeModel();

        $like = $likeM->getLike($userId, $data['feed_id']);


        $rs = $likeM->deleteLike($data['feed_id'] , $userId);

        if($rs){

            $feedM = new FeedModel();

            $feedM->updateLikeNum($data['feed_id'], 'minus');

            $this->send($like['user_info']);
        }
        else{

            $this->send_error(FEED_LIKE_HAS_CANCLED);
        }


    }


}