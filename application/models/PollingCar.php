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


    public function getReportList($userId){


        $sql = 'SELECT * FROM `bibi_chedang_report` WHERE user_id = '.$userId;

        $sql .= " AND status != 1 ";

        $result  =$this->query($sql);

        return $result ? $result : array();

    }


    public function geChedangBrandList(){

        $sql="SELECT brand_id,name,logo FROM `bibi_chedang_brand_list`";

        $list = $this->query($sql);

        return $list;

    }


}