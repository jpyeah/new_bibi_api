<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/6/18
 * Time: 01:23
 */
class FocusModel extends PdoDb
{

    public function __construct()
    {

        parent::__construct();
        self::$table = 'new_bibi_car_focus';
    }

    public function getFocus($userId){

        $sql = '
                SELECT
                t1.id as focus_id,t2.brand_id,t2.brand_name,t2.brand_url
                FROM
                `new_bibi_car_focus` AS t1
                LEFT JOIN
                `new_bibi_car_brand_list` AS t2
                ON
                t1.brand_id = t2.brand_id
                ';

        $sql .= ' WHERE t1.user_id = '.$userId.' ';

        $focus = $this->query($sql);

        $list['list'] = $focus;

        return $list;

    }

}