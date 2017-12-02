<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/12/12
 * Time: 下午12:30
 */
class CarReportController extends ApiYafControllerAbstract
{

    public $info_fields = array(
        'session_id', 'files_id', 'files_type','car_color','brand_id','series_id','model_id',
        'contact_phone','contact_name','contact_address','guide_price', 'board_fee','insurance_fee',
        'other_fee','other_fee_intro','extra_info','bank_no','bank_name','bank_account');

    public function publishProgress($data,$userId){

        $properties['car_id']=$data['car_id'];
        $properties['hash']=$data['hash'];
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
        $properties['car_name'] = $brandM['brand_name'] . ' ' . $seriesM['series_name'] . ' ' . $modelM['model_name'];
        $properties['car_name'] = trim($properties['car_name']);

        $properties['brand_id']      =$data['brand_id'];
        $properties['series_id']     =$data['series_id'];
        $properties['model_id']      =$data['model_id'];
        $properties['model_name']    =$modelM['model_name'];
        $properties['series_name']   =$seriesM['series_name'];
        $properties['brand_name']    =$brandM['brand_name'];
        $properties['guide_price']   = $data['guide_price'];
        $properties['board_fee']     = $data['board_fee'];
        $properties['insurance_fee'] = $data['insurance_fee'];
        $properties['other_fee'] = $data['other_fee'];
        $properties['other_fee_intro'] = $data['other_fee_intro'];
        $properties['extra_info'] = $data['extra_info'];
        $properties['bank_no'] = $data['bank_no'];
        $properties['bank_name'] = $data['bank_name'];
        $properties['bank_account'] = $data['bank_account'];
        $properties['contact_phone'] = $data['contact_phone'];
        $properties['contact_name'] = $data['contact_name'];
        $properties['contact_address'] = $data['contact_address'];

        $time = time();
        $properties['created'] = $time;
        $properties['updated'] = $time;

//        $filesInfo = $this->dealFilesWithString($data['files_id'], $data['files_type']);
//
//        $properties['files'] = $filesInfo ? serialize($filesInfo) : '';
//
//        if (!$properties['files']) {
//            $this->send_error(CAR_CREATE_FILES_ERROR);
//        }

        return $properties;
    }
    /**
     * @api {POST} /v4/carreport/create  生成报价单
     * @apiName carreport create
     * @apiGroup Carreport
     * @apiDescription 生成报价单
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.1.0
     *
     * @apiParam (request) {string} [device_identifier] 设备唯一标识
     * @apiParam (request) {string} [session_id] session_id
     * @apiParam (request) {Object} [file_type] 图片类型说明 默认填写 1
     * @apiParam (request) {Object} [files_id] 七牛图片hash
     * @apiParam (request) {number} car_color 车辆颜色
     * @apiParam (request) {number} brand_id 车品牌id
     * @apiParam (request) {number} series_id 车系列id
     * @apiParam (request) {number} model_id 车型id
     * @apiParam (request) {string} contact_phone 联系电话
     * @apiParam (request) {string} contact_name 联系人姓名
     * @apiParam (request) {string} [contact_address] 联系地址
     * @apiParam (request) {string} guide_price 指导价
     * @apiParam (request) {string} [board_fee] 上牌费用
     * @apiParam (request) {string} [insurance_fee] 保险费用
     * @apiParam (request) {string} [other_fee] 其他费用
     * @apiParam (request) {string} [other_fee_intro] 其他费用说明
     * @apiParam (request) {string} [bank_no] 银行卡号
     * @apiParam (request) {string} [bank_name] 银行名称
     * @apiParam (request) {string} [bank_account] 开户人名称
     * @apiParam (request) {string} [extra_info] 基本配置选项(id与逗号拼接字符串 2,3,4,5)
     *
     */
    public function createAction()
    {

        $this->required_fields = array_merge(
            $this->required_fields,
            $this->info_fields
        );

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $properties = $this->publishProgress($data, $userId);


        $csReport  = new CarSellingReportModel();

        $csReport->properties = $properties;

        $carId = $csReport->CreateM();

        if ($carId) {

            $response['info']=111;

//            $title = is_array($carInfo['user_info']) ?
//                $carInfo['user_info']['profile']['nickname'] . '的' . $carInfo['car_name']
//                : $carInfo['car_name'];
//            $response['share_title'] = $title;
//            $response['share_url'] = 'http://share.bibicar.cn/views/detail/car.html?ident='.$data['device_identifier'].'&session='.$data['session_id'].'&id='.$properties['hash'];
//            $response['share_txt'] = '更多精选二手车在bibi car,欢迎您来选购!';
//            $response['share_img'] = isset($carInfo['files']["type1"]) ? $carInfo['files']["type1"][0]['file_url'] : '';
            $this->send($response);
        } else {
            $this->send_error(CAR_ADDED_ERROR);

        }

    }




    /**
     * @api {POST} /v4/Publishcar/update 更新车辆
     * @apiName car update
     * @apiGroup Car
     * @apiDescription 更新车辆
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.0.0
     *
     * @apiParam {string} [device_identifier] 设备唯一标识
     * @apiParam {string} [session_id] session_id
     * @apiParam {Object} [file_type] 文字说明 车辆照片类型 (1:外观 2:中控内饰 3:发动机及结构 4:更多细节)
     *
     * @apiParam {Object} [files_id] 图片
     * @apiParam {string} [car_id] 车辆Id
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

        $car_info_ids = @$data['car_info_ids'];
        $city_code = @$data['city_code'];

        unset($data['city_code']);
        unset($data['car_info_ids']);
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



            $last_str = substr($car_info_ids, -1);

            if($last_str == ','){
                $car_info_ids = substr($car_info_ids,0,strlen($car_info_ids)-1);
            }

            $ExtraModel = new CarSellingExtraInfoModel();

            $ExtraModel->updateExtraInfo($result['id'],$data['car_id'],$car_info_ids);

            $cs = new CarSellingV1Model();

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