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




}