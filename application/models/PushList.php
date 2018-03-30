<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/19
 * Time: 下午11:30
 */


class PushListModel extends PdoDb{

    static public $table = 'bibi_push_list';

    public function __construct(){

        parent::__construct();
    }

    public function initProfile($data){

        $this->insert(self::$table , $data);
    }

    public function getUserlist($userId){

        $sql = "SELECT * FROM bibi_push_list WHERE type != 0 ";

    }

}