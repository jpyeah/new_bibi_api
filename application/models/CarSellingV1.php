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

    public $left_model;
    public $left_series;
    public $left_extra;


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
            t3.avatar,t3.nickname,t3.type as user_type
            FROM `' . self::$table . '`
            AS t1
            LEFT JOIN `bibi_user` AS t2
            ON t1.user_id = t2.user_id
            LEFT JOIN `bibi_user_profile` AS t3
            ON t2.user_id = t3.user_id
            WHERE t1.hash = "' . $hash . '"
        ';

        $res = $this->query($sql);


        $car= $res ? $res[0] : array();

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

    public function handlerCarByOne($car,$userId=0){

        $car['car_id'] = $car['hash'];

        $brandM = new BrandModel();

        $ExtraModel = new CarSellingExtraInfoModel();
        $car['car_extra_info'] = $ExtraModel->getInfo($car['id']);

        $car['brand_info']  = $brandM->getBrandModel($car['brand_id']);
        $car['series_info'] = $brandM->getSeriesModel($car['brand_id'],$car['series_id']);
        $car['model_info']  = $brandM->getModelModel($car['series_id'], $car['model_id']);
        $car['model_detail']= $brandM->getModelDetail($car['model_id']);

//        unset($car['brand_id']);
//        unset($car['series_id']);
//        unset($car['model_id']);
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
            $car['user_info']['profile']['type']  = $car['user_type'];
            unset($car['user_type']);
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
//        unset($car['car_intro']);
//        unset($car['verify_status']);
        // unset($car['price']);
//        unset($car['guide_price']);
//        unset($car['board_time']);
//        unset($car['mileage']);
//        unset($car['displacement']);
//        unset($car['gearbox']);
//        unset($car['style']);
//        unset($car['contact_name']);
//        unset($car['contact_phone']);
//        unset($car['contact_address']);
//        unset($car['exchange_time']);
//        unset($car['maintain']);
//        unset($car['insurance_due_time']);
//        unset($car['is_transfer']);
//        unset($car['check_expiration_time']);

        $images = unserialize($car['files']);

        $car['files'] = array();
        $items1=array();
        $items2=array();
        $items3=array();
        $items4=array();
        if($images){

            foreach ($images as $k => $image) {

                if ($image['hash']) {

                    switch ($image['type']) {
                        case 0:
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
        $car['files']['type1'] = $items1;
        $car['files']['type2'] = $items2;
        $car['files']['type3'] = $items3;
        $car['files']['type4'] = $items4;

//        unset($car['id']);
        unset($car['hash']);

       // $car['files'] = $items;
//        unset($car['city_id']);
//        unset($car['city_name']);
        unset($car['user_id']);
        unset($car['platform_id']);
        unset($car['platform_location']);
        unset($car['platform_name']);
        unset($car['platform_url']);
        unset($car['avatar']);
        unset($car['nickname']);
        unset($car['type']);

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


    public function handlerCarByList($car,$userId=0){

        $car['car_id'] = $car['hash'];

        $brandM = new BrandModel();

        $car['brand_info']  = $brandM->getBrandModel($car['brand_id']);
        $car['series_info'] = $brandM->getSeriesModel($car['brand_id'],$car['series_id']);
        $car['model_info']  = $brandM->getModelModel($car['series_id'], $car['model_id']);
        $car['model_detail']= $brandM->getModelDetail($car['model_id']);

//        $car['brand_info']  = new stdClass();//$brandM->getBrandModel($car['brand_id']);
//        $car['series_info'] = new stdClass();//$brandM->getSeriesModel($car['brand_id'],$car['series_id']);
//        $car['model_info']  = new stdClass();//$brandM->getModelModel($car['series_id'], $car['model_id']);
//        $car['model_detail']= new stdClass();//$brandM->getModelDetail($car['model_id']);

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
            $car['user_info']['profile']['type']  = $car['user_type'];
            unset($car['user_type']);
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
      //  unset($car['car_intro']);
      //  unset($car['verify_status']);
        // unset($car['price']);
        unset($car['guide_price']);
//        unset($car['board_time']);
//        unset($car['mileage']);
        unset($car['displacement']);
        unset($car['gearbox']);
        unset($car['style']);
//        unset($car['contact_name']);
//        unset($car['contact_phone']);
//        unset($car['contact_address']);
        unset($car['exchange_time']);
        unset($car['maintain']);
        unset($car['insurance_due_time']);
        unset($car['is_transfer']);
        unset($car['check_expiration_time']);

        $images = unserialize($car['files']);
        $car['files'] = new stdClass();
        $items1=array();
        $items2=array();
        $items3=array();
        $items4=array();

        if($car['car_type']==2){
            $car['file_img'] = "http://thirtimg.bibicar.cn/". $images[0]['key']."?imageMogr2/auto-orient/thumbnail/1000x/strip";
        }else{
            if($images){

//                $car['file_img'] = IMAGE_DOMAIN.$images[0]['key']."?imageMogr2/auto-orient/thumbnail/1000x/strip";
//

                foreach($images as $k => $val ){
                       if($k == 0){
                           $car['file_img'] = IMAGE_DOMAIN.$val['key']."?imageMogr2/auto-orient/thumbnail/1000x/strip";
                           break;
                       }
                }
            }else{
                $car['file_img'] = "";
            }
        }

        unset($car['id']);
        unset($car['hash']);
        unset($car['files']);
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
                t3.avatar,t3.nickname,t3.type as user_type
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
            $item = $this->handlerCarByList($car,$userId);

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


    public function getCarListTotal($userId = 0)
    {


        $sqlCnt = '
                SELECT
                count(*) AS total
                FROM `bibi_car_selling_list` AS t1 
                LEFT JOIN `bibi_user_profile` AS t3
                ON t1.user_id = t3.user_id ';

        if($this->left_series){
            $sqlCnt .= $this->left_series;
        }

        if($this->left_model){
            $sqlCnt .= $this->left_model;
        }

        if($this->left_extra){
            $sqlCnt .= $this->left_extra;
        }

        $sqlCnt .= $this->where;
        $sqlCnt .= $this->order;

        $total = @$this->query($sqlCnt)[0]['total'];

        $list['car_list'] =array();
        $list['has_more'] = 1;
        $list['total'] = $total;
        $list['number'] = 0;

        return $list;
    }


    public function getCarNewList($userId = 0)
    {

        $pageSize = 10;

        $sql = '
                SELECT
                t1.*,
                t3.avatar,t3.nickname,t3.type as user_type
                FROM `bibi_car_selling_list` AS t1  
                LEFT JOIN `bibi_user_profile` AS t3
                ON t1.user_id = t3.user_id ';

        $sqlCnt = '
                SELECT
                count(*) AS total
                FROM `bibi_car_selling_list` AS t1 
                LEFT JOIN `bibi_user_profile` AS t3
                ON t1.user_id = t3.user_id ';

        if($this->left_series){
            $sql .= $this->left_series;
            $sqlCnt .= $this->left_series;
        }

        if($this->left_model){
            $sql .= $this->left_model;
            $sqlCnt .= $this->left_model;
        }

        if($this->left_extra){
            $sql .= $this->left_extra;
            $sqlCnt .= $this->left_extra;
        }

        $sql .= $this->where;
        $sql .= $this->order;

        $number = ($this->page-1)*$pageSize;

        $sql .= ' LIMIT '.$number.' , '.$pageSize.' ';

        $cars = $this->query($sql);

        $items = array();

        foreach($cars as $k => $car){

            $item = $this->handlerCarByList($car,$userId);
            $items[$k]['car_info'] =$item;

        }

        $sqlCnt .= $this->where;
        $sqlCnt .= $this->order;

        $total = @$this->query($sqlCnt)[0]['total'];

        $count = count( $items);

        $list['car_list'] =$items;
        $list['has_more'] = (($number+$count) < $total) ? 1 : 2;
        $list['total'] = $total;
        $list['number'] = $number;

        return $list;
    }

    public function getCarlistByIds($userId = 0){

        $sql = '
                SELECT
                t1.*,
                t3.avatar,t3.nickname,t3.type as user_type
                FROM `bibi_car_selling_list` AS t1
                LEFT JOIN `bibi_user` AS t2
                ON t1.user_id = t2.user_id
                LEFT JOIN `bibi_user_profile` AS t3
                ON t2.user_id = t3.user_id
                ';

        $sql .= $this->where;

        $cars = $this->query($sql);

        foreach($cars as $k => $car){
            $brand_id = $car['brand_id'];
            $item = $this->handlerCarByList($car,$userId);
            $items[$k]['car_info'] = $item;
        }
        $list = $items;
        return $list;

    }

    public function getCarlistByHashs($userId = 0){

        $sql = '
                SELECT
                t1.*,
                t3.avatar,t3.nickname,t3.type as user_type
                FROM `bibi_car_selling_list` AS t1
                LEFT JOIN `bibi_user` AS t2
                ON t1.user_id = t2.user_id
                LEFT JOIN `bibi_user_profile` AS t3
                ON t2.user_id = t3.user_id
                ';

        $sql .= $this->where;

        $cars = $this->query($sql);

        foreach($cars as $k => $car){

            $brand_id = $car['brand_id'];
            $item = $this->handlerCarByList($car,$userId);
            $items[$k]['car_info'] = $item;

        }
        $list = $items;
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
                t3.avatar,t3.nickname,t3.type as user_type
                FROM `bibi_car_selling_list` AS t1
                LEFT JOIN `bibi_user` AS t2
                ON t1.user_id = t2.user_id
                LEFT JOIN `bibi_user_profile` AS t3
                ON t2.user_id = t3.user_id
                WHERE
                 t1.files <> "" AND t1.car_type != 3 AND t1.hash != "'.$carId.'" AND
                 t1.brand_id > 0 AND t1.series_id > 0 AND
                t1.price BETWEEN '.$minPrice.' AND '.$maxPrice.' AND (t1.verify_status=2 OR t1.verify_status = 11)
				ORDER BY t1.car_type ASC, t1.price ASC
                LIMIT 0 , 20
                ';


        $cars = $this->query($sql);

        $items = array();

        if($cars){

            foreach($cars as $k => $car){

                $item = $this->handlerCarByList($car);
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

                $item = $this->handlerCarByOne($car);
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


    public function getUserCars($userId){

        $pageSize = 10;

        $number = ($this->page-1)*$pageSize;

        $sql = '
            SELECT
            t1.*,
            t3.avatar,t3.nickname,t3.type as user_type
            FROM `' . self::$table . '`
            AS t1
            LEFT JOIN `bibi_user` AS t2
            ON t1.user_id = t2.user_id
            LEFT JOIN `bibi_user_profile` AS t3
            ON t2.user_id = t3.user_id
            WHERE t1.user_id = "' . $userId . '" 
        ';

        $sqlCnt = '
            SELECT
            count(*) as total
            FROM `' . self::$table . '`
            AS t1
            LEFT JOIN `bibi_user` AS t2
            ON t1.user_id = t2.user_id
            LEFT JOIN `bibi_user_profile` AS t3
            ON t2.user_id = t3.user_id
            WHERE t1.user_id = "' . $userId . '"
        ';

        if(@$this->car_type){

            $sql .= ' AND car_type = '.$this->car_type;

            $sqlCnt .= ' AND car_type = '.$this->car_type;

        }

        $sql .= ' LIMIT '.$number.' , '.$pageSize.' ';


        $cars = $this->query($sql);

        $total = $this->query($sqlCnt)['0']['total'];

        $price = $this->getUserCarTotalPrice($userId);

        $userId=$this->currentUser;

        $items = array();

        if($cars){

            foreach($cars as $k => $car){
                $item = $this->handlerCarByList($car,$userId);
                $items[$k] = $item;
            }
        }

        $count  =count($items);

        $result['car_list']=$items;
        $result['total_price']=$price;
        $result['total']=$total;
        $result['has_more'] = (($number+$count) < $total) ? 1 : 2;

        return $result;

    }


    public function getUserCarTotal($userId){

           $sql='
               SELECT COUNT(*) as total 
               FROM `bibi_car_selling_list` 
               WHERE ( verify_status = 2 OR verify_status = 11 ) AND 
               user_id = '.$userId;

           $total = $this->query($sql)[0]['total'];

           return $total;

    }

    public function getUserCarTotalPrice($userId){
        $sql ='
            SELECT
            t1.hash,t1.model_id,
            t3.CarReferPrice
            FROM `' . self::$table . '` AS t1
            LEFT JOIN `bibi_user` AS t2
            ON t1.user_id = t2.user_id
            LEFT JOIN `bibi_car_model_detail` AS t3
            ON t3.model_id = t1.model_id
            WHERE t1.user_id = '.$userId;
        $res = $this->query($sql);

        $total_price = 0;

        if($res){
            foreach($res as $k ){
                $price = explode('万',$k["CarReferPrice"])[0];
                $total_price = $total_price + (float)$price ;
            }
        }

        return  round($total_price,2);


    }


    public function getUserCarTotalPriceList(){

        $pageSize = 10;

        $number = ($this->page-1)*$pageSize;

        if($this->page > 5 ){

            $list['list'] = array();
            $list['user_info'] = $this->getUserPrice($this->currenuser);
            $list['has_more'] =  2;
            $list['total'] = 50;

            return $list;

        }

        $sql = '
            SELECT
            sum( t3.CarReferPrice) as total_money,
            t2.type,t2.user_id,t2.nickname,t2.avatar,t2.sort
            FROM bibi_car_selling_list AS t1
            LEFT JOIN `bibi_user_profile` AS t2
            ON t2.user_id = t1.user_id
            LEFT JOIN `bibi_car_model_detail` AS t3
            ON t3.model_id = t1.model_id
            WHERE t2.type = 1
            GROUP BY t1.user_id
            ORDER BY total_money DESC 
        ';
        $sql .= ' LIMIT '.$number.' , '.$pageSize.' ';

        $items = $this->query($sql);

        foreach($items  as $k => $val){

            $key = 'rich_like_'.$val['user_id'].'_'.$this->currenuser.'';

            $likevalue= RedisDb::getValue($key);

            $items[$k]['total_money']=$val['total_money']?$val['total_money'] :0;


            if($likevalue){
                $items[$k]['is_like']=1;
            }else{
                $items[$k]['is_like']=2;
            }

            $items[$k]['rank']=$number + $k + 1;

            $rankkey = 'rich_rank_'.$val['user_id'];

            $rankvalue= RedisDb::setValue($rankkey,$items[$k]['rank']);

            $items[$k]['like_num']=$items[$k]['sort'];


        }

        $total = 50;

        $count = count($items);

        if($count != 10 ){
            $total = $number+$count;
        }

        $list['list'] = $items;
        $list['user_info'] = $this->getUserPrice($this->currenuser);
        $list['has_more'] = (($number+$count) < $total) ? 1 : 2;
        $list['total'] = $total;

        return $list;
    }


    public function getUserPrice($userId){

        $sql='
        SELECT  *
           FROM (
            SELECT
            sum( t3.CarReferPrice) as total_money,
            t2.type,t2.user_id,t2.nickname,t2.avatar,t2.sort
            FROM bibi_car_selling_list AS t1
            LEFT JOIN `bibi_user_profile` AS t2
            ON t2.user_id = t1.user_id
            LEFT JOIN `bibi_car_model_detail` AS t3
            ON t3.model_id = t1.model_id
            WHERE t2.type = 1
            GROUP BY t1.user_id
            ORDER BY total_money DESC 
           ) as A where A.user_id = '.$userId.'
        ';

        $res = $this->query($sql);

        if($res){

            $rankkey = 'rich_rank_'.$res[0]['user_id'];

            $rankvalue= RedisDb::getValue($rankkey);

            if($rankvalue){
                $res[0]['rank']=$rankvalue;

            }else{

                $res[0]['rank']=0;
            }

            $key = 'rich_like_'.$res[0]['user_id'].'_'.$userId.'';

            $likevalue= RedisDb::getValue($key);

            if($likevalue){
                $res[0]['is_like']=1;
            }else{
                $res[0]['is_like']=2;
            }

            $res[0]['like_num']=$res[0]['sort'];

            return $res ? $res[0] :new stdClass();

        }else{
            $Profile = new ProfileModel();

            $userinfo =$Profile->getProfile($userId);

            $key = 'rich_like_'.$userId.'_'.$userId.'';

            $likevalue= RedisDb::getValue($key);

            if($likevalue){
                $result['is_like']=1;
            }else{
                $result['is_like']=2;
            }
            $result['like_num']=$userinfo['sort'];

            $result['total_money']=0;
            $result['type']= $userinfo['type'];
            $result['user_id']=$userId;
            $result['nickname']=$userinfo['nickname'];
            $result['avatar']=$userinfo['avatar'];
            $result['nickname']=$userinfo['nickname'];
            $result['rank']=0;

            return $result;
        }

    }


    public function getUserVisitCars($userId){

        $pageSize = 10;

        $number = ($this->page - 1) * $pageSize;

        $sqlVisit = '
                        SELECT
                        car_id
                        FROM
                        `bibi_visit_car` 
                        WHERE user_id = '.$userId.'
                        ORDER BY created DESC
                        LIMIT ' . $number . ' , ' . $pageSize .'
                    ';

        $sqlCnt = '
                        SELECT
                        COUNT(car_id) AS total
                        FROM
                         `bibi_visit_car`
                        WHERE user_id = '.$userId.'
                    ';

        $total = $this->query($sqlCnt)[0]['total'];

        $result = @$this->query($sqlVisit);

        $result = $this->implodeArrayByKey('car_id', $result);

        $inStr = "'".str_replace(",","','",$result)."'";

        $sql = '
            SELECT
            t1.*,
            t3.avatar,t3.nickname,t3.type as user_type
            FROM `' . self::$table . '`
            AS t1
            LEFT JOIN `bibi_user` AS t2
            ON t1.user_id = t2.user_id
            LEFT JOIN `bibi_user_profile` AS t3
            ON t2.user_id = t3.user_id
        ';

        $sql .= ' WHERE t1.hash in (' . $inStr . ')'; //ORDER BY t3.comment_id DESC

        $cars = $this->query($sql);

        $count=count($cars);

        $items = array();

        if($cars){
            foreach($cars as $k => $car){
                $item = $this->handlerCarByList($car,$userId);
                $items[$k] = $item;
            }
        }

        $res['car_list']=$items;
        $res['total']=$total;
        $res['has_more'] = (($number + $count) < $total) ? 1 : 2;

        return $res;

    }


    public function getUserPublishCar($userId){

        if(@$this->pageSize){
            $pageSize = $this->pageSize;
        }else{
            $pageSize = 25;
        }

        $sql = '
            SELECT
                t1.*,
                t3.avatar,t3.nickname,t3.type as user_type
                FROM `bibi_car_selling_list` AS t1
                LEFT JOIN `bibi_user` AS t2
                ON t1.user_id = t2.user_id
                LEFT JOIN `bibi_user_profile` AS t3
                ON t2.user_id = t3.user_id
            WHERE
               
        ';

        $sqlCnt = '
            SELECT
                count(*) AS total
                FROM `bibi_car_selling_list` AS t1
                LEFT JOIN `bibi_user` AS t2
                ON t1.user_id = t2.user_id
                LEFT JOIN `bibi_user_profile` AS t3
                ON t2.user_id = t3.user_id
            WHERE  ';


        $profileModel = new \ProfileModel;
        $userInfo = $profileModel->getProfile($userId);

        $user_type=@$userInfo['type'];

        if($user_type == 2){

            $lists=$profileModel->getcompanyuserlist(1,$userId);

            $result = $this->implodeArrayByKey('user_id', $lists['user_list']);

            $result = $userId . "," .$result;

            $str = ' t2.user_id in (' . $result . ') ';

            $sql .= $str;
            $sqlCnt .= $str;

        }else{

            $sql .= ' t2.user_id ='.$userId;
            $sqlCnt .= ' t2.user_id ='.$userId;
        }

        $sql .='  AND (t1.car_type = '.PLATFORM_USER_SELLING_CAR.' OR t1.car_type = '.PLATFORM_USER_NEW_CAR.')';
        $sqlCnt .='  AND (t1.car_type = '.PLATFORM_USER_SELLING_CAR.' OR t1.car_type = '.PLATFORM_USER_NEW_CAR.')';

        if(@$this->brand_id){
            $sql.= ' AND t1.brand_id = '.$this->brand_id;
        }
        if(@$this->series_id){
            $sql.= ' AND t1.series_id ='.$this->series_id;
        }

        if(@$this->verify_status){

            if($this->verify_status == 1){

                //$sql.= ' AND (t1.verify_status = '.CAR_VERIFIED.' OR t1.verify_status = '.CAR_AUTH.' OR t1.verify_status = 4)';

            }else{
                $sql.= ' AND t1.verify_status ='.$this->verify_status;
            }
        }else{
            $sql.= ' AND (t1.verify_status = '.CAR_VERIFIED.' OR t1.verify_status = '.CAR_AUTH.')';
        }

        if(@$this->is_pacted){
            $sql.=' AND is_pacted ='.$this->is_pacted;
        }

        $number = ($this->page-1)*$pageSize;

        $sql .= ' ORDER BY  t1.updated DESC LIMIT '.$number.' , '.$pageSize.' ';

        if(@$this->brand_id){
            $sqlCnt.= ' AND t1.brand_id ='.$this->brand_id;
        }
        if(@$this->series_id){
            $sqlCnt.= ' AND t1.series_id ='.$this->series_id;
        }

        if(@$this->verify_status){

            if($this->verify_status == 1){

                $sqlCnt.= ' AND (t1.verify_status = '.CAR_VERIFIED.' OR t1.verify_status = '.CAR_AUTH.' OR t1.verify_status = 4)';

            }else{
                $sqlCnt.= ' AND t1.verify_status ='.$this->verify_status;
            }
        }else{
            $sqlCnt.= ' AND (t1.verify_status = '.CAR_VERIFIED.' OR t1.verify_status = '.CAR_AUTH.')';
        }

        if(@$this->is_pacted){
            $sqlCnt.=' AND is_pacted ='.$this->is_pacted;
        }


        $cars = $this->query($sql);


        $items = array();


        foreach($cars as $k => $car){

            $item = $this->handlerCarByList($car);

            $items[$k]['car_info'] = $item;
           // $items[$k]['car_users'] = $this->getSameBrandUsers($car['brand_id']);
        }

        $total = @$this->query($sqlCnt)[0]['total'];

        $count = count($items);

        $list['car_list'] = $items;
        $list['has_more'] = (($number+$count) < $total) ? 1 : 2;
        $list['total'] = $total;
        //$list['number'] = $number;

        return $list;


    }

    public function countUserCarNum($userId,$verity){


        $profileModel = new \ProfileModel;
        $userInfo = $profileModel->getProfile($userId);

        $user_type=@$userInfo['type'];

        if($user_type == 2){

            $list=$profileModel->getcompanyuserlist(1,$userId);

            $result = $this->implodeArrayByKey('user_id', $list['user_list']);

            $result = $userId . "," .$result;

            $str = ' user_id in (' . $result . ') ';

        }else{
            $str = ' user_id ='.$userId;
        }

        $sqlCnt = '
            SELECT
                count(*) AS total
                FROM `bibi_car_selling_list` 
            WHERE '.$str.' AND (car_type = '.PLATFORM_USER_SELLING_CAR.' OR car_type = '.PLATFORM_USER_NEW_CAR .')';

        if($verity == 11){
            $sqlCnt .= ' AND ( verify_status =2  OR verify_status = 11 )';
        }else{
            $sqlCnt .= ' AND verify_status ='.$verity;
        }

        $total = @$this->query($sqlCnt)[0]['total'];

        return $total;
    }


    public function getUserFavoriteCar($userId){

        $pageSize = 10;

        $sql = '
            SELECT
                t1.*,
                t3.avatar,t3.nickname,t3.type as user_type
                FROM `bibi_car_selling_list` AS t1
                LEFT JOIN `bibi_user` AS t2
                ON t1.user_id = t2.user_id
                LEFT JOIN `bibi_user_profile` AS t3
                ON t2.user_id = t3.user_id
                LEFT JOIN `bibi_favorite_car` AS t4
                ON t1.hash = t4.car_id
            WHERE t4.user_id = '.$userId.'
            ORDER BY t4.created DESC
        ';

        $number = ($this->page - 1)*$pageSize;

        $sql .= ' LIMIT '.$number.' , '.$pageSize.' ';

        $sqlCnt = '
            SELECT
                count(*) AS total
            FROM `bibi_car_selling_list` AS t1
            LEFT JOIN `bibi_user` AS t2
                ON t1.user_id = t2.user_id
            LEFT JOIN `bibi_user_profile` AS t3
                ON t2.user_id = t3.user_id
            LEFT JOIN `bibi_favorite_car` AS t4
                ON t1.hash = t4.car_id
                WHERE t4.user_id = '.$userId.'
        ';

        $cars = $this->query($sql);

        $items = array();

        foreach($cars as $k => $car){

            $item = $this->handlerCarByList($car);
            $items[$k]['car_info'] = $item;
            //$items[$k]['car_users'] = $this->getSameBrandUsers();
        }

        $total = @$this->query($sqlCnt)[0]['total'];

        $count = count($items);

        $list['car_list'] = $items;
        $list['has_more'] = (($number+$count) < $total) ? 1 : 2;
        $list['total'] = $total;
        //$list['number'] = $number;

        return $list;

    }













}