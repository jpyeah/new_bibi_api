<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/6
 * Time: 下午2:29
 */


class ShopModel extends PdoDb {

    public $like_id;
    public $feed_id;
    public $user_id;
    public $created;

    public function __construct(){

        parent::__construct();
        self::$table = 'bibi_shop';
    }

    public function saveProperties(){

        $this->properties['feed_id'] = $this->feed_id;
        $this->properties['user_id'] = $this->user_id;
        $this->properties['created'] = $this->created;

    }

    public function getshop($page=1,$userId=0,$shop_id=0){
         

        $sql = '
                SELECT
                shop_id,image,shop_name,goods_num,lat,lng,seller_id
                FROM
                `bibi_shop` 
                WHERE
                status = 1
        ';
        $sqlCnt = '
                SELECT
                COUNT(shop_id) AS total
                FROM
                `bibi_shop`
                WHERE
                status = 1
        ';
        
        $pageSize = 10;
        $number = ($page - 1) * $pageSize;

        if($shop_id){
             $sql .= 'AND  shop_id = '.$shop_id;
        }
        
        $sql .= '  LIMIT ' . $number . ' , ' . $pageSize . ' ';
        
        $total = $this->query($sqlCnt)[0]['total'];

        $shops= $this->query($sql);

        $shops = $this->handleshop($shops);
         
        $count = count($shops);

        $list = array();
        
        if(!$shop_id){

             $list['shop_list'] = $shops;
             $list['has_more'] = (($number + $count) < $total) ? 1 : 2;
             $list['total'] = $total;
             return $list;
        }
        else{

            return isset($shops[0]) ? $shops[0] : array() ;
        }
       
        
        

    }
    public function handleshop($shops){

        return $shops;
    }

    public function update($where,$data){

         $result=$this->updateByPrimaryKey(self::$table,$where,$data);
         return $result;
    }



    public function dealFilesWithString($goods_list)
    {

        $filesInfo = array();

        $files = json_decode($goods_list, true);

        return $files;

    }


}


