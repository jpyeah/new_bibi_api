<?php


class DreamCarController extends ApiYafControllerAbstract
{
/**
 * @apiDefine DreamParam
 *
 * @apiParam (data) {string} [device_identifier]  设备唯一标示.
 * @apiParam (data) {string} [session_id]     用户session_id.
 * 
 * 
 */


/**
 * @api {POST} /v3/DreamCar/create 添加梦想车
 * @apiName create dreamcar
 * @apiGroup DreamCar
 * @apiDescription 添加梦想车
 * @apiPermission anyone
 * @apiSampleRequest http://www.bibicar.cn:8090
 *
 * @apiParam {string} [device_identifier] 设备唯一标识
 * @apiParam {string} [session_id] session_id
 * @apiParam {string} [series_id] 系列id
 * @apiParam {string} [brand_id] 品牌id
 * 
 * @apiParam {json} data object
 * @apiUse DreamParam
 * @apiParamExample {json} 请求样例
 *   POST /v3/DreamCar/create
 *   {
 *     "data": {
 *       "device_identifier":"",
 *       "session_id":"",
 *       "series_id":"",
 *       "brand_id":"",
 *       
 *     }
 *   }
 *
 */

    public function createAction(){

        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id','series_id', 'brand_id')
        );

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $dreamCarModel = new DreamCarModel();

        $insert = array();
        $insert['user_id'] = $userId;
        $insert['brand_id'] = $data['brand_id'];
        $insert['series_id'] = $data['series_id'];

        $dc_id = $dreamCarModel->add($insert);
        
        //推荐车辆给用户
        if($dc_id){
            $CarSellingM=new CarSellingModel;
            $car=$CarSellingM->pushSametoCarUser($data);
            if($car){
                $toId=$userId ; 
                $carId=$car['hash'];
                $mh = new MessageHelper;
                $mh->recommendNotify($toId,$carId);
            }

        }

        $brandModel = new BrandModel();

        $response = array();

        $response['dc_id'] = $dc_id;
        $response['brand_info']  = $brandModel->getBrandModel($insert['brand_id']);
        $response['series_info'] = $brandModel->getSeriesModel($insert['brand_id'],$insert['series_id']);

        $this->send($response);

    }

/**
 * @api {POST} /v3/DreamCar/update 修改梦想车
 * @apiName update dreamcar
 * @apiGroup DreamCar
 * @apiDescription 修改梦想车
 * @apiPermission anyone
 * @apiSampleRequest http://www.bibicar.cn:8090
 *
 * @apiParam {string} [device_identifier] 设备唯一标识
 * @apiParam {string} [session_id] session_id
 * @apiParam {number} [series_id] 系列id any id
 * @apiParam {number} [brand_id] 品牌id any id
 * @apiParam {number} [dc_id] 梦想id any id
 * 
 * @apiParam {json} data object
 * @apiUse DreamParam
 * @apiParamExample {json} 请求样例
 *   POST /v3/DreamCar/update
 *   {
 *     "data": {
 *       "device_identifier":"",
 *       "session_id":"",
 *       "series_id":"",
 *       "brand_id":"",
 *       "dc_id":"",
 *       
 *       
 *     }
 *   }
 *
 */

    public function updateAction(){

        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id','series_id', 'brand_id','dc_id')
        );

        $data = $this->get_request_data();

        $userId=$this->userAuth($data);

        $dreamCarModel = new DreamCarModel();
        $dreamCarModel->where(array('dc_id'=>$data['dc_id']))->save(array(
            'brand_id'=>$data['brand_id'],
            'series_id'=>$data['series_id'],
        ));
        
        //推荐车辆给用户
        $CarSellingM=new CarSellingModel;
        $car=$CarSellingM->pushSametoCarUser($data);
        if($car){
            $toId=$userId ; 
            $carId=$car['hash'];
            $mh = new MessageHelper;
            $mh->recommendNotify($toId,$carId);
        }

        $brandModel = new BrandModel();

        $response = array();

        $response['dc_id'] = $data['dc_id'];
        $response['brand_info']  = $brandModel->getBrandModel($data['brand_id']);
        //print
        $response['series_info'] = new stdClass();

        $this->send($response);

    }

/**
 * @api {POST} /v3/DreamCar/list 梦想车列表
 * @apiName dreamcar list
 * @apiGroup DreamCar
 * @apiDescription 梦想车列表
 * @apiPermission anyone
 * @apiSampleRequest http://www.bibicar.cn:8090
 *
 * @apiParam {string} [device_identifier] 设备唯一标识
 * @apiParam {string} [session_id] session_id
 * @apiParam {number} [user_id] 用户id any id
 * 
 * @apiParam {json} data object
 * @apiUse DreamParam
 * @apiParamExample {json} 请求样例
 *   POST /v3/DreamCar/list
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
    public function listAction(){

        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id','user_id')
        );

        $data = $this->get_request_data();

        //$userId = $this->userAuth($data);

        $userId = $data['user_id'];

        $dreamCarModel = new DreamCarModel();
        $dreamCars = $dreamCarModel->where(array('user_id'=>$userId))->select();

        $brandModel = new BrandModel();

        $items = array();

        foreach ($dreamCars as $k => $dreamCar){

            $item = array();
            $item['dc_id'] = $dreamCar['dc_id'];
            $item['brand_info']  = $brandModel->getBrandModel($dreamCar['brand_id']);
            $item['series_info'] = $brandModel->getSeriesModel($dreamCar['brand_id'],$dreamCar['series_id']);
            $items[] = $item;
        }

        $this->send($items);
    }

/**
 * @api {POST} /v3/DreamCar/getsameuser 梦想车同款车主
 * @apiName get same dream user
 * @apiGroup DreamCar
 * @apiDescription 梦想车同款车主
 * @apiPermission anyone
 * @apiSampleRequest http://www.bibicar.cn:8090
 *
 * @apiParam {string} [device_identifier] 设备唯一标识
 * @apiParam {string} [session_id] session_id
 * @apiParam {number} [series_id] 系列id any id
 * @apiParam {number} [brand_id] 品牌id any id
 * 
 * @apiParam {json} data object
 * @apiUse DreamParam
 * @apiParamExample {json} 请求样例
 *   POST /v3/DreamCar/getsameuser
 *   {
 *     "data": {
 *       "device_identifier":"",
 *       "session_id":"",
 *       "series_id":"",
 *       "brand_id":"",
 *       
 *       
 *       
 *     }
 *   }
 *
 */
    public function getsameuserAction(){
        
        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id','series_id','brand_id')
        );
        
        $data = $this->get_request_data();
        $cars=new CarSellingModel();
        $response=$cars->getSameDreamCarUser($data);

        $this->send($response);
    }


    public function testpushcarAction(){
            $data['series_id']=1994;
            $data['brand_id']=8;
            $CarSellingM=new CarSellingModel;
            $car=$CarSellingM->pushSametoCarUser($data);
            if($car){
                $toId=544; 
                $carId=$car['hash'];
                $mh = new MessageHelper;
                $mh->recommendNotify($toId,$carId);
            }
          
            
    }


}