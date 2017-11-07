<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/12
 * Time: 下午7:00
 */
class CarSellingExtraInfoModel extends PdoDb
{

    //public static $table = 'bibi_car_selling_list';
    public $brand_info;
    //public static $visit_user_id = 0;


    public function __construct()
    {

        parent::__construct();
        self::$table = 'bibi_car_selling_list_extra_info';
    }

    public function getExtrainfolist(){

        $sql ="SELECT * FROM bibi_car_selling_list_extra_info_list";

        $list = $this->query($sql);

        foreach($list as $k =>$val){

               switch($val['type']){
                   case 1:
                       $items1['type_name']='常用配置';
                       $items1['type_id']=1;
                       $items1['list'][]=$val;
                       break;
                   case 2:
                       $items2['type_name']='智能系统';
                       $items2['type_id']=2;
                       $items2['list'][]=$val;
                       break;
                   case 3:
                       $items3['type_name']='驾驶员辅助系统';
                       $items3['type_id']=3;
                       $items3['list'][]=$val;
                       break;
                   case 4:
                       $items4['type_name']='视觉辅助系统';
                       $items4['type_id']=4;
                       $items4['list'][]=$val;
                       break;
                   case 5:
                       $items5['type_name']='LED灯光系统';
                       $items5['type_id']=5;
                       $items5['list'][]=$val;
                       break;
                   case 6:
                       $items6['type_name']='运动系统';
                       $items6['type_id']=6;
                       $items6['list'][]=$val;
                       break;
               }
        }

        $arr=[$items1,$items2,$items3,$items4,$items5,$items6];

        return $arr;
    }

    public function getExtraInfo(){

        $sql ="SELECT * FROM bibi_car_selling_list_extra_info_list";

        $sql .= $this->where;

       // print_r($sql);exit;

        $list = $this->query($sql);

        return $list;
    }

    public function getExtra($hash){

        $sql ="SELECT * FROM bibi_car_selling_list_extra_info WHERE hash ='".$hash."'";

        $res = $this->query($sql);

        return $res ? $res[0] : array();
    }







}