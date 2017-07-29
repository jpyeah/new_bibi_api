<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/12
 * Time: 下午7:00
 */
class CarSellingV1Model extends PdoDb
{

    //public static $table = 'bibi_car_selling_list';
    public $brand_info;
    public static $visit_user_id = 0;


    public function __construct()
    {

        parent::__construct();
        self::$table = 'bibi_car_selling_list';
    }

    public function GetCarInfoById($hash,$userId=0)
    {


        $sql = '
            SELECT
            t1.*,
            t3.avatar,t3.nickname,t3.type
            FROM `' . self::$table . '`
            AS t1
            LEFT JOIN `bibi_user` AS t2
            ON t1.user_id = t2.user_id
            LEFT JOIN `bibi_user_profile` AS t3
            ON t2.user_id = t3.user_id
            WHERE t1.hash = "' . $hash . '"
        ';


        $car = @$this->query($sql)[0];

        if (!$car) {

            return array();
        }

        $car = $this->handlerCarByOne($car,$userId);

        return $car;

    }

    public function GetCarBrandInfoById($hash,$userId=0)
    {


        $sql = '
            SELECT
            brand_id
            FROM `' . self::$table . '`
            WHERE hash = "' . $hash . '"
        ';
        $car = @$this->query($sql)[0];

        if (!$car) {

            return array();
        }

        $brandM = new BrandModel();
        $list=array();
        $list['brand_info']  = $brandM->getBrandModel($car['brand_id']);

        return $list;

    }

    public function handlerCar($car,$userId=0){

        $car['car_id'] = $car['hash'];

//        $brandM = new BrandModel();
//
//        $car['brand_info']  = $brandM->getBrandModel($car['brand_id']);
//        $car['series_info'] = $brandM->getSeriesModel($car['brand_id'],$car['series_id']);
//        $car['model_info']  = $brandM->getModelModel($car['series_id'], $car['model_id']);
//        $car['model_detail']= $brandM->getModelDetail($car['model_id']);

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

        unset($car['avatar']);
        unset($car['user_id']);
        unset($car['nickname']);

        unset($car['car_no']);
        unset($car['vin_no']);
        unset($car['engine_no']);
        unset($car['vin_file']);
        unset($car['car_intro']);
        unset($car['verify_status']);
       // unset($car['price']);
        unset($car['guide_price']);
        unset($car['board_time']);
        unset($car['mileage']);
        unset($car['displacement']);
        unset($car['gearbox']);
        unset($car['style']);
        unset($car['contact_name']);
        unset($car['contact_phone']);
        unset($car['contact_address']);
        unset($car['exchange_time']);
        unset($car['maintain']);
        unset($car['insurance_due_time']);
        unset($car['is_transfer']);
        unset($car['check_expiration_time']);


        $images = unserialize($car['files']);
        $items = array();

        if($images){

            foreach ($images as $k => $image) {

                if ($image['hash']) {

                        if($k == 1){
                        $item = array();
                        $item['file_id'] = $image['hash'];
                        if($car['car_type']==2){
                            $item['file_url'] = "http://thirtimg.bibicar.cn/". $image['key']."?imageMogr2/auto-orient/thumbnail/1000x/strip";
                        }else{
                            $item['file_url'] = IMAGE_DOMAIN . $image['key']."?imageMogr2/auto-orient/thumbnail/1000x/strip";
                        }

                        $item['file_type'] = $image['type'] ? $image['type'] : 0;
                        $items[] = $item;

                        break;

                    }

                }

            }
        }

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

        //print_r($car);exit;
        $favCarM = new FavoriteCarModel();
        $favCarM->user_id = self::$visit_user_id;
        $favCarM->car_id  = $car['car_id'];
        $favId = $favCarM->get();

        $car['is_fav'] = $favId ? 1 : 2;
        $car['car_time'] = Common::getBeforeTime($car['created']);
        //$car['visit_num'] = $car['visit_num'];

        return $car;

    }


    public function handlerCarByOne($car,$userId=0){

        $car['car_id'] = $car['hash'];

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


        if($car['user_id']){

            $car['user_info'] = array();
            $car['user_info']['user_id']  = $car['user_id'];
            unset($car['user_id']);
            $car['user_info']['username'] = '';
            $car['user_info']['mobile']   = '';
            $car['user_info']['created']  = 0;
            //$car['user_info']['is_auth']  = 1;
            $car['user_info']['profile']['avatar']  = $car['avatar'];
            unset($car['avatar']);
            $car['user_info']['profile']['nickname']  = $car['nickname'];
            unset($car['nickname']);
            $car['user_info']['profile']['signature']  = '';
            $car['user_info']['profile']['age']  = 0;
            $car['user_info']['profile']['constellation']  = '';
            $car['user_info']['profile']['gender']  = 0;
        }
        else{

            $car['user_info'] = new stdClass();
        }

        unset($car['car_no']);
        unset($car['vin_no']);
        unset($car['engine_no']);
        unset($car['vin_file']);
        unset($car['car_intro']);
        unset($car['verify_status']);
        // unset($car['price']);
        unset($car['guide_price']);
        unset($car['board_time']);
        unset($car['mileage']);
        unset($car['displacement']);
        unset($car['gearbox']);
        unset($car['style']);
        unset($car['contact_name']);
        unset($car['contact_phone']);
        unset($car['contact_address']);
        unset($car['exchange_time']);
        unset($car['maintain']);
        unset($car['insurance_due_time']);
        unset($car['is_transfer']);
        unset($car['check_expiration_time']);

        $images = unserialize($car['files']);
        $items = array();

        $items1=array();
        $items2=array();
        $items3=array();
        $items4=array();

        if($images){

            foreach ($images as $k => $image) {

                if ($image['hash']) {

                    switch ($image['type']) {
                        case 1:
                        case 5:
                        case 6:
                        case 9:
                        case 13:
                        case 15:
                        case 16:
                            $item = array();
                            $item['file_id'] = $image['hash'];
                            if($car['car_type']==2){
                                $item['file_url'] = "http://thirtimg.bibicar.cn/". $image['key']."?imageMogr2/auto-orient/thumbnail/1000x/strip";
                            }else{
                                $item['file_url'] = IMAGE_DOMAIN . $image['key']."?imageMogr2/auto-orient/thumbnail/1000x/strip";
                            }

                            $item['file_type'] =  $image['type'] ? $image['type'] : 0;
                            $items1[] = $item;
                            break;
                        case 2:
                        case 7:
                        case 8:
                        case 10:
                        case 12:
                        case 14:
                            $item = array();
                            $item['file_id'] = $image['hash'];
                            if($car['car_type']==2){
                                $item['file_url'] = "http://thirtimg.bibicar.cn/". $image['key']."?imageMogr2/auto-orient/thumbnail/1000x/strip";
                            }else{
                                $item['file_url'] = IMAGE_DOMAIN . $image['key']."?imageMogr2/auto-orient/thumbnail/1000x/strip";
                            }

                            $item['file_type'] =  $image['type'] ? $image['type'] : 0;
                            $items2[] = $item;

                            break;

                        case 3:
                            $item = array();
                            $item['file_id'] = $image['hash'];
                            if($car['car_type']==2){
                                $item['file_url'] = "http://thirtimg.bibicar.cn/". $image['key']."?imageMogr2/auto-orient/thumbnail/1000x/strip";
                            }else{
                                $item['file_url'] = IMAGE_DOMAIN . $image['key']."?imageMogr2/auto-orient/thumbnail/1000x/strip";
                            }

                            $item['file_type'] =  $image['type'] ? $image['type'] : 0;
                            $items3[] = $item;
                            break;

                        case 4:
                            $item = array();
                            $item['file_id'] = $image['hash'];
                            if($car['car_type']==2){
                                $item['file_url'] = "http://thirtimg.bibicar.cn/". $image['key']."?imageMogr2/auto-orient/thumbnail/1000x/strip";
                            }else{
                                $item['file_url'] = IMAGE_DOMAIN . $image['key']."?imageMogr2/auto-orient/thumbnail/1000x/strip";
                            }

                            $item['file_type'] =  $image['type'] ? $image['type'] : 0;
                            $items4[] = $item;
                            break;
                        default:
                            break;
                    }

                }

            }
        }
        $car['files2']['type1'] = $items1;
        $car['files2']['type2'] = $items2;
        $car['files2']['type3'] = $items3;
        $car['files2']['type4'] = $items4;

//        if($images){
//
//            foreach ($images as $k => $image) {
//
//                if ($image['hash']) {
//
//                        $item = array();
//                        $item['file_id'] = $image['hash'];
//                        if($car['car_type']==2){
//                            $item['file_url'] = "http://thirtimg.bibicar.cn/". $image['key']."?imageMogr2/auto-orient/thumbnail/1000x/strip";
//                        }else{
//                            $item['file_url'] = IMAGE_DOMAIN . $image['key']."?imageMogr2/auto-orient/thumbnail/1000x/strip";
//                        }
//
//                        $item['file_type'] = $image['type'] ? $image['type'] : 0;
//                        $items[] = $item;
//
//                }
//
//            }
//        }

        unset($car['id']);
        unset($car['hash']);


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

        return $car;

    }



    public function dealFilesWithString($files_id, $files_type)
    {

        $filesInfo = array();

        $files = json_decode($files_id, true);
        $files_type = json_decode($files_type, true);

        if($files && $files_type){

            foreach ($files as $k => $fileHash) {

                $filesInfo[] = array('hash' => $fileHash, 'type' => $files_type[$k], 'key' => $fileHash);

            }
        }

        return $filesInfo;

    }


    public function getCarList($userId = 0)
    {

        $pageSize = 10;

        $sql = '
                SELECT
                t1.*,
                t3.avatar,t3.nickname,t3.type
                FROM `bibi_car_selling_list` AS t1
                LEFT JOIN `bibi_user` AS t2
                ON t1.user_id = t2.user_id
                LEFT JOIN `bibi_user_profile` AS t3
                ON t2.user_id = t3.user_id
                ';

        $sqlCnt = '
                SELECT
                count(*) AS total
                FROM `bibi_car_selling_list` AS t1
                LEFT JOIN `bibi_user` AS t2
                ON t1.user_id = t2.user_id
                LEFT JOIN `bibi_user_profile` AS t3
                ON t2.user_id = t3.user_id
                ';

        $sql .= $this->where;
        $sql .= $this->order;

        $number = ($this->page-1)*$pageSize;

        $sql .= ' LIMIT '.$number.' , '.$pageSize.' ';


        $cars = $this->query($sql);

        //$carM = new CarSellingModel();

        $items = array();

        foreach($cars as $k => $car){

            $brand_id = $car['brand_id'];
            $item = $this->handlerCar($car,$userId);

            $items[$k]['car_info'] = $item;
       //     $items[$k]['car_users'] = $this->getSameBrandUsers($brand_id);

        }

        $sqlCnt .= $this->where;
        $sqlCnt .= $this->order;


        $total = @$this->query($sqlCnt)[0]['total'];

        $count = count($items);

        $list['car_list'] = $items;
        $list['has_more'] = (($number+$count) < $total) ? 1 : 2;
        $list['total'] = $total;
        $list['number'] = $number;

        return $list;
    }

    public function getSameBrandUsers($brand_id){

//        $userInfos = unserialize(RedisDb::getValue('test_car_users'));
//
//        return $userInfos ? $userInfos : array();

        $jsonData = require APPPATH .'/configs/JsonData.php';

        $sql = '
                SELECT
	              t2.user_id,
  	              t2.nickname,
	              t2.avatar
                FROM
	              `bibi_car_selling_list` AS t1
                INNER JOIN `bibi_user_profile` AS t2
                ON t1.user_id = t2.user_id
                WHERE
	            t1.`car_type` = 3 AND t1.brand_id = '.$brand_id.'
	            LIMIT 0, 10
	            ';

        $data = $this->query($sql);

        foreach($data as $k => $d){

            $userData = $jsonData['user_info'];
            $userData['user_id'] = $d['user_id'];
            $userData['profile']['nickname'] = $d['nickname'];
            $userData['profile']['avatar']   = $d['avatar'];

            $items[] = $userData;

        }

        return $items;
    }


    public function relatedPriceCars($carId , $price){

        $minPrice = $price * 0.7;
        $maxPrice = $price * 1.3;


        $sql = '
                SELECT
                t1.*,
                t3.avatar,t3.nickname
                FROM `bibi_car_selling_list` AS t1
                LEFT JOIN `bibi_user` AS t2
                ON t1.user_id = t2.user_id
                LEFT JOIN `bibi_user_profile` AS t3
                ON t2.user_id = t3.user_id
                WHERE
                 t1.files <> "" AND t1.car_type != 3 AND t1.hash != "'.$carId.'" AND
                 t1.brand_id > 0 AND t1.series_id > 0 AND
                t1.price BETWEEN '.$minPrice.' AND '.$maxPrice.'
				ORDER BY t1.car_type ASC, t1.price ASC
                LIMIT 0 , 20
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



    public function relatedStyleCars($carId ,$brand_id, $series_id){


        $sql = '
                SELECT
                t1.*,
                t3.avatar,t3.nickname
                FROM `bibi_car_selling_list` AS t1
                LEFT JOIN `bibi_user` AS t2
                ON t1.user_id = t2.user_id
                LEFT JOIN `bibi_user_profile` AS t3
                ON t2.user_id = t3.user_id
                WHERE
                t1.files <> "" AND t1.car_type != 3  AND t1.hash != "'.$carId.'" AND
                t1.brand_id = '.$brand_id.' AND t1.series_id = '.$series_id.'
                ORDER BY t1.car_type ASC, t1.price ASC
                LIMIT 0 , 20
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


    public function getTotal()
    {

        $sql = '
            SELECT
            COUNT(*) AS total
            FROM `' . self::$table . '`';

        $total = $this->query($sql)[0];

        return $total;

    }

    public function updataPactByKey($car_id , $data){

        $where = array('hash' => $car_id);

        $result = $this->updateByPrimaryKey(self::$table, $where, $data);
        return $result;
    }







}