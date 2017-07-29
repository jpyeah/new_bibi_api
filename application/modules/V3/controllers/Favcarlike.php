<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/11
 * Time: 下午12:21
 */

class FavcarlikeController extends ApiYafControllerAbstract {
/**
 * @apiDefine Data
 *
 * @apiParam (data) {string} [device_identifier]  设备唯一标示.
 * @apiParam (data) {string} [session_id]     用户session_id.
 * 
 * 
 */

/**
 * @api {POST} /v3/Favcarlike/create 爱车点赞
 * @apiName favcarlike create
 * @apiGroup Car
 * @apiDescription 爱车点赞
 * @apiPermission anyone
 * @apiSampleRequest http://www.bibicar.cn:8090
 *
 * @apiParam {string} [device_identifier] 设备唯一标识
 * @apiParam {string} [session_id] session_id
 * @apiParam {string} [car_id] 车辆id
 * 
 * @apiParam {json} data object
 * @apiUse DreamParam
 * @apiParamExample {json} 请求样例
 *   POST /v3/Favcarlike/create
 *   {
 *     "data": {
 *       "device_identifier":"",
 *       "session_id":"",
 *       "car_id":"",
 *       
 *       
 *     }
 *   }
 *
 */
    public function createAction(){
        
       $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id', 'car_id')
        );

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);
    
        $time = time();
       
        $FavcarlikeM = new FavcarlikeModel();
      
        $like = $FavcarlikeM->getLike($userId,$data['car_id']);

        if(!$like){
           $FavcarlikeM = new FavcarlikeModel();
           $FavcarlikeM->user_id = $userId;
           $FavcarlikeM->car_id = $data['car_id'];
           $FavcarlikeM->created = $time;
           $FavcarlikeM->saveProperties();
           $id = $FavcarlikeM->CreateM();

            if($id){

                $key = 'favoritecarlike_'.$data['car_id'].'_'.$userId.'';

                RedisDb::setValue($key,1);

                $like =  $FavcarlikeM->getLike($userId, $data['car_id']);

                $this->send($like[0]);
            }


        }
        else{

            $this->send_error(FEED_HAS_LIKED);
        }

    }

    public function listAction(){
        
        $this->required_fields = array_merge($this->required_fields,array('session_id','car_id','page'));

        $data = $this->get_request_data();
        $data['page']     = $data['page'] ? ($data['page']+1) : 1;

        $sess = new SessionModel();
        $userId = $sess->Get($data);

        $FavcarlikeM = new FavcarlikeModel();
        $FavcarlikeM->currentUser = $userId;
        $likes = $FavcarlikeM->getLike(0,$data['car_id'],$data['page']);
        print_r($likes);exit;
        $this->send($likes);

    }
/**
 * @api {POST} /v3/Favcarlike/delete 爱车点赞取消
 * @apiName favcarlike delete
 * @apiGroup Car
 * @apiDescription 爱车点赞取消
 * @apiPermission anyone
 * @apiSampleRequest http://www.bibicar.cn:8090
 *
 * @apiParam {string} [device_identifier] 设备唯一标识
 * @apiParam {string} [session_id] session_id
 * @apiParam {string} [car_id] 车辆id
 * 
 * @apiParam {json} data object
 * @apiUse Data
 * @apiParamExample {json} 请求样例
 *   POST /v3/Favcarlike/delete
 *   {
 *     "data": {
 *       "device_identifier":"",
 *       "session_id":"",
 *       "car_id":"",
 *       
 *       
 *     }
 *   }
 *
 */

    public function deleteAction(){
        
        $this->required_fields = array_merge($this->required_fields,array('session_id','car_id'));
        $data = $this->get_request_data();
        
        
     
        $sess = new SessionModel();
        $userId = $sess->Get($data);



        $FavcarlikeM = new FavcarlikeModel();
        $FavcarlikeM->currentUser = $userId;

        $likes = $FavcarlikeM->deleteLike($data['car_id'],$userId);
        $this->send($likes);
        

    }






    


}