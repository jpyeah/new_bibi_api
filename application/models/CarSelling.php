<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/12
 * Time: 下午7:00
 */
class CarSellingModel extends PdoDb
{

    //public static $table = 'bibi_car_selling_list';
    public $brand_info;
    //public static $visit_user_id = 0;


    public function __construct()
    {

        parent::__construct();
        self::$table = 'new_bibi_car_selling_list';
    }

    public function GetCarInfoById($model_id,$userId=0)
    {


        $sql = '
            SELECT
            t1.*
            FROM `' . self::$table . '`
            AS t1
            WHERE t1.model_id = "' . $model_id . '"  
        Limit 1';

        $car = @$this->query($sql)[0];

        if (!$car) {
            return array();
        }

        $car = $this->handlerCar($car,$userId);

        return $car;

    }


    public function GetCarInfoByHash($car_id)
    {


        $sql = '
            SELECT
            t1.*
            FROM `' . self::$table . '`
            AS t1
            WHERE t1.hash = "' . $car_id . '"  
        Limit 1';

        $car = @$this->query($sql)[0];

        if (!$car) {
            return array();
        }

        $car = $this->handlerCar($car);

        return $car;

    }


    public function handlerCar($car,$userId=0){


        $brandM = new BrandModel();

        $car['brand_info']  = $brandM->getBrandModel($car['brand_id']);
        $car['series_info'] = $brandM->getSeriesModel($car['brand_id'],$car['series_id']);
        $car['model_info']  = $brandM->getModelModel($car['series_id'], $car['model_id']);
        $car['model_detail']= $brandM->getModelDetail($car['model_id']);

        $images = unserialize($car['files']);
        $items = array();

        if($images){

            foreach ($images as $k => $image) {

                if ($image['hash']) {

                    $item = array();
                    $item['file_id'] = $image['hash'];
                    $item['file_url'] = IMAGE_DOMAIN . $image['key']."?imageMogr2/auto-orient/thumbnail/1000x/strip";
                    $item['file_type'] = $image['type'] ? (int)$image['type'] : 0;
                    $items[] = $item;

                }

            }
        }

        unset($car['id']);
        $car['car_id'] = $car['hash'];
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

        //print_r($car);exit;
        //可优化查询

        $favkey = 'favorite_'.$userId.'_'.$car['car_id'].'';
        Common::globalLogRecord('favorite key', $favkey);
        $favId = RedisDb::getValue($favkey);

        $car['is_collect'] = $favId ? 1 : 2;

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




   
       
        




}