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










}