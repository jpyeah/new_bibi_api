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
        self::$table = 'bibi_new_car_selling_list';
    }

    public function getCarListBySeries($series_id)
    {

        $sql = '
                SELECT
                image,model_name,car_name,exterior,interior,version,price,hash as car_id,series_id,brand_id
                FROM `bibi_new_car_selling_list` 
                ';

        $sql .= $this->where;

        $models = $this->query($sql);

        $items = array();

        foreach($models as $k => $model){
           $images = unserialize($model['image']);

           $model['image']=$images['url'];
           $items[]  = $model;
        }

        $list['car_list'] = $items;
        $brandM = new BrandModel();
        $list['series_info'] = $brandM->getSeriesInfo($series_id);
        $list['brand_info'] = $brandM->getBrandModel($list['series_info']['brand_id']);

        return $list;
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


    public function GetCarInfoByHash($car_id,$userId=0)
    {

        $sql = '
            SELECT
            *
            FROM `' . self::$table . '`
            WHERE hash = "' . $car_id . '"  ';

        $car = @$this->query($sql)[0];

        if (!$car) {
            return array();
        }

        $car = $this->handlerCar($car,$userId);

        return $car;

    }


    public function handlerCar($car,$userId=0){


        $brandM = new BrandModel();
        $car['brand_info']  = $brandM->getBrandModel($car['brand_id']);

        $car['series_info'] = $brandM->getSeriesModel($car['brand_id'],$car['series_id']);

        $car['model_info']  = $brandM->getModelModel($car['series_id'], $car['model_id']);

        $car['model_detail']= $brandM->getModelDetail($car['model_id']);

        $ExtraModel = new CarSellingExtraInfoModel();
        $car['car_extra_info'] = $car['extra_ids'] ?$ExtraModel->getExtraInfoByIds($car['extra_ids']):array();

        unset($car['extra_ids']);

        $items = array();
        if($car['files']){
            $images = unserialize($car['files']);
            if(isset($images)){
                foreach ($images as $k => $image) {
                    if ($image['key']) {
                        $item = array();
                        $item['file_id'] = $image['key'];
                        $item['file_url'] = $image['url'];
                        $item['file_type'] = 0;
                        $items[] = $item;
                    }
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

        $image= unserialize($car['image']);
        $car['image']=$image['url'];

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

    public function getCarList($userId = 0)
    {

        $pageSize = 10;

        $sql = '
                SELECT
                image,model_name,car_name,exterior,interior,version,price,hash as car_id
                FROM `bibi_new_car_selling_list`
                ';

        $sqlCnt = '
                SELECT
                count(*) AS total
                FROM `bibi_new_car_selling_list` 
                ';

        $sql .= $this->where;

        $number = ($this->page-1)*$pageSize;

        $sql .= ' LIMIT '.$number.' , '.$pageSize.' ';

        $models = $this->query($sql);

        $items = array();

        foreach($models as $k => $model){
            $images = unserialize($model['image']);

            $model['image']=$images['url'];

            $items[]  = $model;
        }

        $sqlCnt .= $this->where;

        $total = @$this->query($sqlCnt)[0]['total'];

        $count = count($items);

        $list['car_list'] = $items;
        $list['has_more'] = (($number+$count) < $total) ? 1 : 2;
        $list['total'] = $total;

        return $list;
    }


    public function changemodel(){


        $sql = '
                SELECT
                *
                FROM `bibi_new_car_series_model` 
                ';

        $lists = @$this->query($sql);


        foreach($lists as $k){

            $se = '
                SELECT
                *
                FROM `bibi_new_car_brand_series` 
                WHERE brand_series_id ='.$k['series_id'];
            $ses =$this->query($se)[0];

            $brand = '
                SELECT
                *
                FROM `bibi_new_car_brand_list` 
                WHERE brand_id ='.$ses['brand_id'];
            $br =$this->query($brand)[0];

            $car_name = $br['brand_name']." ".$ses['brand_series_name']." ".$k['model_name'];

            $up = 'UPDATE bibi_new_car_series_model SET car_name ='."'".$car_name."'".' WHERE model_id = '.$k['model_id'];

            $re = $this->execute($up);

        }




    }




   
       
        




}