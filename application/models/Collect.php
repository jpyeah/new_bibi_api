<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/12/6
 * Time: 上午12:08
 */

class CollectModel extends PdoDb{

    public $user_id;
    public $car_id;
    public $created;
    public $id;

    public function __construct()
    {

        parent::__construct();
        self::$table = 'new_bibi_car_collect';
    }


    public function getCollect($userId,$page=1){

        $sql = '
                SELECT
                t1.id as collect_id,
                t1.user_id , t2.image, t2.car_name,t2.car_color
                FROM
                `new_bibi_car_collect` AS t1
                LEFT JOIN
                `new_bibi_car_selling_list` AS t2
                ON
                t1.car_id = t2.hash
                ';

            $sqlCnt = '
                SELECT
                COUNT(t1.id) AS total
                FROM
                `new_bibi_car_collect` AS t1
                LEFT JOIN
                `new_bibi_car_selling_list` AS t2
                ON
                t1.car_id = t2.hash
            ';

        $sql .= ' WHERE t1.user_id = '.$userId.' ';

        $sqlCnt .= ' WHERE t1.user_id = '.$userId.' ';

        $pageSize = 10;

        $number = ($page - 1) * $pageSize;

        $sql .= '  LIMIT ' . $number . ' , ' . $pageSize . ' ';

        $total = $this->query($sqlCnt)[0]['total'];

        $collect = $this->query($sql);

        $count = count($collect);

        $list['list'] = $collect;
        $list['has_more'] = (($number + $count) < $total) ? 1 : 2;
        $list['total'] = $total;

        return $list;

    }


    public function get(){
            
        $key = 'favorite_'.$this->user_id.'_'.$this->car_id.'';
        

        $favId = RedisDb::getValue($key);
        Common::globalLogRecord('favorite key', $key);

        if(!$favId){

            $sql = 'SELECT
                  `id`
                FROM
                `new_bibi_car_collect`
                WHERE
                  `user_id` = '.$this->user_id.' AND `car_id` = "'.$this->car_id.'" ';


            $item = @$this->query($sql)[0];

            if($item){

                $favId = $item['favorite_id'];
                RedisDb::setValue($key,$favId);

                return $favId;
            }
            else{

                RedisDb::setValue($key, 0);

                return 0;
            }

        }
        else{

            return $favId;
        }


    }

    public function delete(){

        $key = 'favorite_'.$this->user_id.'_'.$this->car_id.'';

        $this->deleteByPrimaryKey(CollectModel::$table, array('id'=>$this->id));

        RedisDb::delValue($key);

    }




}


