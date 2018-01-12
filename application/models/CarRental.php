<?php

/**
 * Created by PhpStorm.
 * User: jpjy
 * Date: 17/07/03
 * Time: 下午7:00
 */
class CarRentalModel extends PdoDb
{

    public function __construct()
    {

        parent::__construct();
        self::$table = 'bibi_car_selling_rental_list';
    }

    public function getRentalCarList($userId = 0)
    {
        $pageSize = 10;

        $number = ($this->page-1)*$pageSize;

        $sql = '
                SELECT
                t1.*,
                t4.*
                FROM `bibi_car_selling_rental_list` AS t1
                LEFT JOIN `bibi_car_selling_list` AS t4
                ON t1.hash = t4.hash
                ';

        $sqlCnt = '
                SELECT
                count(*) AS total
                FROM `bibi_car_selling_rental_list` AS t1
                LEFT JOIN `bibi_car_selling_list` AS t4
                ON t1.hash = t4.hash
                ';

        //$sql .= ' WHERE t1.status= 1'; //ORDER BY t3.comment_id DESC;
       //$sqlCnt .= ' WHERE t1.status= 1'; //ORDER BY t3.comment_id DESC;

        $number = ($this->page-1)*$pageSize;

        $sql .= ' LIMIT '.$number.' , '.$pageSize.' ';

        $cars = $this->query($sql);

        $total = @$this->query($sqlCnt)[0]['total'];

        $items = array();

        foreach($cars as $k => $car){

            $brand_id = $car['brand_id'];
            $item = $this->handlerCar($car,$userId);

            $items[$k]['car_info'] = $item;
        }

        $count = count($items);

        $list['car_list'] = $items;
        $list['has_more'] = (($number+$count) < $total) ? 1 : 2;
        $list['total'] = $total;
        //$list['number'] = $number;

        return $list;
    }


    public function handlerCar($car,$userId=0){


        $brandM = new BrandModel();

        $car['brand_info']  = $brandM->getBrandModel($car['brand_id']);
        $car['series_info'] = $brandM->getSeriesModel($car['brand_id'],$car['series_id']);
        $car['model_info']  = $brandM->getModelModel($car['series_id'], $car['model_id']);
        $car['model_detail']= $brandM->getModelDetail($car['model_id']);

        unset($car['brand_id']);
        unset($car['series_id']);
        unset($car['model_id']);
        unset($car['brand_name']);
        unset($car['series_name']);
        unset($car['model_name']);
        unset($car['baidu_brand_id']);
        unset($car['baidu_series_id']);
        unset($car['image']);
        unset($car['thumbnail']);

        $car['user_info'] = new stdClass();

        $images = unserialize($car['files']);
        $items = array();

        if($images){

            foreach ($images as $k => $image) {

                if ($image['hash']) {

                    $item = array();
                    $item['file_id'] = $image['hash'];
                    if($car['car_type']==2){
                        $item['file_url'] = "http://thirtimg.bibicar.cn/". $image['key']."?imageMogr2/auto-orient/thumbnail/1000x/strip";
                    }else{
                        $item['file_url'] = IMAGE_DOMAIN . $image['key']."?imageMogr2/auto-orient/thumbnail/1000x/strip";
                    }

                    $item['file_type'] = $image['type'] ? (int)$image['type'] : 0;
                    $items[] = $item;

                }

            }
        }

        $car['rental_info']=[
            'one' =>$car['one'],
            'two' =>$car['two'],
            'three'=>$car['three'],
            'four' => $car['four'],
            'pick_address'=>$car['pick_address'],
            'pick_lng'    =>$car['pick_lng'],
            'pick_lat'    =>$car['pick_lat'],
            'status'      =>$car['status'],
            'deposit'      =>$car['deposit'],
            'subscription'      =>$car['subscription'],
         ];

        if($car['status'] == 2){

            $carrentalOrder = new CarRentalOrderModel();

            $info =$carrentalOrder->getRentalOrderInfoByCarId($car['hash']);


            @$car['rental_info']['rental_time_end']=$info['rental_time_end'];

        }else{

            $car['rental_info']['rental_end_time']=0;

        }

        unset($car['one']);
        unset($car['two']);
        unset($car['three']);
        unset($car['four']);
        unset($car['pick_address']);
        unset($car['pick_lng']);
        unset($car['pick_lat']);
        unset($car['status']);
        unset($car['deposit']);
        unset($car['subscription']);

        unset($car['id']);
        $car['car_id'] = $car['hash'];
        unset($car['hash']);

        $car['city_info'] = array(
            'city_id' =>   93,//$car['city_id'],
            'city_name' => '深圳',   //$car['city_name'],
            'city_lng' => 360,
            'city_lat' => 360,
        );

        if ($car['platform_id']) {

            $car['platform_info'] = array('platform_id' => $car['platform_id'], 'platform_location' => $car['platform_location'], 'platform_name' => $car['platform_name']);
        } else {

            $car['platform_info'] = new stdClass();
        }

        $car['files'] = $items;

        unset($car['city_id']);
        unset($car['city_name']);
        unset($car['user_id']);
        unset($car['platform_id']);
        unset($car['platform_location']);
        unset($car['platform_name']);
        unset($car['platform_url']);
        unset($car['avatar']);
        unset($car['nickname']);
        unset($car['type']);

        //print_r($car);exit;
        //可优化查询


        $favkey = 'favorite_'.$userId.'_'.$car['car_id'].'';
        Common::globalLogRecord('favorite key', $favkey);
        $favId = RedisDb::getValue($favkey);


        $car['is_fav'] = $favId ? 1 : 2;
        $car['car_time'] = Common::getBeforeTimes($car['created']);
        //$car['visit_num'] = $car['visit_num'];
        //

        $likeKey='favoritecarlike_'.$car['car_id'].'_'.$userId.'';
        Common::globalLogRecord('like key', $likeKey);
        $isLike = RedisDb::getValue($likeKey);

        $car['is_like']  = $isLike ? 1 : 2;

        $favCarM = null;

        return $car;

    }


    public function GetCarInfoById($hash,$userId=0)
    {

        $sql = '
            SELECT
            t1.*,
            t2.*
            FROM `bibi_car_selling_rental_list` AS t1
            LEFT JOIN `bibi_car_selling_list` AS t2
            ON t1.hash = t2.hash
            WHERE t1.hash = "' . $hash . '" 
        ';

        $res = $this->query($sql);

        $car = $res ? $res[0] : array();

        if (!$car) {

            return new stdClass();
        }

        $car = $this->handlerCar($car,$userId);

        return $car;

    }
    //车是否可租
    public function getCarRetalStatus($hash){

        $sql = 'SELECT
                id,status,deposit
                FROM `' . self::$table . '`
                WHERE hash = "' . $hash . '" '
                ;

        $result = $this->query($sql);

        if($result){

            return $result[0];


        }else{

            return array();


        }


    }


    public function relatedRecommonCars(){


        $sql = '
                SELECT
                t1.*,
                t2.*
                FROM `bibi_car_selling_rental_list` AS t1
                LEFT JOIN `bibi_car_selling_list` AS t2
                ON t1.hash = t2.hash
                ORDER BY t1.ren_num  LIMIT 0 , 5
                ';

        $cars = $this->query($sql);

        $items = array();

        if($cars){

            foreach($cars as $k => $car){

                $item = $this->handlerCar($car);
                $items[$k] = $item;
            }
        }
        return $items;
    }

    public function updateByHash($car_id,$status){
        $sql = '
            UPDATE
            `bibi_car_selling_rental_list`
            SET
            status = '.$status.'
            WHERE
            `hash`= '."'".$car_id."'".'
            ;
        ';
        $this->exec($sql);
    }


    public function update($where, $data){

        $res =  $this->updateByPrimaryKey(self::$table, $where, $data);

        return $res;

    }



}

