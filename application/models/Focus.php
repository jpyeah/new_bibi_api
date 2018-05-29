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


    public function getFocu($brand_id,$UserId){

        $sql = '
                SELECT
                id 
                FROM
                `new_bibi_car_focus` 
                WHERE brand_id = '.$brand_id.' AND
                user_id='.$UserId;


        $focus = $this->query($sql);

        return $focus;


    }

    public function deleteFocus($brand_id){

        $sql = '
                DELETE FROM new_bibi_car_focus where
                brand_id ='.$brand_id;

        $focus = $this->execute($sql);

        return $focus;

    }

}