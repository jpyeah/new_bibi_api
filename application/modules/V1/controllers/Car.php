<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/13
 * Time: 下午6:09
 */
use JPush\Client as JPush;

class CarController extends ApiYafControllerAbstract
{

    /**
     * @api {POST} /v1/car/series 车辆系列表(首页)
     * @apiName car series
     * @apiGroup Car
     * @apiDescription 车辆系列表(首页)
     * @apiPermission anyone
     * @apiSampleRequest http://new.bibicar.cn
     * @apiVersion 1.0.0
     *
     * @apiParam {string} page 页码 从0开始
     *
     * @apiParamExample {json} 请求样例
     *    POST /v1/car/series
     *   {
     *     "data": {
     *       "page":"",
     *
     *     }
     *   }
     *
     */
    public function seriesAction(){

       // $this->required_fields = array_merge($this->required_fields,array('session_id'));

        $data = $this->get_request_data();

      //  $data['page']     = $data['page'] ? ($data['page']+1) : 1;

        $BrandM = new BrandModel();

        $list = $BrandM->getSeries();

        $this->send($list);
    }


    /**
     * @api {POST} /v1/car/list 车辆列表
     * @apiName car list
     * @apiGroup Car
     * @apiDescription 车辆列表
     * @apiPermission anyone
     * @apiSampleRequest http://new.bibicar.cn
     * @apiVersion 1.0.0
     *
     * @apiParam {string} series_id 系列ID
     *
     * @apiSuccess {string} model_name 车型
     * @apiSuccess {string} exterior 外饰
     * @apiSuccess {string} interior 内饰
     * @apiSuccess {string} version 版本
     * @apiSuccess {string} image 图片
     * @apiSuccess {string} car_id 车辆id
     *
     *
     * @apiParamExample {json} 请求样例
     *    POST /v1/car/list
     *   {
     *     "data": {
     *       "series_id":"",
     *
     *     }
     *   }
     *
     */
     public function  listAction(){

        $this->required_fields = array_merge($this->required_fields, array('session_id', 'series_id'));

        $data = $this->get_request_data();

        $carM = new CarSellingModel();

        $carM->where = " WHERE series_id =".$data['series_id'];

        $list = $carM->getCarListBySeries($data['series_id']);

         $list['share_title'] = "更多精选豪车在bibicar,欢迎您来选购";
         //http://m.bibicar.cn/post/index?device_identifier='.$data['device_identifier'].'&fcar_id='.$carId.'
         $list['share_url'] = 'http://www.bibicars.com/recommend.html?id='.$data['series_id'];
         $list['share_txt'] = '更多精选豪车在bibicar,欢迎您来选购!';
         $list['share_img'] = "http://img.bibicar.cn/bibilogo.png";

        $this->send($list);
    }

    /**
     * @api {POST} /v1/car/search 车辆搜索
     * @apiName car search
     * @apiGroup Car
     * @apiDescription 车辆搜索(首页)
     * @apiPermission anyone
     * @apiSampleRequest http://new.bibicar.cn
     * @apiVersion 1.0.0
     *
     * @apiParam {string} page 页码 从0开始
     * @apiParam {string} keyword 关键词
     *
     * @apiParamExample {json} 请求样例
     *    POST /v1/car/search
     *   {
     *     "data": {
     *       "page":"",
     *       "keyword":"",
     *
     *     }
     *   }
     *
     */
    public function SearchAction(){

        $this->required_fields = array_merge($this->required_fields, array('keyword','page'));

        $data = $this->get_request_data();


        if(@$data['session_id']){
            $sess = new SessionModel();
            $userId = $sess->Get($data);
        }else{
            $userId = 0;
        }

        $data['page']     = $data['page'] ? ($data['page']+1) : 1;

        $number = ($data['page']-1)*10;

        $carM = new CarSellingModel();

        $where = ' WHERE car_name LIKE "%'.$data['keyword'].'%" ';

        $carM->where = $where;

        $carM->page = $data['page'];

        $results = $carM->getCarlist($userId);

        return $this->send($results);
    }

    /**
     * @api {POST} /v1/car/index 车辆详情
     * @apiName car index
     * @apiGroup Car
     * @apiDescription 车辆详情
     * @apiPermission anyone
     * @apiSampleRequest http://new.bibicar.cn
     * @apiVersion 1.0.0
     *
     * @apiParam {string} session_id session
     * @apiParam {string} car_id 车辆Id
     *
     * @apiParamExample {json} 请求样例
     *    POST /v1/car/index
     *   {
     *     "data": {
     *       "session_id":"",
     *       "car_id":"",
     *
     *     }
     *   }
     *
     */
    public function indexAction()
    {

        $this->required_fields = array_merge($this->required_fields, array('session_id', 'car_id'));

        $data = $this->get_request_data();

        //$userId = $this->userAuth($data);
        if(@$data['session_id']){
            $sess = new SessionModel();
            $userId = $sess->Get($data);
        }else{
            $userId = 0;
        }

        $carModel = new CarSellingModel();

        $carT = $carModel::$table;

        $carId = $data['car_id'];

        $carInfo = $carModel->GetCarInfoByHash($carId,$userId);

        if($carInfo){

            $response['car_info'] = $carInfo;

            $response['share_title'] = @$carInfo['car_name'];
            //http://m.bibicar.cn/post/index?device_identifier='.$data['device_identifier'].'&fcar_id='.$carId.'
            $response['share_url'] = 'http://www.bibicars.com/detail.html?id='.$data['car_id'];
            $response['share_txt'] = '更多精选豪车在bibicar,欢迎您来选购!';
            $response['share_img'] = @$carInfo['image'];

            $this->send($response);
        }else{

            $this->send_error(CAR_NOT_EXIST);

        }




    }


    public function detailAction(){

        $brandM = new BrandModel();

        $model_id=123;

        $car['model_detail']= $brandM->getModelDetail($model_id);

        print_r($car);exit;


    }


    public function testAction(){

        $app_key="68b7d81675c93b86ec6a11ac";
        $master_secret="81072e86bacd7dd98d4cd310";

        $client = new JPush($app_key, $master_secret);

        $cid =uniqid();

        $response = $client->push()
           // ->setCid($cid)
            ->setPlatform(['ios', 'android'])
            ->setAudience('all')
            ->setNotificationAlert('车辆推送')
            ->iosNotification('保时捷车辆推送', [
                'sound' => 'sound',
                'badge' => '+1',
                'extras' => [
                    'type' => '1',
                    'title' => '保时捷上新',
                    'content' => '感谢你的关注，保时捷新款车上新,请点击此推送框，进入车辆详情页面',
                    'from' => '保时捷',
                    'image_url' => 'http://img.bibicar.cn/logo.png',
                    'created_at' => date('y/m/d',time()),
                    'related_id' => "5b123cfba370d",
                ]
            ])
            ->androidNotification('车辆推送')
            ->message('保时捷车辆推送', [
                'title' => '车辆推送',
                'content_type' => 'text',
                'extras' => [
                    'type' => '1',
                    'title' => '保时捷上新',
                    'content' => '感谢你的关注，保时捷新款车上新,请点击此推送框，进入车辆详情页面',
                    'from' => '保时捷',
                    'image_url' => 'http://img.bibicar.cn/logo.png',
                    'created_at' => date('y/m/d',time()),
                    'related_id' => "5b123cfba370d",
                ]
            ])
            ->send();

        print_r($response);exit;
    }


    public function pushtAction(){

        $app_key="68b7d81675c93b86ec6a11ac";
        $master_secret="81072e86bacd7dd98d4cd310";

        $client = new JPush($app_key, $master_secret);

        $cid =uniqid();

        $regId = array('1114a89792fad3cb19e');

        $response = $client->push()
            // ->setCid($cid)
            ->setPlatform(['ios', 'android'])
           // ->setAudience('all')
            ->addRegistrationId($regId)
            ->setNotificationAlert('车辆推送')
            ->iosNotification('保时捷车辆推送', [
                'sound' => 'sound',
                'badge' => '+1',
                'extras' => [
                    'type' => '1',
                    'title' => '保时捷上新',
                    'content' => '感谢你的关注，保时捷新款车上新,请点击此推送框，进入车辆详情页面',
                    'from' => '保时捷',
                    'image_url' => 'http://img.bibicar.cn/logo.png',
                    'created_at' => date('y/m/d',time()),
                    'related_id' => "5b123cfba370d",
                ]
            ])
            ->androidNotification('车辆推送')
            ->message('保时捷车辆推送', [
                'title' => '车辆推送',
                'content_type' => 'text',
                'extras' => [
                    'type' => '1',
                    'title' => '保时捷上新',
                    'content' => '感谢你的关注，保时捷新款车上新,请点击此推送框，进入车辆详情页面',
                    'from' => '保时捷',
                    'image_url' => 'http://img.bibicar.cn/logo.png',
                    'created_at' => date('y/m/d',time()),
                    'related_id' => "5b123cfba370d",
                ]
            ])
            ->send();

        print_r($response);exit;
    }


    public function push2Action(){

        $app_key="68b7d81675c93b86ec6a11ac";
        $master_secret="81072e86bacd7dd98d4cd310";

        $client = new JPush($app_key, $master_secret);

        $cid =uniqid();

        $regId = array('171976fa8ad3f8fbecd');

        $response = $client->push()
            // ->setCid($cid)
            ->setPlatform(['ios', 'android'])
            ->addRegistrationId($regId)
            ->setNotificationAlert('订单推送')
            ->iosNotification('订单状态推送', [
                'sound' => 'sound',
                'badge' => '+1',
                'extras' => [
                    'type' => '1',
                    'title' => '订单已经更新',
                    'content' => '你的订单车辆已打包装箱',
                    'from' => '吡吡',
                    'image_url' => 'http://img.bibicar.cn/logo.png',
                    'created_at' => date('y/m/d',time()),
                    'related_id' => "27",
                ]
            ])
            ->androidNotification('订单推送')
            ->message('订单状态推送', [
                'title' => '订单推送',
                'content_type' => 'text',
                'extras' => [
                    'type' => '1',
                    'title' => '订单已经更新',
                    'content' => '你的订单车辆已打包装箱',
                    'from' => '保时捷',
                    'image_url' => 'http://img.bibicar.cn/logo.png',
                    'created_at' => date('y/m/d',time()),
                    'related_id' => "27",
                ]
            ])
            ->send();

        print_r($response);exit;
    }











}
