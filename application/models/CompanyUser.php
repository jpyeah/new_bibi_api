<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/19
 * Time: 下午11:30
 */


class CompanyUserModel extends PdoDb{

    static public $table = 'bibi_user_company';

    public function __construct(){

        parent::__construct();
    }

    public function initCompanyUser($data){

        $this->insert(self::$table,$data);
    }


    public function getInfoById($userId){

        $sql = 'SELECT `user_id` ,`address`, `telenumber`, `name` FROM '.self::$table.' WHERE `user_id` = '.$userId;

        $info = $this->query($sql);
        return isset($info[0]) ? $info[0] : null;
    }




}