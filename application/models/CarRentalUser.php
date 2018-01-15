<?php

/**
 * Created by PhpStorm.
 * User: jpjy
 * Date: 17/07/03
 * Time: 下午7:00
 */
class CarRentalUserModel extends PdoDb
{

    public function __construct()
    {

        parent::__construct();
        self::$table = 'bibi_car_selling_rental_user_info';
    }

    public function saveProperties()
    {
        $this->properties['user_id']        = $this->user_id;
        $this->properties['card_no']        = $this->card_no;
        $this->properties['contact_name']   = $this->contact_name;
        $this->properties['card_cur']       = $this->card_cur;
        $this->properties['card_opp']       = $this->card_opp;
        $this->properties['drive_cur']      = $this->drive_cur;
        $this->properties['drive_opp']      = $this->drive_opp;
        $this->properties['created_at']     = $this->created_at;
       //$this->properties['mobile']         = @$this->mobile;

    }


    public function getUserByCardNo($user_id,$card_no){

        $sql = '
                SELECT id
                FROM `bibi_car_selling_rental_user_info` 
                ';
        $sql .= " WHERE user_id = ".$user_id."  AND card_no = '".$card_no."'";


        $result =@$this->query($sql);

        if($result){
            return $result['0'];
        }else{
            return array();
        }

    }


    public function getRentalUserById($user_id){

        $sql = '
                SELECT id,contact_name,mobile
                FROM `bibi_car_selling_rental_user_info` 
                ';
        $sql .= " WHERE user_id = ".$user_id;

        $result =@$this->query($sql);

        if($result){
            return $result['0'];
        }else{
            return array();
        }


    }

    public function update($where, $data){

        $res =  $this->updateByPrimaryKey(self::$table, $where, $data);

        return $res;

    }



}

