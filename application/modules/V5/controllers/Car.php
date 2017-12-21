<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/13
 * Time: 下午6:09
 */
use EasyWeChat\Foundation\Application;
use EasyWeChat\Payment\Order;
class CarController extends ApiYafControllerAbstract
{

    /**
     * @api {POST} /v5/car/index 车辆详情
     * @apiName car detail
     * @apiGroup Car
     * @apiDescription 车辆详情
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.5.3
     *
     * @apiParam (request) {string} [device_identifier] 设备唯一标识
     * @apiParam (request) {string} [session_id] session_id
     * @apiParam (request) {string} [car_id] 车辆car_id
     *
     * @apiParam (response) {object} car_info_ids
     *
     *
     *
     */
    public function  indexAction()
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

        $carModel = new CarSellingV5Model();

        $carT = $carModel::$table;

        if(!$data['car_id']){
            return $this->send_error(CAR_NOT_EXIST);
        }

        $carId = $data['car_id'];

        $carModel->currentUser = $userId;

        $carInfo = $carModel->GetCarInfoById($carId,$userId);

        if(!$carInfo){
            return $this->send_error(CAR_NOT_EXIST);
        }

        $response['car_info'] = $carInfo;


        $brandId = isset($carInfo['brand_info']['brand_id']) ? $carInfo['brand_info']['brand_id'] : 0;


        if(!is_object($carInfo['user_info'])){
            $car_userId = $carInfo['user_info']['user_id'];
        }else{
            $car_userId = 0;
        }
        //$car_userId = $carInfo['user_info'] ? $carInfo['user_info']['user_id']: 0;
        //同款车
        // $response['related_price_car_list'] = $carModel->relatedPriceCars($carId,$carInfo['price']);
        $response['related_price_car_list'] = $carModel->relatedPriceCarsTest($carId,$carInfo['price'],$car_userId);
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
        $response['share_url'] = 'http://share.bibicar.cn/views/detail/car.html?ident='.$data['device_identifier'].'&session='.$data['session_id'].'&id='.$carId;

        $response['share_txt'] = '更多精选二手车在bibi car,欢迎您来选购!';
        $response['share_img'] = isset($carInfo['files']["type1"]) ? @$carInfo['files']["type1"][0]['file_url'] : '';

        $this->send($response);

    }


    /**
     * @api {POST} /v5/car/list  车辆列表
     * @apiName car  list
     * @apiGroup Car
     * @apiDescription 车辆列表
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.5.3
     *
     * @apiParam {string} device_identifier]设备唯一标识
     * @apiParam {string} [session_id] session_id
     * @apiParam {number} order_id 排序Id 0:默认排序1:最新发布 2:价格最低、3价格最高
     * @apiParam {number} brand_id 车品牌Id
     * @apiParam {number} series_id 车系列Id
     * @apiParam {number} page 页数
     * @apiParam {number} search_type 类型 1：获取总数 2：获取列表
     * @apiParam {string} [min_price] 最低价格
     * @apiParam {string} [max_price] 最高价格
     * @apiParam {string} [min_mileage] 最低里程
     * @apiParam {string} [max_mileage] 最高里程
     * @apiParam {string} [min_board_time] 最短上牌时间
     * @apiParam {string} [max_board_time] 最长上牌时间
     * @apiParam {string} [min_forfloat] 最小排量
     * @apiParam {string} [max_forfloat] 最大排量
     * @apiParam {number} [has_vr] 是否vr 1:是
     * @apiParam {number} [car_type] 是否新车二手车 1:新车  2: 二手车 
     * @apiParam {number} [car_source] 车辆来源(个人，商家) 1:个人 2 商家
     * @apiParam {number} [forward] 变速箱  1:手动 2:自动
     * @apiParam {number} [board_add] 车牌所在地  1：本地 2：外地 (选择车牌所在地必须传city_code)
     * @apiParam {number} [car_level] 车辆级别 (ids传值 1,2,3) 6:小轿车 7:MPV 8:SUV 9:跑车 11:皮卡 13:敞篷跑车
     * @apiParam {number} [car_color] 颜色 (ids传值 1,2,3) 0:未知 1:黑色 2:红色 3:深灰色 4:粉红色 5:银灰色 6:紫色 7:白色 8:蓝色 9:香槟色 10:绿色11:黄色12:咖啡色13:橙色 14:多彩色
     * @apiParam {number} [seat_num] 座位数 (ids传值 2,3,4,5)
     * @apiParam {number} [envirstandard] 环保标准 (ids传值 1,2,3) 1:国1 2:国2 3:国3 4:国4
     * @apiParam {number} [fueltype] 燃油类型 (ids传值 1,2,3) 1:汽油、2:柴油、3:油电混合动力 4:电动
     * @apiParam {string} [extra_info] 亮点配置  (ids传值 1,2,3)
     * @apiParam {string} [city_code] 高德地图城市编码  (选择车牌所在地必须传city_code)
     * @apiParam {string} [city_lat] 高德地图城市纬度
     * @apiParam {string} [city_lng] 高德城市经度
     *
     */
    public function listAction(){
        $jsonData = require APPPATH .'/configs/JsonData.php';
        $this->optional_fields = array('order_id','brand_id','series_id');
        // $this->required_fields = array_merge($this->required_fields, array('session_id'));
        $data = $this->get_request_data();

        $data['order_id'] = $data['order_id'] ? $data['order_id'] : 0 ;
        $data['page']     = $data['page'] ? ($data['page']+1) : 1;
        $data['brand_id'] = $data['brand_id'] ? $data['brand_id'] : 0 ;
        $data['series_id'] = $data['series_id'] ? $data['series_id'] : 0 ;
        $data['search_type'] = @$data['search_type'] ? $data['search_type'] : 1 ;

        $carM = new CarSellingV5Model();

        $where = 'WHERE t1.files <> "" AND t1.brand_id <> 0 AND t1.series_id <> 0 AND t1.car_type <> 3 AND t1.car_type <> 4 AND (t1.verify_status = 2 OR t1.verify_status = 11 OR t1.verify_status = 4) ';

        //品牌
        if(@$data['brand_id']){

            $where .= ' AND t1.brand_id = '.$data['brand_id'].' ';
        }
        //系列
        if(@$data['series_id']){
            $where .= ' AND t1.series_id = '.$data['series_id'].' ';
        }
        //是否新车二手车
        if(@$data['car_type']){
            if($data['car_type'] == 1){
                   $where .= ' AND t1.car_type = 0  ';
            }else{
                  $where .= ' AND t1.car_type = 1  ';
            }
        }
        //车源
        if(@$data['car_source']){
            $where .= ' AND t1.car_source = '.$data['car_source'].' ';
        }
        //车级别
        if(@$data['car_level']) {
            $where .= ' AND t1.car_level in ( ' . $data['car_level'] . ')  ';

        }
        //车颜色
        if(@$data['car_color']) {
            $where .= ' AND t1.car_color in (' . $data['car_color'] . ') ';
        }
        //环保标准
        if(@$data['envirstandard']) {
            $where .= ' AND t5.Engine_EnvirStandard_type in (' . $data['envirstandard'] . ') ';
            if(!$carM->left_model){
                $carM->left_model = 'LEFT JOIN `bibi_car_model_detail` AS t5 ON t1.model_id = t5.model_id ';
            }
        }
        //座位数
        if(@$data['seat_num']) {
            $where .= ' AND t5.Perf_SeatNum in (' . $data['seat_num'] . ') ';
            if(!$carM->left_model){
                $carM->left_model = 'LEFT JOIN `bibi_car_model_detail` AS t5 ON t1.model_id = t5.model_id ';
            }
        }
        //变速箱
        if(@$data['forward']) {
            $where .= ' AND t5.UnderPan_ForwardGearNum_type ='.$data['forward'].' ';
            if(!$carM->left_model){
                $carM->left_model = 'LEFT JOIN `bibi_car_model_detail` AS t5 ON t1.model_id = t5.model_id ';
            }
        }
        //燃油类别
        if(@$data['fueltype']) {
            $where .= ' AND t5.Oil_FuelType_type in ('.$data['fueltype'] .') ';
            if(!$carM->left_model){
                $carM->left_model = 'LEFT JOIN `bibi_car_model_detail` AS t5 ON t1.model_id = t5.model_id ';
            }
        }
        //车牌所在地
        if(@$data['board_add'] && $data['city_code']){

            if( $data['board_add'] == 1 ){

                $where .= ' AND t1.city_id = '.$data['city_code'].' ';

            }else{

                $where .= ' AND t1.city_id <> '.$data['city_code'].' ';
            }
        }
        //基本配置
        if(@$data['extra_info']){
            $ExtraModel = new CarSellingExtraInfoModel();
            $info = $ExtraModel->getExtraInfoByIds($data['extra_info']);
            $item ='';
            foreach($info as $k){
                   $str = $k['alias'];
                   $item .=" AND t6.".$str." = 1 ";
            }
            $where .= $item;
            if(!$carM->left_extra && $item){
                $carM->left_extra = 'LEFT JOIN `bibi_car_selling_list_info` AS t6 ON t1.id = t6.car_id ';
            }
        }
        //排量
        if(@$data['min_forfloat'] == 5){
            $where .= ' AND t5.Engine_ExhaustForFloat >= '.$data['min_forfloat'] .' ';
            if(!$carM->left_model){
                $carM->left_model = 'LEFT JOIN `bibi_car_model_detail` AS t5 ON t1.model_id = t5.model_id ';
            }
        }else{
            if(@$data['min_forfloat']){
                $where .= ' AND t5.Engine_ExhaustForFloat >= '.$data['min_forfloat'] .' ';

                if(!$carM->left_model){
                    $carM->left_model = 'LEFT JOIN `bibi_car_model_detail` AS t5 ON t1.model_id = t5.model_id ';
                }
            }
            if(@$data['max_forfloat']){
                $where .=' AND t5.Engine_ExhaustForFloat <='.$data['max_forfloat'].' ';

                if(!$carM->left_model){
                    $carM->left_model = 'LEFT JOIN `bibi_car_model_detail` AS t5 ON t1.model_id = t5.model_id ';
                }
            }

        }
        //价格
        if(@$data['min_price']==200){
            $where .=' AND t1.price >='.$data['min_price'].' ';
        }else{

            if(@$data['min_price']){
                $where .=' AND t1.price >='.$data['min_price'].' ';
            }
            if(@$data['max_price']){
                $where .=' AND t1.price <='.$data['max_price'].' ';
            }
        }
        //里程数
        if(@$data['min_mileage']==15){
            $min_mileage=$data['min_mileage']*10000;
            $where .=' AND t1.mileage >='.$min_mileage.' ';
        }else{
            if(@$data['min_mileage']){
                $min_mileage=$data['min_mileage']*10000;
                $where .=' AND t1.mileage >='.$min_mileage.' ';
            }
            if(@$data['max_mileage']){
                $max_mileage=$data['max_mileage']*10000;
                $where .=' AND t1.mileage <='.$max_mileage.' ';
            }
        }
        //年龄
        $year=date("Y");
        if(@$data['min_board_time']==10){
            $min=$year-$data['min_board_time'];
            $where .=' AND t1.board_time <='.$min.' ';
        }else{
            if(@$data['min_board_time']){
                $max=$year-$data['min_board_time'];
                $where .=' AND t1.board_time <='.$max.' ';
            }
            if(@$data['max_board_time']){
                $min=$year-$data['max_board_time'];
                $where .=' AND t1.board_time >='.$min.' ';
            }
        }
        if(@$data['has_vr'] == 1){
            $where .= 'AND t1.vr_url is not null';
        }
        if(isset($jsonData['new_order_info'][$data['order_id']])) {

            // $carM->order  = ' ORDER BY t1.car_type ASC , ';
            $carM->order = $jsonData['new_order_info'][$data['order_id']];

        }

        $sess = new SessionModel();
        $userId = $sess->Get($data);

        $carM->currentUser = $userId;

        $carM->where = $where;

        $carM->page = $data['page'];

        if($data['search_type'] == 1 ){

            $lists = $carM->getCarListTotal($userId);

            $response = $lists;

            $response['order_id'] = $data['order_id'];
            if(@$data['city_id']){
                $jsonData['city_info']['city_id'] = $data['city_id'];
                $jsonData['city_info']['city_lat'] = $data['city_lat'];
                $jsonData['city_info']['city_lng'] = $data['city_lng'];
            }
            $response['city_info'] = $jsonData['city_info'];
            $response['keyword']   = @$data['keyword'];

            $response['custom_url'] = "http://custom.bibicar.cn/customize";

            $this->send($response);

        }else{

            $lists = $carM->getCarNewList($userId);
            $response = $lists;
            $response['order_id'] = $data['order_id'];
            if(@$data['city_id']){
                $jsonData['city_info']['city_id'] = $data['city_id'];
                $jsonData['city_info']['city_lat'] = $data['city_lat'];
                $jsonData['city_info']['city_lng'] = $data['city_lng'];

            }
            $response['city_info'] = $jsonData['city_info'];
            $response['keyword']   = @$data['keyword'];


            $response['custom_url'] = "http://custom.bibicar.cn/customize";

            $this->send($response);
        }


    }

    public function explode_str($str){

           $attr = explode(',',$str);

           return $attr;

    }


    /**
     * @api {POST} /v5/car/userFavCars 用户爱车
     * @apiName user favcars
     * @apiGroup Car
     * @apiDescription 用户爱车
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.5.3
     *
     * @apiParam {string} device_identifier 设备唯一标识
     * @apiParam {string} session_id session_id
     * @apiParam {number} user_id user_id
     * @apiParam {number} page 页码
     *
     *
     * @apiParamExample {json} 请求样例
     *   POST /v5/car/userFavCars
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *
     *     }
     *   }
     *
     */

    public function userFavCarsAction(){

        $this->required_fields = array_merge($this->required_fields, array('session_id','page'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $objId = $this->getAccessId($data,$userId);

        $car = new CarSellingV5Model();

        $car->currentUser = $userId;

        $car->car_type = 3;

        $car->page = $data['page'] ? $data['page'] + 1 : 1;

        $response['list'] = $car->getUserCars($objId);

        $this->send($response);
    }
    /**
     * @api {POST} /v5/car/carvisithistory 车辆浏览历史
     * @apiName car carvisithistory
     * @apiGroup Car
     * @apiDescription 车辆列表
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.5.3
     *
     * @apiParam {string} device_identifier]设备唯一标识
     * @apiParam {string} session_id session_id
     *
     *
     */
    public function carvisithistoryAction(){

           $this->required_fields = array_merge($this->required_fields, array('session_id','page'));

           $data = $this->get_request_data();

           $userId = $this->userAuth($data);

           $page = $data['page'] ? ($data['page']+1) : 1;

           $carM = new CarSellingV5Model();

           $carM->page = $page;

           $car_list = $carM->getUserVisitCars($userId);

           $this->send($car_list);
    }
    /**
     * @api {POST} /v5/car/search 车辆搜索
     * @apiName car search
     * @apiGroup Car
     * @apiDescription 车辆搜索
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.5.3
     *
     * @apiParam {string} device_identifier]设备唯一标识
     * @apiParam {string} session_id session_id
     * @apiParam {string} keyword 关键词
     *
     * @apiParamExample {json} 请求样例
     *    POST /v5/car/search
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "keyword":"",
     *
     *     }
     *   }
     *
     */
    public function SearchAction(){

        $this->required_fields = array_merge($this->required_fields, array('keyword','page'));

        $data = $this->get_request_data();

        $data['page']     = $data['page'] ? ($data['page']+1) : 1;

        $number = ($data['page']-1)*10;

        $carM = new CarSellingV5Model();

        $results = $this->searchcar($data['keyword'], $number);

        if($results['hits']['hits']){

            $inStr = $this->implodeArrayByKey('_id',$results['hits']['hits']);

            $where = '';

            $where .= ' where t1.id in (' . $inStr . ')'; //ORDER BY t3.comment_id DESC

            $carM = new CarSellingV5Model();

            $carM->where = $where;

            $list = $carM->getCarlistByIds();

        }else{

            $list=array();
        }
        $total=$results['hits']['total'];

        $count = count($results['hits']['hits']);

        $lists['car_list']=$list;
        $lists['has_more'] = (($number+$count) < $total) ? 1 : 2;
        $lists['total'] = $total;
        $lists['number'] = $number;

        $lists['custom_url'] = "http://custom.bibicar.cn/customize";


        return $this->send($lists);
    }

    public function searchseriesAction(){

        $this->required_fields = array_merge($this->required_fields, array('keyword','page'));

        $data = $this->get_request_data();

        $data['page']     = $data['page'] ? ($data['page']+1) : 1;

        $number = ($data['page']-1)*10;

        $results = $this->searchseries($data['keyword'], $number);


        if($results['hits']['hits']){

              $list=$results['hits']['hits'];
              $items =array();
             foreach($list as $k =>$val){
                  $items[$k]['brand_series_name'] =$val['_source']['brand_series_name'];
                  $items[$k]['brand_series_id']=$val['_source']['brand_series_id'];
                  $items[$k]['makename']=$val['_source']['makename'];
                  $items[$k]['brand_id']=$val['_source']['brand_id'];
                  $items[$k]['brand_name']=$val['_source']['brand_name'];
             }
        }else{
            $items=array();
        }

        return $this->send($items);
    }

    public function testInsertAction(){


          $MyfocusM = new MyFocusModel();


    }

    public function searchcar($keyword,$number=0){

        $client=new Elasticsearch;

        $client=$client->instance();

        $params = [
            'index' => 'car',
            'type' => 'car_selling_list',
            'body' => [
                'query' => [
                    'match' => [
                        'car_name' => $keyword,
                    ]
                ],
                'highlight' =>[
                    "pre_tags" => ["<b>"],
                    "post_tags" => ["</b>"],
                    "fields" => [
                        "car_name" => new \stdClass()
                    ]
                ]
            ]
        ];
        $params['size'] = 10;
        $params['from'] =$number;
        $results = $client->search($params);

        //print_r($results);exit;
        return $results;

    }

    public function searchseries($keyword,$number=0){

        $client=new Elasticsearch;
        $client=$client->instance();
        $params = [
            'index' => 'car',
            'type' => 'car_brand_series',
            'body' => [
                'query' => [
                    'match' => [
                        'brand_series_name' => $keyword,
                    ]
                ],
                'highlight' =>[
                    "pre_tags" => ["<b>"],
                    "post_tags" => ["</b>"],
                    "fields" => [
                        "brand_series_name" => new \stdClass()
                    ]
                ]
            ]
        ];
        $params['size'] =10;
        $params['from'] = 0;
        $results = $client->search($params);
        return $results;

    }

    public function implodeArrayByKey($key, $result,$string=','){


        $values = array();

        foreach($result as $k => $rs){

            $values[] = $rs[$key];

        }

        $values = implode($string , $values);

        return $values ? $values : 0;
    }


    public function testAction(){

           $ExtraModel = new CarSellingExtraInfoModel();
           $hash = '57837734900f4';
           $car_id = 123;
           $ids = '2,6,8,16,18,19,26,25,30,29,34,33,';
           $res = $ExtraModel->addExtrainfo($car_id,$hash,$ids);

         // $ExtraModel->getInfo(123);
           print_r($res);exit;
    }

    public function createtestAction(){

        $ExtraModel = new CarSellingExtraInfoModel();
        $insert['hash']='134567';
        $insert['ids']='12,13,14,1,2,3,4';
        $id = $ExtraModel->insert('bibi_car_selling_list_extra_info',$insert);

    }




}
