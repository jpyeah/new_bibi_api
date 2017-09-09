<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/12/6
 * Time: 上午12:01
 */

class FavoritecarController extends ApiYafControllerAbstract{
/**
 * @apiDefine Data
 *
 * @apiParam (data) {string} [device_identifier]  设备唯一标示.
 * @apiParam (data) {string} [session_id]     用户session_id.
 * 
 * 
 */

/**
 * @api {POST} /v3/Favoritecar/create 收藏车辆
 * @apiName favoritecar create
 * @apiGroup Collect
 * @apiDescription 收藏车辆
 * @apiPermission anyone
 * @apiSampleRequest http://testapi.bibicar.cn
 *
 * @apiParam {string} [device_identifier] 设备唯一标识
 * @apiParam {string} [session_id] session_id
 * @apiParam {string} [car_id] 车辆id
 * 
 * @apiParamExample {json} 请求样例
 *   POST /v3/Favoritecar/create
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

        $this->required_fields = array_merge($this->required_fields,array('session_id','car_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $favCarM = new FavoriteCarModel();
        $favCarM->user_id = $userId;
        $favCarM->car_id  = $data['car_id'];
        $favCar = $favCarM->get();

        if($favCar){
            $this->send_error(FAVORITE_CAR_ALREADY);
        }

        $carM = new CarSellingModel();
        $carMTable = $carM::$table;
        $carM->currentUser = $userId;
        $car = $carM->GetCarInfoById($data['car_id']);
        $favNum = $car['fav_num'] + 1;


        if(!$car){

            $this->send_error(CAR_NOT_EXIST);
        }
        $favCarM = new FavoriteCarModel();
        $properties = array();
        $properties['user_id'] = $userId;
        $properties['car_id']  = $data['car_id'];
        $created = time();
        $properties['created'] = $created;

        $favCarM->properties = $properties;
        $id = $favCarM->CreateM();

        if(!$id){
            $this->send_error(FAVORITE_FAIL);
        }
        else{

            $carM->updateByPrimaryKey($carMTable, array('hash'=>$car['car_id']),array('fav_num'=>$favNum));

            $response = array();
            $response['favorite_id'] = $id;
            $response['car_info'] = $car;

            $key = 'favorite_'.$userId.'_'.$data['car_id'].'';

            RedisDb::setValue($key,1);

            $this->send($response);
        }


    }

/**
 * @api {POST} /v3/Favoritecar/delete 删除收藏车辆
 * @apiName favoritecar delete
 * @apiGroup Collect
 * @apiDescription 删除收藏车辆
 * @apiPermission anyone
 * @apiSampleRequest http://testapi.bibicar.cn
 *
 * @apiParam {string} [device_identifier] 设备唯一标识
 * @apiParam {string} [session_id] session_id
 * @apiParam {string} [car_id] 车辆id
 * 
 * @apiUse Data
 * @apiParamExample {json} 请求样例
 *   POST /v3/Favoritecar/delete
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


        $this->required_fields = array_merge($this->required_fields,array('session_id', 'car_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $favCarM = new FavoriteCarModel();

        $favCarM->car_id      = $data['car_id'];
        $favCarM->user_id     = $userId;

        $key = 'favorite_'.$favCarM->user_id.'_'.$favCarM->car_id.'';


        $favId = RedisDb::getValue($key);
        $favCarM->favorite_id = $favId;
        $favCarM->delete();

        RedisDb::delValue($key);

        $response = array();

        $this->send($response);

    }

/**
 * @api {POST} /v3/Favoritecar/list 收藏车辆列表
 * @apiName favoritecar list
 * @apiGroup Collect
 * @apiDescription 收藏车辆列表
 * @apiPermission anyone
 * @apiSampleRequest http://testapi.bibicar.cn
 *
 * @apiParam {string} [device_identifier] 设备唯一标识
 * @apiParam {string} [session_id] session_id
 * @apiParam {number} [page] 车辆id
 * 
 * @apiUse Data
 * @apiParamExample {json} 请求样例
 *   POST /v3/Favoritecar/list
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
    public function listAction(){


        $this->required_fields = array_merge($this->required_fields,array('session_id','page'));

        $carM = new CarSellingModel();

        $data = $this->get_request_data();

        $data['page']     = $data['page'] ? ($data['page']+1) : 1;
        $carM->page = $data['page'];
        $userId = $this->userAuth($data);

        $carM->currentUser = $userId;

        $list = $carM->getUserFavoriteCar($userId);

        $response = $list;

        $this->send($response);

    }



}