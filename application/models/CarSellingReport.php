<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/12
 * Time: 下午7:00
 */
class CarSellingReportModel extends PdoDb
{


    public function __construct()
    {
        parent::__construct();
        self::$table = 'bibi_car_selling_list_report';
    }

    public function getReport($report_id){

           $sql = "SELECT * FROM `bibi_car_selling_list_report` where id =".$report_id;

           $res = $this->query($sql);

           if($res){
               $resport = $this->handleReport($res[0]);
               return $resport;
           }
    }

    public function getReports(){

           $sql = "SELECT * FROM `bibi_car_selling_list_report` WHERE user_id =".$this->user_id;

           $res = $this->query($sql);

           $list = $this->handleReports($res);

           return $list;

    }

    public function handleReport($report){

        $CarExtraInfo = new CarSellingExtraInfoModel();

        if($report['extra_info']){

            $report['extra_info']=$CarExtraInfo->getExtraInfoByIds($report['extra_info']);
        }
        $images = unserialize($report['files']);
        $report['files'] = array();
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
                            $item['file_url'] = IMAGE_DOMAIN . $image['key']."?imageMogr2/auto-orient/thumbnail/1000x/strip";
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
                            $item['file_url'] = IMAGE_DOMAIN . $image['key']."?imageMogr2/auto-orient/thumbnail/1000x/strip";
                            $item['file_type'] =  $image['type'] ? $image['type'] : 0;
                            $items2[] = $item;
                            break;
                        case 3:
                            $item = array();
                            $item['file_id'] = $image['hash'];
                            $item['file_url'] = IMAGE_DOMAIN . $image['key']."?imageMogr2/auto-orient/thumbnail/1000x/strip";
                            $item['file_type'] =  $image['type'] ? $image['type'] : 0;
                            $items3[] = $item;
                            break;
                        case 4:
                            $item = array();
                            $item['file_id'] = $image['hash'];
                            $item['file_url'] = IMAGE_DOMAIN . $image['key']."?imageMogr2/auto-orient/thumbnail/1000x/strip";
                            $item['file_type'] =  $image['type'] ? $image['type'] : 0;
                            $items4[] = $item;
                            break;
                        default:
                            break;
                    }
                }
            }
        }
        $report['files']['type1'] = $items1;
        $report['files']['type2'] = $items2;
        $report['files']['type3'] = $items3;
        $report['files']['type4'] = $items4;

        return $report;

    }

    public function handleReports($reports){

           $items = array();
           foreach($reports as $k =>$report){
               $images = unserialize($report['files']);
               $car['files'] = new stdClass();
               $items1=array();
               $items2=array();
               $items3=array();
               $items4=array();

               if($images){
                       foreach($images as $k => $val ){
                           if($k == 0){
                               $items[$k]['file_img'] = IMAGE_DOMAIN.$val['key']."?imageMogr2/auto-orient/thumbnail/1000x/strip";
                               break;
                           }
                       }
                   }else{
                       $items[$k]['file_img'] = "";
               }
               $items[$k]['brand_name']=$report['brand_name'];
               $items[$k]['series_name']=$report['series_name'];
               $items[$k]['model_name']=$report['model_name'];
               $items[$k]['guide_price']=$report['guide_price'];
               $items[$k]['board_fee']=$report['board_fee'];
               $items[$k]['insurance_fee']=$report['insurance_fee'];
               $items[$k]['other_fee']=$report['other_fee'];
               $items[$k]['total_fee']=$report['other_fee']+$report['insurance_fee']+$report['board_fee']+$report['guide_price'];
           }
           return $items;

    }






}