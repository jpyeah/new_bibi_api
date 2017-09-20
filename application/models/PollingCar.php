<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/6
 * Time: 下午1:30
 */
class PollingCarModel extends PdoDb
{

    public function __construct()
    {

        parent::__construct();
        self::$table = 'bibi_chedang_report';
    }

    public function getReport($report_sn){

           $sql = 'SELECT * FROM `bibi_chedang_report` WHERE report_sn = '.$report_sn;

           $result  =$this->query($sql);

           return $result ? $result[0] : array();
    }

    public function getReprotBrand($brand_id){

          $sql = 'SELECT * FROM  `bibi_chedang_brand_list` WHERE brand_id ='.$brand_id;

          $brand_info = $this->query($sql);

          return $brand_info ? $brand_info[0] : array();

    }


    public function getReportList($userId,$type=0){

        $pageSize = 10;

        $number = ($this->page-1)*$pageSize;

        $sql = 'SELECT t1.* ,t2.name as brand_name,t2.logo as brand_logo 

        FROM `bibi_chedang_report` AS t1 

        LEFT JOIN `bibi_chedang_brand_list` AS t2 
         
        ON t2.brand_id =t1.brand_id 

        WHERE t1.user_id = '.$userId;

        $sql .= " AND t1.status != 1 ";

        if($type){

            $sql .= " AND t1.type = ".$type;

        }

        $sql .= ' LIMIT '.$number.' , '.$pageSize.' ';

        $sqlCnt = 'SELECT count(t1.id) as total FROM  `bibi_chedang_report` AS t1
   
        LEFT JOIN `bibi_chedang_brand_list` AS t2 
        
        ON t2.brand_id = t1.brand_id 
 
        WHERE t1.user_id='.$userId;

        $sqlCnt .= " AND t1.status != 1 ";

        if($type){

            $sqlCnt .= " AND t1.type = ".$type;

        }


        $result  =$this->query($sql);

        $total  =$this->query($sqlCnt)[0]['total'];

        $count = count($result);

        $list['list'] =$result ;
        $list['has_more'] = (($number+$count) < $total) ? 1 : 2;
        $list['total'] =$total;

        return $list;

    }


    public function geChedangBrandList(){

        $sql="SELECT brand_id,name,logo FROM `bibi_chedang_brand_list`";

        $list = $this->query($sql);

        return $list;

    }


}