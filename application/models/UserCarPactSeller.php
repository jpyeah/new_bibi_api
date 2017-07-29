<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/19
 * Time: 下午10:42
 */

class UserCarPactSellerModel extends PdoDb{


    static public  $table = 'bibi_user_car_pact_seller';

    public function init(){

        parent::__construct();
    }

    public function initProfile($data){

        $this->insert(self::$table , $data);
    }

    public function updatePactByKey($id , $data){

        $where = array('id' => $id);

        $result = $this->updateByPrimaryKey(self::$table, $where, $data);
        return $result;
    }

    public function getSellerPactInfoByPactNo($pact_no){

        $sql = "SELECT * FROM ".self::$table." WHERE pact_no =".$pact_no;

        $result = $this->query($sql);

        return $result[0];
    }

    public function getSellerPactList($user_id){

        $sql = "SELECT * FROM ".self::$table." WHERE user_id =".$user_id ." AND status <> 1 ORDER BY updated";

        $result = $this->query($sql);

        if($result){
            return $result;
        }else{
            return array();
        }

    }





}