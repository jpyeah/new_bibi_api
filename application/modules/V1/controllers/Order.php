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

        $this->required_fields = array_merge($this->required_fields, array('session_id', 'car_id'));

        $data = $this->get_request_data();

        //$userId = $this->userAuth($data);
        if(@$data['session_id']){

            $sess = new SessionModel();
            $userId = $sess->Get($data);
        }
        else{

            $userId = 0;
        }

        $carModel = new CarSellingModel();

        $carT = $carModel::$table;

        $carId = $data['car_id'];

        $carModel->currentUser = $userId;

        $carInfo = $carModel->GetCarInfoById($carId);


        $response['car_info'] = $carInfo;

        $brandId = isset($carInfo['brand_info']['brand_id']) ? $carInfo['brand_info']['brand_id'] : 0;


        $response['car_users'] = $carModel->getSameBrandUsers($brandId);

        //同款车
        $response['related_price_car_list'] = $carModel->relatedPriceCars($carId,$carInfo['price']);

        //同价车
        $response['related_style_car_list'] = $carModel->relatedStyleCars(
            $carId,
            $carInfo['brand_info']['brand_id'] ,
            $carInfo['series_info']['series_id']
        );


        $visitCarM = new VisitCarModel();
        $visitCarM->car_id  = $carId;
        $visitCarM->user_id = $userId;

        $id = $visitCarM->get();

        if(!$id){

            $properties = array();
            $properties['created'] = time();
            $properties['user_id'] = $userId;
            $properties['car_id']  = $carId;

            $carModel->updateByPrimaryKey(
                $carT,
                array('hash'=>$carId),
                array('visit_num'=>($carInfo['visit_num']+1))
            );

            $visitCarM->insert($visitCarM->tableName, $properties);
        }

        $title = is_array($carInfo['user_info']) ?
            $carInfo['user_info']['profile']['nickname'] . '的' . $carInfo['car_name']
            : $carInfo['car_name'];

        $response['share_title'] = $title;
        //http://m.bibicar.cn/post/index?device_identifier='.$data['device_identifier'].'&fcar_id='.$carId.'
        $response['share_url'] = 'http://wx.bibicar.cn/car/index/car_id/'.$carId.'';
        $response['share_txt'] = '更多精选二手车在bibi car,欢迎您来选购!';
        $response['share_img'] = isset($carInfo['files'][0]) ? $carInfo['files'][0]['file_url'] : '';

        $this->send($response);


    }




    public function listAction(){


        $jsonData = require APPPATH .'/configs/JsonData.php';

        $this->optional_fields = array('keyword','order_id','brand_id','series_id');
        //$this->required_fields = array_merge($this->required_fields, array('session_id'));


        $data = $this->get_request_data();

        $data['order_id'] = $data['order_id'] ? $data['order_id'] : 0 ;
        $data['page']     = $data['page'] ? ($data['page']+1) : 1;
        $data['brand_id'] = $data['brand_id'] ? $data['brand_id'] : 0 ;
        $data['series_id'] = $data['series_id'] ? $data['series_id'] : 0 ;


        $carM = new CarSellingModel();
        $where = 'WHERE t1.files <> "" AND t1.brand_id <> 0 AND t1.series_id <> 0 AND t1.car_type <> 3 ';

        if($data['keyword']){
            $carM->keyword = $data['keyword'];
            $where .= ' AND t1.car_name LIKE "%'.$carM->keyword.'%" ';
        }

        if($data['brand_id']){

            $where .= ' AND t1.brand_id = '.$data['brand_id'].' ';
        }

        if($data['series_id']){

            $where .= ' AND t1.series_id = '.$data['series_id'].' ';
        }

        if($data['source'] == 1){

            $where .= ' AND t1.car_type = 1';
        }


        $carM->where = $where;

        if(isset($jsonData['order_info'][$data['order_id']])) {

            // $carM->order  = ' ORDER BY t1.car_type ASC , ';
            $carM->order = $jsonData['order_info'][$data['order_id']];

        }

        $carM->page = $data['page'];

        $sess = new SessionModel();
        $userId = $sess->Get($data);

        $carM->currentUser = $userId;

        $lists = $carM->getCarList();

        if($lists['car_list']){

            foreach($lists['car_list'] as $key => $list){

                $file = isset($list['car_info']['files'][0]) ?  $list['car_info']['files'][0] : array();

                $lists['car_list'][$key]['car_info']['files'] = array();
                $lists['car_list'][$key]['car_info']['files'][] = $file;
            }
        }


        //$response = array();
        $response = $lists;
        $response['order_id'] = $data['order_id'];

        if($data['city_id']){

            $jsonData['city_info']['city_id'] = $data['city_id'];
            $jsonData['city_info']['city_lat'] = $data['city_lat'];
            $jsonData['city_info']['city_lng'] = $data['city_lng'];

        }

        $response['city_info'] = $jsonData['city_info'];
        $response['keyword']   = $data['keyword'];
        $bm = new BrandModel();
        $response['brand_info'] = $bm->getBrandModel($data['brand_id']);
        $response['series_info'] = $bm->getSeriesModel($data['brand_id'],$data['series_id']);

        $this->send($response);

    }







}
