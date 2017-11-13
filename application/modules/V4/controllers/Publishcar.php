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

        if(isset($data['board_address'])){
            $properties['board_address']=$data['board_address'];
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
     * @api {POST} /v4/Publishcar/create  上传二手车
     * @apiName car create
     * @apiGroup Car
     * @apiDescription 发布朋友圈
     * @apiPermission anyone
     * @apiSampleRequest http://www.bibicar.cn:8090
     * @apiVersion 2.1.0
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
     * @apiParam {string} [board_address] 上牌地点(城市:深圳)
     * @apiParam {string} [car_info_ids] 基本配置选项(id与逗号拼接字符串 2,3,4,5)
     *
     * @apiParam {json} data object
     * @apiUse Data
     * @apiParamExample {json} 请求样例
     *   POST /v4/Publishcar/create
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
        $car_info_ids = @$data['car_info_ids'];

        unset($data['car_info_ids']);
        unset($data["v3/publishcar/create"]);
        $userId = $this->userAuth($data);

        $cs = new CarSellingModel();

        $car_info_ids = $data['car_info_ids'];

        unset($data['car_info_ids']);

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

            if($car_info_ids){
                $ExtraModel = new CarSellingExtraInfoModel();
                $insert['hash']=$properties['hash'];
                $insert['ids']=$car_info_ids;
                $id = $ExtraModel->insert('bibi_car_selling_list_extra_info',$insert);
            }

            //我的关注数据myfocus
            $UserFocusM= new MyFocusModel();
            $UserFocusM->created_at =time();
            $UserFocusM->type = 1;
            $UserFocusM->type_id = $properties['hash'];
            $UserFocusM->user_id =  $userId;
            $UserFocusM->saveProperties();
            $id = $UserFocusM->CreateM();

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
     * @api {POST} /v4/Publishcar/newCar  上传新车
     * @apiName car newCar
     * @apiGroup Car
     * @apiDescription 发布朋友圈
     * @apiPermission anyone
     * @apiSampleRequest http://www.bibicar.cn:8090
     * @apiVersion 2.0.0
     *
     * @apiParam {string} device_identifier 设备唯一标识
     * @apiParam {string} session_id session_id
     * @apiParam {Object} file_type 文字说明
     * @apiParam {Object} files_id 图片
     * @apiParam {number} car_color 车辆颜色
     * @apiParam {string} city_id 车辆类型 0:新车 1:二手车 3:爱车
     * @apiParam {string} brand_id 车品牌id
     * @apiParam {number} model_id 车型id
     * @apiParam {number} series_id 车系列id
     * @apiParam {string} car_no 车牌号码
     * @apiParam {string} car_intro 车主介绍
     * @apiParam {number} action 上传车类型
     * @apiParam {string} price 价格
     * @apiParam {string} contact_phone 联系电话
     * @apiParam {string} contact_address 联系地址
     * @apiParam {string} contact_name 联系人姓名
     * @apiParam {string} [vin_no] 车架号
     * @apiParam {string} [vin_file] 驾驶证照片
     * @apiParam {number} [mileage] 里程
     * @apiParam {string} [engine_no] 发动机号
     * @apiParam {number} [is_transfer] 是否过户
     * @apiParam {number} [car_status] 车辆状态
     * @apiParam {string} [board_time] 上牌时间
     * @apiParam {string} [car_info_ids] 基本配置选项(id与逗号拼接字符串 2,3,4,5)
     *
     * @apiParamExample {json} 请求样例
     *   POST /v4/Publishcar/newCar
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "files_id":"",
     *       "files_type":"",
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

        $car_info_ids = @$data['car_info_ids'];

        unset($data['car_info_ids']);

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

            if($car_info_ids){
                $ExtraModel = new CarSellingExtraInfoModel();
                $insert['hash']=$properties['hash'];
                $insert['ids']=$car_info_ids;
                $id = $ExtraModel->insert('bibi_car_selling_list_extra_info',$insert);
            }

            //我的关注数据myfocus
            $UserFocusM= new MyFocusModel();
            $UserFocusM->created_at =time();
            $UserFocusM->type = 1;
            $UserFocusM->type_id = $properties['hash'];
            $UserFocusM->user_id =  $userId;
            $UserFocusM->saveProperties();
            $id = $UserFocusM->CreateM();

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
     * @api {POST} /v4/publishcar/getcompanylist 车行列表
     * @apiName compayny list
     * @apiGroup Car
     * @apiDescription 车行列表接口
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.0.0
     *
     * @apiParam {string} device_identifier 设备唯一标识
     * @apiParam {string} session_id session_id
     * @apiParam {string} page 页码
     *
     *
     * @apiUse Data
     * @apiParamExample {json} 请求样例
     *   POST /v4/publishcar/getcompanylist
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

        $this->required_fields = array_merge($this->required_fields, array('session_id','page'));
        $data = $this->get_request_data();
        $Profile=new ProfileModel();
        $page     = $data['page'] ? ($data['page']+1) : 1;
        $user=$Profile->getCompanylistV1($page);
        $response['list']=$user;
        $this->send($response);

    }

    /**
     * @api {POST} /v4/Publishcar/list 我的售车
     * @apiName Car list
     * @apiGroup Car
     * @apiDescription 我的售车
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.0.0
     *
     * @apiParam {string} device_identifier 设备唯一标识
     * @apiParam {string} session_id session_id
     * @apiParam {number} [brand_id] 品牌id
     * @apiParam {number} [user_id] 用户id
     * @apiParam {number} [series_id] 系列id
     * @apiParam {number} [type] type类型 1：已售出 2:在售 ,3：在售最新＋已出售 4 所有售车；
     * @apiParam {number} page 页数
     *
     * @apiParam {json} data object
     * @apiUse Data
     * @apiParamExample {json} 请求样例
     *   POST /v4/Publishcar/list
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

        $carM = new CarSellingV1Model();

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

            }else if($data['type'] == 4){
                $carM->verify_status=1;
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

        $friendShipM = new FriendShipModel();

        $friendShip = $friendShipM->getMyFriendShip($userId, $objId);

        $response['is_friend'] = isset($friendShip['user_id']) ? 1 : 2;

        $this->send($response);
    }











}