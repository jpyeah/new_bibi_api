<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/11
 * Time: 上午10:58
 */

class BrandModel extends PdoDb{

    static public  $tableBrand  = 'bibi_new_car_brand_list';
    static public  $tableSeries = 'bibi_new_car_brand_series';
    static public  $tableModel  = 'bibi_new_car_series_model';

    public $brand_id;
    public $series_id;
    public $model_id;

    public function __construct(){

        parent::__construct();

    }

    public function getBrandInfo(){

        $sql = 'SELECT
                CONCAT(t1.brand_name, t2.brand_series_name, t3.model_name) AS car_name,
                t1.brand_id,
                t1.brand_name,
                t2.brand_series_id,
                t2.brand_series_name AS `series_name`,
                t3.model_id,
                t3.model_name
                FROM
                `'.self::$tableBrand.'` AS t1 LEFT JOIN `'.self::$tableSeries.'` AS t2
                ON t1.brand_id = t2.brand_id
                LEFT JOIN `'.self::$tableModel.'` AS t3
                ON t2.brand_series_id = t3.series_id
                WHERE
                t1.brand_id = '.$this->brand_id.'
                AND t2.brand_series_id = '.$this->series_id.'
                AND t3.model_id = '.$this->model_id.'
                ';

        $info = $this->query($sql);

        return isset($info[0]) ? $info[0] : array();
    }

    public function getBrandModel($brandId){


            $sql = 'SELECT `brand_id`, `brand_name`, `abbre`, `brand_url` FROM `bibi_new_car_brand_list` WHERE `brand_id` = "'.$brandId.'" ';

            $brandM = $this->query($sql);

            if(isset($brandM[0])){

                return $brandM[0];
            }
            else{
                return new stdClass;
            }



    }

    public function getSeriesModel($brandId, $seriesId){


            $sql = 'SELECT `brand_series_id` AS `series_id`, `brand_series_name` AS `series_name`,`series_info`,`brand_series_url1`,`brand_series_url2` ,`brand_series_video` FROM `bibi_new_car_brand_series` WHERE `brand_id` = ' . $brandId . ' AND `brand_series_id` = '.$seriesId.' ';

            $series = $this->query($sql);

            if(isset($series[0])){

                $info = $series[0];
                $info['brand_id'] = $brandId;
                return $info;

            }
            else{

                return new stdClass();
            }
    }

    public function getSeriesInfo($seriesId){


        $sql = 'SELECT `brand_series_id` AS `series_id`, `brand_series_name` AS `series_name`,`series_info`,`brand_series_url1`,`brand_series_url2` ,`brand_series_video`,`brand_id` FROM `bibi_new_car_brand_series` WHERE  `brand_series_id` = '.$seriesId;

        $series = $this->query($sql);

        if(isset($series[0])){

            $info = $series[0];
            $info['brand_id'] = $series[0]['brand_id'];
            return $info;

        }
        else{

            return new stdClass();
        }
    }

    public function getModelModel($seriesId, $modelId)
    {

            $sql = 'SELECT `model_id` ,`model_year` , `model_name` FROM `bibi_new_car_series_model` WHERE  `series_id` = '.$seriesId.' AND `model_id`='.$modelId.' ';

            $model = $this->query($sql);

            if(isset($model[0])){

                $info = $model[0];
                $info['series_id'] = $seriesId;

               // $name = explode(' ', $info['model_name']);

               // $info['model_name'] = $name[0] . ' ' . $name[1] . ' ' . $name[2];

                return $info;
            }
            else{

                return new stdClass();
            }

    }

    public function getModelDetail($modelId)
    {


            $sql = 'SELECT * FROM `bibi_new_car_model_detail` WHERE  `model_id`='.$modelId.' ';

            $model = $this->query($sql);

            if(isset($model[0])){

                $info = $model[0];

                return $info;
            }
            else{

                return new stdClass();
            }
    }


    public function getSeries($page=1){

        $sql = 'SELECT 
               `brand_series_url1`,
               `brand_series_url2`,
               `brand_series_id` AS `series_id`,
               `brand_series_name` AS `series_name`, 
               `brand_series_video` AS `series_video`,
               `brand_series_video_img` AS `series_video_img`
                FROM `bibi_new_car_brand_series` 
          ';

        $sqlCnt = 'SELECT 
                   count(*) as total 
                   FROM `bibi_new_car_brand_series` 
        ';

        $pageSize = 10;

        $number = ($page - 1) * $pageSize;

        $sql .= '  LIMIT ' . $number . ' , ' . $pageSize . ' ';

        $total = $this->query($sqlCnt)[0]['total'];

        $series = $this->query($sql);


        $count = count($series);

        $list['list'] = $series;
        $list['has_more'] = (($number + $count) < $total) ? 1 : 2;
        $list['total'] = $total;

        return $list;

    }




}