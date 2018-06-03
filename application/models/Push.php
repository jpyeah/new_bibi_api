<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/12
 * Time: 下午7:00
 */
class PushModel extends PdoDb
{

    //public static $table = 'bibi_car_selling_list';
    //public static $visit_user_id = 0;


    public function __construct()
    {

        parent::__construct();
        self::$table = 'bibi_push_list';
    }

    public function getPushs($page=1){

        $sql = '
                SELECT
                `title`,`content`,`created_at`,`image_url`,`from`,`type`,`related_id`
                FROM
                `bibi_push_list`
                ';
        $sqlCnt = '
                SELECT
                COUNT(id) AS total
                FROM
                `bibi_push_list` 
            ';


        $pageSize = 10;

        $number = ($page - 1) * $pageSize;

        $sql .= '  LIMIT ' . $number . ' , ' . $pageSize . ' ';

        $total = $this->query($sqlCnt)[0]['total'];

        $lists = $this->query($sql);

        $count = count($lists);

        $list['list'] = $lists;
        $list['has_more'] = (($number + $count) < $total) ? 1 : 2;
        $list['total'] = $total;

        return $list;


    }
















}