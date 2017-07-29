<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/12/12
 * Time: 下午12:30
 */
class PublishcarController extends ApiYafControllerAbstract
{

    public $new_car_info_fields = array(

        'brand_id' => CAR_BRAND_SERIES_MODEL_ERROR,
        'series_id' => CAR_BRAND_SERIES_MODEL_ERROR,
        'model_id' => CAR_BRAND_SERIES_MODEL_ERROR,
        'price' => CAR_PRICE_ERROR,
        'city_id' => CAR_CITY_ERROR,
        'car_color' => CAR_COLOR_ERROR,
        'car_no' => CAR_NO_ERROR,
        'car_intro' => CAR_INTRO_ERROR,
    );


    public $car_info_fields = array(

        'brand_id' => CAR_BRAND_SERIES_MODEL_ERROR,
        'series_id' => CAR_BRAND_SERIES_MODEL_ERROR,
        'model_id' => CAR_BRAND_SERIES_MODEL_ERROR,
        'price' => CAR_PRICE_ERROR,
        //'board_time' => CAR_BOARD_TIME_ERROR,
        'mileage' => CAR_MILEAGE_ERROR,
        'car_status' => CAR_STATUS_ERROR,
        'city_id' => CAR_CITY_ERROR,
        'car_color' => CAR_COLOR_ERROR,
        'car_no' => CAR_NO_ERROR,
        'contact_name' => CAR_CONTACT_NAME_ERROR,
        'contact_address' => CAR_CONTACT_ADDRESS_ERROR,
        'maintain' => CAR_MAINTAIN_ERROR,
        //'is_transfer' => CAR_IS_TRANSFER_ERROR,
        //'insurance_due_time' => CAR_INSURANCE_DUE_TIME_ERROR,
        //'check_expiration_time' => CAR_EXPIRATION_TIME_ERROR,
        'car_intro' => CAR_INTRO_ERROR,
        'exchange_time' => CAR_EXCHANGE_TIME_ERROR,

    );

    public $vin_fields = array('vin_no', 'vin_file');

    public function publishProgress($data,$userId,$cs,$car_type=PLATFORM_USER_SELLING_CAR,$act='insert'){

//        if ($data['action']) {
//
//            $this->submitCheck($data, $this->car_info_fields);
//
//        }

        if (!$data['vin_no'] && !$data['vin_file'] && $act == 'insert' && $car_type==PLATFORM_USER_SELLING_CAR) {

            $this->send_error(CAR_DRIVE_INFO_ERROR);
        }


        $properties = $data;
        $properties['car_type'] = $car_type;
        unset($properties['device_identifier']);
        unset($properties['session_id']);
        unset($properties['files_id']);
        unset($properties['files_type']);

        $properties['user_id'] = $userId;

        $bm = new BrandModel();
        $brandM = $bm->getBrandModel($data['brand_id']);
        $seriesM = $bm->getSeriesModel($data['brand_id'], $data['series_id']);
        $modelM = $bm->getModelModel($data['series_id'], $data['model_id']);


        if (!is_array($brandM)) {

            $this->send_error(CAR_BRAND_ERROR);
        }

        if (!is_array($seriesM)) {
            $this->send_error(CAR_SERIES_ERROR);
        }

        if (!is_array($modelM)) {

            $this->send_error(CAR_MODEL_ERROR);
        }
        if(isset($data['board_time'])){
            if(strlen($data['board_time']) > 4 ){
                $properties['board_time']=date('Y',strtotime($data['board_time']));
            }else{
                $properties['board_time']=$data['board_time'];
            }
            
        }

        if(isset($data['contact_phone'])){
                $properties['contact_phone']=$data['contact_phone'];
        }
        
        $properties['car_name'] = $brandM['brand_name'] . ' ' . $seriesM['series_name'] . ' ' . $modelM['model_name'];
        $properties['car_name'] = trim($properties['car_name']);
        
        $filesInfo = $cs->dealFilesWithString($data['files_id'], $data['files_type']);

        $time = time();
        if($act == 'insert'){

            $properties['created'] = $time;
            $properties['updated'] = $time;
        }
        else{

            $properties['updated'] = $time;
        }

        $properties['files'] = $filesInfo ? serialize($filesInfo) : '';

        if ( !$properties['files'] ) {
            $this->send_error(CAR_CREATE_FILES_ERROR);
        }


        $profileModel = new \ProfileModel;
        $userInfo = $profileModel->getProfile($userId);

        $user_type=@$userInfo['type'];



        if($user_type == 2){
            $properties['verify_status'] = $car_type == (PLATFORM_USER_SELLING_CAR || PLATFORM_USER_NEW_CAR) ? CAR_VERIFIED : CAR_AUTH;

        }else{
            $company= @$userInfo['company'];
            //echo $userInfo['company'];exit;
            if($company){

                $properties['verify_status'] = $car_type == (PLATFORM_USER_SELLING_CAR || PLATFORM_USER_NEW_CAR) ? CAR_VERIFIED : CAR_AUTH;
            }else{
                $properties['verify_status'] = $car_type == (PLATFORM_USER_SELLING_CAR || PLATFORM_USER_NEW_CAR) ? CAR_VERIFYING : CAR_NOT_AUTH;
            }




        }
        unset($properties['action']);

        return $properties;
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
 * @api {POST} /v3/Publishcar/update 更新车辆
 * @apiName car update
 * @apiGroup Car
 * @apiDescription 发布朋友圈
 * @apiPermission anyone
 * @apiSampleRequest http://www.bibicar.cn:8090
 *
 * @apiParam {string} [device_identifier] 设备唯一标识
 * @apiParam {string} [session_id] session_id
 * @apiParam {Object} [file_type] 文字说明 车辆照片类型 (1:外观 2:中控内饰 3:发动机及结构 4:更多细节)
 *
 * @apiParam {Object} [files_id] 图片
 * @apiParam {string} [car_id] 位置经度
 * @apiParam {number} [car_type] 车辆类型 0:新车 1:二手车 3:爱车
 * 
 * @apiParam {json} data object
 * @apiUse Data
 * @apiParamExample {json} 请求样例
 *   POST /v3/Publishcar/update
 *   {
 *     "data": {
 *       "device_identifier":"",
 *       "session_id":"",
 *       "files_id":"",
 *       "files_type":"",
 *       "car_id":"",
 *       "car_type":"",
 *       
 *       
 *     }
 *   }
 *
 */

    public function updateAction()
    {

        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id','files_id', 'files_type','car_id','car_type')
            //array_keys($this->car_info_fields),
            //$this->vin_fields
        );
        
        $data = $this->get_request_data();
        unset($data['v3/publishcar/update']);
        $userId = $this->userAuth($data);

        $cs = new CarSellingModel();

        $properties = $this->publishProgress($data, $userId, $cs, $data['car_type'],'update');

        unset($properties['car_id']);
        unset($properties['created']);
        unset($properties['verify_status']);


        $cs->properties = $properties;
        $result=$cs->getCarById($data['car_id']);
        
        foreach($properties as $k =>$val){
            if($properties[$k]!=$result[$k]){
                $updated=array();
                $updated['clumn']=$k;
                $updated['value']=$val;
                $updated['car_id']=$data['car_id'];
                $updated['updated']=time();
                $updated['user_id']=$userId;
                 $cs->insert('bibi_car_selling_list_updated',$updated);
            }
        }

        $rs = $cs->updateByPrimaryKey($cs::$table,array('hash'=>$data['car_id']),$properties);

        if($rs){

           
            $carInfo = $cs->GetCarInfoById($data['car_id']);

            $ifr = new ItemFilesRelationModel();

            $ifr->DeleteBatch($carInfo['car_id'], ITEM_TYPE_CAR);
            $ifr->CreateBatch($carInfo['car_id'], $data['files_id'], ITEM_TYPE_CAR, $data['files_type']);

            $response['car_info'] = $carInfo;

            $this->send($response);

        } else {

            $this->send_error(CAR_ADDED_ERROR);

        }

    }
/**
 * @api {POST} /v3/Publishcar/create  上传二手车
 * @apiName car create
 * @apiGroup Car
 * @apiDescription 发布朋友圈
 * @apiPermission anyone
 * @apiSampleRequest http://www.bibicar.cn:8090
 *
 * @apiParam {string} [device_identifier] 设备唯一标识
 * @apiParam {string} [session_id] session_id
 * @apiParam {Object} [file_type] 文字说明
 * @apiParam {Object} [files_id] 图片
 * @apiParam {number} [car_color] 车辆颜色 
 * @apiParam {string} [city_id] 车辆类型 0:新车 1:二手车 3:爱车
 * @apiParam {number} [model_id] 车型id
 * @apiParam {number} [series_id] 车系列id
 * @apiParam {number} [action] 上传车类型
 * @apiParam {string} [contact_phone] 联系电话
 * @apiParam {string} [vin_no] 车架号
 * @apiParam {string} [vin_file] 驾驶证照片
 * @apiParam {number} [mileage] 里程 
 * @apiParam {string} [brand_id] 车品牌id
 * @apiParam {string} [engine_no] 发动机号
 * @apiParam {number} [is_transfer] 是否过户
 * @apiParam {string} [contact_address] 联系地址
 * @apiParam {string} [car_no] 车牌号码
 * @apiParam {string} [car_intro] 车主介绍
 * @apiParam {string} [contact_name] 联系人姓名
 * @apiParam {string} [price] 价格
 * @apiParam {number} [car_status] 车辆状态
 * @apiParam {string} [board_time] 上牌时间
 * 
 * @apiParam {json} data object
 * @apiUse Data
 * @apiParamExample {json} 请求样例
 *   POST /v3/Publishcar/create
 *   {
 *     "data": {
 *       "device_identifier":"",
 *       "session_id":"",
 *       "files_id":"",
 *       "files_type":"",
 *       "car_id":"",
 *       "car_color":"",
 *       "city_id":"",
 *       "model_id":"",
 *       "series_id":"",
 *       "action":"",
 *       "contact_phone":"",
 *       "vin_no":"",
 *       "vin_file":"",
 *       "mileage":"",
 *       "brand_id":"",
 *       "engine_no":"",
 *       "is_transfer":"",
 *       "contact_address":"",
 *       "car_no":"",
 *       "car_intro":"",
 *       "contact_name":"",
 *       "price":"",
 *       "car_status":"",
 *       "board_time":"",
 *       
 *       
 *     }
 *   }
 *
 */
    public function createAction()
    {

        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id', 'action', 'files_id', 'files_type'),
            array_keys($this->car_info_fields),
            $this->vin_fields
        );

        $data = $this->get_request_data();
        unset($data["v3/publishcar/create"]);
        $userId = $this->userAuth($data);

        $cs = new CarSellingModel();

        $properties = $this->publishProgress($data, $userId, $cs);

        $properties['hash'] = uniqid();

        unset($properties['car_id']);
        

        $cs->properties = $properties;

        $carId = $cs->CreateM();

        if ($carId) {

            $ifr = new ItemFilesRelationModel();
            $ifr->CreateBatch($carId, $data['files_id'], ITEM_TYPE_CAR, $data['files_type']);

            $carInfo = $cs->GetCarInfoById($properties['hash']);

            $response['car_info'] = $carInfo;

            $mh = new MessageHelper;
            $toId=389;
            $content = '用户:'.$userId.'上传了车，赶紧去审核吧';
            $mh->systemNotify($toId, $content);

            $this->send($response);

        } else {

            $this->send_error(CAR_ADDED_ERROR);

        }

    }

/**
 * @api {POST} /v3/Publishcar/newCar  上传新车
 * @apiName car newCar
 * @apiGroup Car
 * @apiDescription 发布朋友圈
 * @apiPermission anyone
 * @apiSampleRequest http://www.bibicar.cn:8090
 *
 * @apiParam {string} [device_identifier] 设备唯一标识
 * @apiParam {string} [session_id] session_id
 * @apiParam {Object} [file_type] 文字说明
 * @apiParam {Object} [files_id] 图片
 * @apiParam {number} [car_color] 车辆颜色 
 * @apiParam {string} [city_id] 车辆类型 0:新车 1:二手车 3:爱车
 * @apiParam {number} [model_id] 车型id
 * @apiParam {number} [series_id] 车系列id
 * @apiParam {number} [action] 上传车类型
 * @apiParam {string} [contact_phone] 联系电话
 * @apiParam {string} [vin_no] 车架号
 * @apiParam {string} [vin_file] 驾驶证照片
 * @apiParam {number} [mileage] 里程 
 * @apiParam {string} [brand_id] 车品牌id
 * @apiParam {string} [engine_no] 发动机号
 * @apiParam {number} [is_transfer] 是否过户
 * @apiParam {string} [contact_address] 联系地址
 * @apiParam {string} [car_no] 车牌号码
 * @apiParam {string} [car_intro] 车主介绍
 * @apiParam {string} [contact_name] 联系人姓名
 * @apiParam {string} [price] 价格
 * @apiParam {number} [car_status] 车辆状态
 * @apiParam {string} [board_time] 上牌时间
 * 
 * @apiParam {json} data object
 * @apiUse Data
 * @apiParamExample {json} 请求样例
 *   POST /v3/Publishcar/newCar
 *   {
 *     "data": {
 *       "device_identifier":"",
 *       "session_id":"",
 *       "files_id":"",
 *       "files_type":"",
 *       "car_id":"",
 *       "car_color":"",
 *       "city_id":"",
 *       "model_id":"",
 *       "series_id":"",
 *       "action":"",
 *       "contact_phone":"",
 *       "vin_no":"",
 *       "vin_file":"",
 *       "mileage":"",
 *       "brand_id":"",
 *       "engine_no":"",
 *       "is_transfer":"",
 *       "contact_address":"",
 *       "car_no":"",
 *       "car_intro":"",
 *       "contact_name":"",
 *       "price":"",
 *       "car_status":"",
 *       "board_time":"",
 *       
 *       
 *     }
 *   }
 *
 */
    public function newCarAction(){

        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id', 'action', 'files_id', 'files_type'),
            array_keys($this->new_car_info_fields)
        );

        $data = $this->get_request_data();
        
        unset($data['v3/publishcar/newCar']);
       
        $userId = $this->userAuth($data);

        $cs = new CarSellingModel();

        $properties = $this->publishProgress($data, $userId, $cs,PLATFORM_USER_NEW_CAR);

        $properties['hash'] = uniqid();

        unset($properties['car_id']);

        $cs->properties = $properties;

        $carId = $cs->CreateM();

        if ($carId) {

            $ifr = new ItemFilesRelationModel();
            $ifr->CreateBatch($carId, $data['files_id'], ITEM_TYPE_CAR, $data['files_type']);

            $carInfo = $cs->GetCarInfoById($properties['hash']);

            $response['car_info'] = $carInfo;


            $mh = new MessageHelper;
            $toId=389;
            $content = '用户:'.$userId.'上传了车，赶紧去审核吧';
            $mh->systemNotify($toId, $content);


            $this->send($response);

        } else {

            $this->send_error(CAR_ADDED_ERROR);

        }
    }

    private function submitCheck($data, $car_info_fields)
    {

        foreach ($car_info_fields as $k => $car_info_error) {

//            echo $k;
//            echo "\n";
//            var_dump($data[$k]);
//            echo mb_strlen($data[$k]);
//            echo "\n";
            if(mb_strlen($data[$k]) == 0){

                $this->send_error($car_info_error);
            }
        }

    }
/**
 * @api {POST} /v3/Publishcar/list 我的售车
 * @apiName Car list
 * @apiGroup Car
 * @apiDescription 我的售车
 * @apiPermission anyone
 * @apiSampleRequest http://testapi.bibicar.cn
 *
 * @apiParam {string} device_identifier 设备唯一标识
 * @apiParam {string} session_id session_id
 * @apiParam {number} [brand_id] 品牌id
 * @apiParam {number} [user_id] 用户id
 * @apiParam {number} [series_id] 系列id
 * @apiParam {number} [type] type类型 1：已售出 2:在售 ,3：在售最新＋已出售；
 * @apiParam {number} page 页数
 * 
 * @apiParam {json} data object
 * @apiUse Data
 * @apiParamExample {json} 请求样例
 *   POST /v3/Publishcar/list
 *   {
 *     "data": {
 *       "device_identifier":"",
 *       "session_id":"",
 *       "series_id":"",
 *       "brand_id":"",
 *       "user_id":"",
 *       "series_id":"",
 *       "page":"",
 *       
 *       
 *     }
 *   }
 *
 */
public function listAction()
    {

        $this->required_fields = array_merge($this->required_fields, array('session_id'));

        $carM = new CarSellingModel();

        $data = $this->get_request_data();

        $page = $data['page'] ? ($data['page']+1) : 1;

        $carM->page = $page;

        $userId = $this->userAuth($data);

        $objId = $this->getAccessId($data, $userId);


        $carM->currentUser = $objId;
        
        if(@$data['brand_id']){
             $carM->brand_id =  $data['brand_id'];
        }
        if(@$data['series_id']){
             $carM->series_id = $data['series_id'];
        }
        if(@$data['type']){
             if($data['type'] == 1){
                $carM->verify_status=4;
             }else if($data['type'] == 2){
                 $carM->verify_status=0;
             }else if($data['type'] ==3){
                 $carM->pageSize=5;
                 $carM->verify_status=0;
                 $sale=$carM->getUserPublishCar($objId);

                 $carM->verify_status=4;
                 $sold=$carM->getUserPublishCar($objId);

                $response['saleing']= $sale;
                $response['sold']= $sold;

                $this->send($response);
                return ;

             }
             
        }
        $list = $carM->getUserPublishCar($objId);
        if($userId != $objId){
           foreach($list["car_list"] as $key => $value){
                  unset($list["car_list"][$key]["car_info"]["vin_no"]);
                  unset($list["car_list"][$key]["car_info"]["engine_no"]);
                  unset($list["car_list"][$key]["car_info"]["vin_file"]);
           }
        }
        $response = $list;

        $this->send($response);
    }





    /**
     * @api {POST} /v3/publishcar/getcompanylist 车行列表
     * @apiName compayny list
     * @apiGroup Car
     * @apiDescription 车行列表接口
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     *
     * @apiParam {string} device_identifier 设备唯一标识
     * @apiParam {string} session_id session_id
     * @apiParam {string} page 页码
     *
     *
     * @apiUse Data
     * @apiParamExample {json} 请求样例
     *   POST /v3/publishcar/getcompanylist
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

    public function getCompanylistAction(){


        $this->required_fields = array_merge($this->required_fields, array('session_id'));
        $data = $this->get_request_data();

        $Profile=new ProfileModel();
        $page     = $data['page'] ? ($data['page']+1) : 1;
        $user=$Profile->getCompanylist($page);
        $response['list']=$user;
        $this->send($response);


    }










}