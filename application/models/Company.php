<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/6
 * Time: 下午2:29
 */


class CouponModel extends PdoDb {

    public $type_id;
    public $tatus_id;
    public $user_id;
    public $created;
    public $code;

    public function __construct(){

        parent::__construct();
        self::$table = 'bibi_coupon';
    }

    public function saveProperties(){
        $this->properties['type'] = $this->type;
        $this->properties['user_id'] = $this->user_id;
        $this->properties['code'] = $this->code;
        $this->properties['status'] = $this->status;
        $this->properties['created'] = $this->created;
        $this->properties['updated'] = $this->updated;
    }



    public function getcode($type,$userId){
        $sql='select code,user_id,type,created from '.self::$table.' where type = '.$type." and user_id = ".$userId;
        $res=$this->query($sql);
        return $res;
    }




}


