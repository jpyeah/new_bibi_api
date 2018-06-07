<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/12
 * Time: 下午7:00
 */
class PushTokenModel extends PdoDb
{

    //public static $table = 'bibi_car_selling_list';
    //public static $visit_user_id = 0;


    public function __construct()
    {

        parent::__construct();
        self::$table = 'bibi_new_push_token';
    }


    public function gettoken($userId){

        $sql ="SELECT * FROM `bibi_new_push_token` WHERE user_id=".$userId;

        $res =$this->query($sql);

        return $res;

    }



















}