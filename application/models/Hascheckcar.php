<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/6
 * Time: 下午1:30
 */
class HascheckcarModel extends PdoDb
{
    public $tablename = "bibi_hascheck_car";
    public function __construct()
    {

        parent::__construct();
        self::$table = 'bibi_hascheck_car';
    }

    public function saveProperties()
    {
        $this->properties['user_id'] = $this->user_id;
        $this->properties['city'] = $this->city;
        $this->properties['hphm']   = $this->hphm;
        $this->properties['engineno'] = $this->engineno;
        $this->properties['classno'] = $this->classno;
        $this->properties['created'] = $this->created;

    }


    public function getList($userId){

        $pageSize = 10;

        $number = ($this->page-1)*$pageSize;

        $sql = 'SELECT * FROM `bibi_hascheck_car` WHERE user_id = '.$userId;

        $sql .= " AND report is not null";

        $sql .= ' LIMIT '.$number.' , '.$pageSize.' ';

        $sqlCnt = 'SELECT count(*) as total FROM  `bibi_hascheck_car` WHERE user_id='.$userId;

        $sqlCnt .=" AND report is not null";

        $result  =$this->query($sql);

        $total  =$this->query($sqlCnt)[0]['total'];

        $count = count($result);

        foreach($result as $k => $val){

             $result[$k]['report']=json_decode($val['report']);

        }

        $list['list'] =$result ;
        $list['has_more'] = (($number+$count) < $total) ? 1 : 2;
        $list['total'] =$total;

        return $list;

    }

    





}