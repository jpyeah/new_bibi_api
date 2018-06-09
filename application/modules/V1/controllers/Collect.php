<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/12/6
 * Time: 上午12:01
 */

class CollectController extends ApiYafControllerAbstract{

    /**
     * @api {POST} /v1/collect/create 添加收藏
     * @apiName collect create
     * @apiGroup Collect
     * @apiDescription 添加收藏
     * @apiPermission anyone
     * @apiSampleRequest http://new.bibicar.cn
     *
     * @apiParam {string} device_identifier 设备唯一标识
     * @apiParam {string} session_id session_id
     * @apiParam {string} car_id 车辆Id
     *
     * @apiParamExample {json} 请求样例
     *   POST /v1/collect/create
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "car_id":"",
     *
     *     }
     *   }
     *
     */
    public function createAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id','car_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $favCarM = new CollectModel();
        $favCarM->user_id = $userId;
        $favCarM->car_id  = $data['car_id'];
        $favCar = $favCarM->get();

        if($favCar){

            $this->send_error(FAVORITE_CAR_ALREADY);
        }

        $carM = new CarSellingModel();
        $carMTable = $carM::$table;
        $carM->currentUser = $userId;
        $car = $carM->GetCarInfoByHash($data['car_id']);
        $favNum = $car['fav_num'] + 1;

        if(!$car){
            $this->send_error(CAR_NOT_EXIST);
        }
        $properties = array();
        $properties['user_id'] = $userId;
        $properties['car_id']  = $data['car_id'];
        $created = time();
        $properties['created_at'] = $created;

        $CollectM = new CollectModel();
        $CollectM->properties = $properties;
        $id = $CollectM->CreateM();

        if(!$id){
            $this->send_error(FAVORITE_FAIL);
        }
        else{

            $carM->updateByPrimaryKey($carMTable, array('hash'=>$data['car_id']),array('fav_num'=>$favNum));

            $response = array();

            $response['id'] = $id;

            $response['car_info'] = $car;

            $key = 'favorite_'.$userId.'_'.$data['car_id'].'';

            RedisDb::setValue($key, $id);

            $this->send($response);
        }

    }


    /**
     * @api {POST} /v1/collect/delete 删除收藏
     * @apiName collect delete
     * @apiGroup Collect
     * @apiDescription 删除收藏
     * @apiPermission anyone
     * @apiSampleRequest http://new.bibicar.cn
     *
     * @apiParam {string} device_identifier 设备唯一标识
     * @apiParam {string} session_id session_id
     * @apiParam {string} car_id 车辆Id
     *
     * @apiParamExample {json} 请求样例
     *   POST /v1/collect/delete
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "car_id":"",
     *
     *     }
     *   }
     *
     */
    public function deleteAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id', 'car_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $favCarM = new CollectModel();

        $favCarM->car_id      = $data['car_id'];
        $favCarM->user_id     = $userId;

        $key = 'favorite_'.$favCarM->user_id.'_'.$favCarM->car_id.'';

        $favId = RedisDb::getValue($key);
        $favCarM->id = $favId;
        $favCarM->delete();

        RedisDb::delValue($key);

        $response = array();

        $this->send($response);

    }

    /**
     * @api {POST} /v1/collect/list 收藏列表
     * @apiName collect list
     * @apiGroup Collect
     * @apiDescription 收藏列表
     * @apiPermission anyone
     * @apiSampleRequest http://new.bibicar.cn
     *
     * @apiParam {string} device_identifier 设备唯一标识
     * @apiParam {string} session_id session_id
     * @apiParam {string} page 页码
     *
     * @apiParamExample {json} 请求样例
     *   POST /v1/collect/list
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "page":"",
     *
     *     }
     *   }
     *
     */
    public function listAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id'));

        $CollectM = new CollectModel();

        $data = $this->get_request_data();

        //$data['page']     = $data['page'] ? ($data['page']+1) : 1;

        $page = $data['page'];

        $userId = $this->userAuth($data);

        $list   =$CollectM->getCollect($userId);

        $response = $list;

        $this->send($response);

    }



}