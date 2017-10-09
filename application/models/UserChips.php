<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/19
 * Time: 下午10:42
 */

class UserChipsModel extends PdoDb{


    static public  $table = 'bibi_user_chips';
    public  $tableName = 'bibi_user_chips';

    public function init(){

        parent::__construct();
    }

    public function initProfile($data){

        $this->insert(self::$table , $data);
    }

    /**
     * ep 获取碎片类型列表
     */
    public function getChipsTypeList($type){

        $sql ="SELECT * FROM `bibi_user_chips_type` WHERE type  = ".$type;

        $res = $this->query($sql);

        return $res? $res :array();
    }

    /**
     * ep 获取用户所抽到的奖品或碎片
     * param type 抽奖类型 1：碎片 2：奖品
     *
     */
    public function getUserChips($userId,$type){

        $sql = "SELECT * FROM `bibi_user_chips` ";

        $sql .= " WHERE user_id = ".$userId;

        $sql .= " AND type = ".$type;

        $sql .= " AND chip_num  != 0 ";

        if(@$this->status){

            $sql .= " AND status = ".$this->status;
        }


        $res =  $this->query($sql);

        if($res){
            $list = $this->handelChip($res);
        }else{
            $list['chips']=array();
            $list["draw_num"]  = 0;
        }
        return $list;
    }

    public function  getChipInfo($chip_id){

        $sql = "SELECT * FROM `bibi_user_chips_type` ";

        $sql .= " WHERE id = ".$chip_id;

        $res = $this->query($sql);

        return $res ? $res[0]:array();

    }

    public function handelChip($chips){

           $draw_num = count($chips);

           if($draw_num == 4){

                $nums =array();
                foreach($chips as $k){
                       $nums[]=$k['chip_num'];
                }
               $draw_num = min($nums);
           }else{

               $draw_num = 0;
           }

           $list['chips']=$chips;
           $list['draw_num']=$draw_num;

           return $list;
    }

    /**
     * ep 查看抽奖劵
     */
    public function getUserChipsInfo($userId,$type,$chip_id){

        $sql = "SELECT * FROM `bibi_user_chips` ";

        $sql .= " WHERE user_id  =  ".$userId;

        $sql .= " AND type = ".$type;

        $sql .= " AND chip_id = ".$chip_id;

        $res = $this->query($sql);

        return $res;
    }

    /**
     * ep 查看抽奖劵
     */
    public function getUserChipsById($userId,$type,$id){

        $sql = "SELECT * FROM `bibi_user_chips` ";

        $sql .= " WHERE user_id  =  ".$userId;

        $sql .= " AND type = ".$type;

        $sql .= " AND id = ".$id;

        $res = $this->query($sql);

        return $res ? $res[0] :array();
    }

    public function updateChipNum($userId,$type,$chip_id, $action='add'){

        $condition = $action == 'add' ? 'chip_num = chip_num + 1' : 'chip_num = chip_num - 1';

        $sql = '
            UPDATE
            `bibi_user_chips`
            SET
            '.$condition.'
        ';
        $sql .= " WHERE user_id =".$userId;

        $sql .= " AND type = ".$type;

        $sql .= " AND chip_id =".$chip_id;

        $this->exec($sql);
    }


    public function CountWardNum($userId){


        $sql ='
              SELECT count(*) as total 
              FROM `bibi_user_chips` WHERE
              user_id = '.$userId.' AND type = 2
        ';

        $res = $this->query($sql);

        return $res ? $res[0]['total'] :0;



    }



}