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

    public function getReportByuser($report_id,$userId){

        $sql = "SELECT * FROM `bibi_car_selling_list_report` where id =".$report_id." AND user_id =".$userId;

        $res = $this->query($sql);

        if($res){
            $resport = $this->handleReport($res[0]);
            return $resport;
        }

    }

    public function getReports(){

        $pageSize = 10;

        $sql = "SELECT * FROM `bibi_car_selling_list_report` WHERE status = 1 AND user_id =".$this->user_id." AND car_id =".$this->car_id;

        $sqlCnt = "SELECT count(*) as total FROM `bibi_car_selling_list_report` WHERE  status = 1 AND user_id =".$this->user_id." AND car_id =".$this->car_id;

        $number = ($this->page-1)*$pageSize;

        $sql .= ' LIMIT '.$number.' , '.$pageSize.' ';

        $total = @$this->query($sqlCnt)[0]['total'];

        $res = $this->query($sql);
        $count = count($res);
        $items=array();
        foreach($res as $k =>$val){

            $items[$k]=$this->handleReports($val);
        }
        $list['list'] =  $items;
        $list['has_more'] = (($number+$count) < $total) ? 1 : 2;
        $list['total'] = $total;
        $list['number'] = $count;
        return $list;
    }

    public function handleReport($report){

        $CarExtraInfo = new CarSellingExtraInfoModel();

        if($report['extra_info']){

            $report['extra_info']=$CarExtraInfo->getExtraInfoByIds($report['extra_info']);
        }
        $brandM = new BrandModel();

        $report['brand_info']  = $brandM->getBrandModel($report['brand_id']);

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

    public function handleReports($report){

           $items = array();
               $images = unserialize($report['files']);
               $car['files'] = new stdClass();
               $items1=array();
               $items2=array();
               $items3=array();
               $items4=array();

               if($images){
                       foreach($images as $k => $val ){
                           if($k == 0){
                               $items['file_img'] = IMAGE_DOMAIN.$val['key']."?imageMogr2/auto-orient/thumbnail/1000x/strip";
                               break;
                           }
                       }
                   }else{
                       $items['file_img'] = "";
               }
               $items['report_id']=$report['id'];
               $items['total_price']=$report['total_price'];
               $items['car_name']=$report['brand_name']." ".$report['series_name']." ".$report['model_name'];
               $items['brand_name']=$report['brand_name'];
               $items['series_name']=$report['series_name'];
               $items['report_time']=$report['report_time'];

            $items['share_title'] = "【吡吡汽车】- 报价单";
            $items['share_url'] ='http://share.bibicar.cn/views/detail/offer.html?session=&id='.$report['id'].'&ident=';
            $items['share_txt'] = '更多精选二手车在bibi car,欢迎您来选购!';
            $items['share_img'] =  $items['file_img'];
               return $items;

    }






}