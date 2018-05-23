<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/13
 * Time: 下午6:09
 */
class CarController extends ApiYafControllerAbstract
{

    public function indexAction()
    {

//        $this->required_fields = array_merge($this->required_fields, array('session_id', 'model_id'));
//
//        $data = $this->get_request_data();
//
//        //$userId = $this->userAuth($data);
//        if(@$data['session_id']){
//            $sess = new SessionModel();
//            $userId = $sess->Get($data);
//        }
//        else{
//
//            $userId = 0;
//        }

        $data['model_id'] = 120223;

        $carModel = new CarSellingModel();

        $carT = $carModel::$table;

        $ModleId = $data['model_id'];

        $carInfo = $carModel->GetCarInfoById($ModleId);

        $response['car_info'] = $carInfo;

//        $brandId = isset($carInfo['brand_info']['brand_id']) ? $carInfo['brand_info']['brand_id'] : 0;
//
//        $title = is_array($carInfo['user_info']) ?
//                    $carInfo['user_info']['profile']['nickname'] . '的' . $carInfo['car_name']
//                    : $carInfo['car_name'];
//
//        $response['share_title'] = $title;
//        //http://m.bibicar.cn/post/index?device_identifier='.$data['device_identifier'].'&fcar_id='.$carId.'
//        $response['share_url'] = 'http://wx.bibicar.cn/car/index/car_id/'.$carId.'';
//        $response['share_txt'] = '更多精选二手车在bibi car,欢迎您来选购!';
//        $response['share_img'] = isset($carInfo['files'][0]) ? $carInfo['files'][0]['file_url'] : '';

        $this->send($response);


    }










}
