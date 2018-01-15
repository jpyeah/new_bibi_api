<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/12
 * Time: 下午7:00
 */
class ShopGoodsModel extends PdoDb
{

    //public static $table = 'bibi_car_selling_list';
    public $brand_info;
    //public static $visit_user_id = 0;

    public function __construct()
    {

        parent::__construct();
        self::$table = 'bibi_shop_goods';
    }

    public function handlerGoods($goods,$userId=0){
         /*
        //print_r($car);exit;
        //可优化查询
        $favkey = 'favorite_'.$userId.'_'.$car['car_id'].'';
        Common::globalLogRecord('favorite key', $favkey);
        $favId = RedisDb::getValue($favkey);


        $car['is_fav'] = $favId ? 1 : 2;
        $car['car_time'] = Common::getBeforeTimes($car['created']);
        //$car['visit_num'] = $car['visit_num'];
        //

        $likeKey='favoritecarlike_'.$car['car_id'].'_'.$userId.'';
        Common::globalLogRecord('like key', $likeKey);
        $isLike = RedisDb::getValue($likeKey);

        $car['is_like']  = $isLike ? 1 : 2;

        $favCarM = null;
        */
        return $goods;

    }

    public function getGoodsList($userId =0,$order=0)
    {

        $pageSize = 10;
        $sql = '
                SELECT
                t1.goods_id,t1.goods_item,t1.goods_name,t1.image_url,t1.sales,t1.stock,t1.price,t1.type,t1.title,t1.desc
                FROM `bibi_shop_goods` AS t1
                ';
        $sqlCnt = '
                SELECT
                count(t1.goods_id) AS total
                FROM `bibi_shop_goods` AS t1
                ';
        if($order){
            $sql .= ' WHERE t1.goods_id in (' . $result . ')';
        }
        
        $sql .= $this->where;
        $sql .= $this->order;
        $number = ($this->page-1)*$pageSize;
        $sql .= ' LIMIT '.$number.' , '.$pageSize.' ';
        
        $goods = $this->query($sql);
        
        $sqlCnt .= $this->where;
        $sqlCnt .= $this->order;

        $total = @$this->query($sqlCnt)[0]['total'];

        $count = count($goods);

        $shopM=new ShopModel();
        $list['shop_info'] =$shopM->getshop(1,0,1);

        foreach($goods as $key =>$value){
            $goods[$key]['sku_info']  =$this->getskuinfo($value['goods_id']);
        }
        $list['goods_list'] = $goods;
        $list['has_more'] = (($number+$count) < $total) ? 1 : 2;
        $list['total'] = $total;
        //$list['number'] = $number;
        return $list;
    }

    public function getGoodsListV1($userId =0,$order=0)
    {

        $pageSize = 10;
        $sql = '
                SELECT
                t1.goods_id,t1.goods_item,t1.goods_name,t1.image_url,t1.sales,t1.stock,t1.price,t1.type,t1.title,t1.desc
                FROM `bibi_shop_goods` AS t1
                ';
        $sqlCnt = '
                SELECT
                count(t1.goods_id) AS total
                FROM `bibi_shop_goods` AS t1
                ';

        $sql .= $this->where;
        $number = ($this->page-1)*$pageSize;
        $sql .= ' LIMIT '.$number.' , '.$pageSize.' ';

        $goods = $this->query($sql);

        $sqlCnt .= $this->where;

        $total = @$this->query($sqlCnt)[0]['total'];

        $count = count($goods);

        $shopM=new ShopModel();
        $list['shop_info'] =$shopM->getshop(1,0,4);

        foreach($goods as $key =>$value){
            $goods[$key]['sku_info']  =$this->getskuinfo($value['goods_id']);
        }

        $list['goods_list'] = $goods;
        $list['has_more'] = (($number+$count) < $total) ? 1 : 2;
        $list['total'] = $total;
        //$list['number'] = $number;
        return $list;
    }
    
    public function GetGoodsInfo($goodsId,$userId=0)
    {

        $sql = '
            SELECT
            goods_id,goods_item,good_sn,shop_id,goods_name,price,image_url,sales
            FROM `' . self::$table . '`
            WHERE goods_id = "' . $goodsId . '"
        ';
        
        $goods= @$this->query($sql)[0];
       
        if (!$goods) {

            return new stdClass();
        }

        return $goods;

    }

   public function GetGoodsInfoById($goodsId,$userId=0)
    {


        $sql = '
            SELECT
            *
            FROM `' . self::$table . '`
            WHERE goods_id = "' . $goodsId . '"
        ';

        $goods= @$this->query($sql)[0];
        
        if (!$goods) {

            return new stdClass();
        }

        $goods = $this->handlerGood($goods,$userId);

        return $goods;

    }

    public function handlerGood($goods,$userId=0){
       $items=array();
       
       $goods['sku_info']=$this->getskuinfo($goods['goods_id']);
       $goods['attr']=$this->getgoodsattr($goods['goods_id']);
       $goods['files']=$this->DEFiles($goods['files']);


        return $goods;

    }
       public function DEFiles($postFiles)
    {

        $images = unserialize($postFiles);
        $items = array();

        if($images){

            foreach ($images as $k => $image) {

                if ($image['hash']) {

                    $item = array();
                    $item['file_id'] = $image['hash'];
                    $item['file_url'] = IMAGE_DOMAIN . $image['key']."?imageMogr2/auto-orient/thumbnail/1000x/strip";
                    $item['file_type'] = $image['type'] ? $image['type'] : 0;
                    $items[] = $item;

                }

            }
        }

        return $items;

    }

    public function getgoodsattr($goods_id)
    {

         $sqlattr = '
            SELECT
            *
            FROM `bibi_shop_goods_attribute`
            WHERE attribute_goods_id = "' .$goods_id. '"
        ';
        $goodsAttr= @$this->query($sqlattr);

        foreach($goodsAttr as $key =>$value){

                $sqlattrvalue = '
                        SELECT
                        *
                        FROM `bibi_shop_goods_attribute_value`
                        WHERE attribute_id = "' . $value['attribute_id'] . '"
                  ';
                $goodsAttrvalue= @$this->query($sqlattrvalue);
                $goodsAttr[$key]['attr_info']=$goodsAttrvalue;
         }
        /*
        
        */
        return $goodsAttr;

    }

   


    public function relatedPriceCars($carId , $price){

        $minPrice = $price * 0.7;
        $maxPrice = $price * 1.3;


        $sql = '
                SELECT
                t1.*,
                t3.avatar,t3.nickname
                FROM `bibi_car_selling_list` AS t1
                LEFT JOIN `bibi_user` AS t2
                ON t1.user_id = t2.user_id
                LEFT JOIN `bibi_user_profile` AS t3
                ON t2.user_id = t3.user_id
                WHERE
                 t1.files <> "" AND t1.car_type != 3 AND t1.hash != "'.$carId.'" AND
                 t1.brand_id > 0 AND t1.series_id > 0 AND
                t1.price BETWEEN '.$minPrice.' AND '.$maxPrice.'
                AND (t1.verify_status = 2 OR t1.verify_status = 11 OR t1.verify_status =4)
				ORDER BY t1.car_type ASC, t1.price ASC
                LIMIT 0 , 20
                ';


        $cars = $this->query($sql);

        $items = array();

        if($cars){

            foreach($cars as $k => $car){

                $item = $this->handlerCar($car);
                $items[$k] = $item;
            }
        }


        return $items;
    }

     public function getsku($skuId){

           $sql='SELECT sku_id,goods_id,attr_bianma,price,stock
           FROM
           `bibi_shop_goods_sku`
           WHERE
           `sku_id` ='.$skuId;

           @$sku=$this->query($sql)[0];

           if($sku['attr_bianma']){


               $arr=explode(',',$sku['attr_bianma']);

               $info=array();
               $info["price"]=$sku['price'];
               $info["sku_id"]=$sku['sku_id'];
               $info["goods_id"]=$sku['goods_id'];
               $info["skuinfo"]=array();
               foreach($arr as $k => $val){
                    $sqlsku='SELECT attribute_name,value_name
                           FROM
                           `bibi_shop_goods_attribute_value`
                           WHERE
                           `value_id` ='.$val;
                     $attr=$this->query($sqlsku)[0];
                     $info["skuinfo"][$k]['attribute_name']=$attr['attribute_name'];
                     $info['skuinfo'][$k]['value_name']=$attr['value_name'];
              }
         }else{

               $info=array();
               $info["price"]=$sku['price'];
               $info["sku_id"]=$sku['sku_id'];
               $info["goods_id"]=$sku['goods_id'];
               $info["skuinfo"]=new stdClass();

         }

           return $info;
         
    }

    public function getskuinfo($goodsId){

           $sql='SELECT sku_id,goods_id,attr_bianma,price,stock
           FROM
           `bibi_shop_goods_sku`
           WHERE
           `goods_id` ='.$goodsId;

           $info=$this->query($sql);

           return $info;

    }


    public function getGoodsAmount($data){


           $Goods_amount =0;
           foreach($data as $k => $val){


               $goods = $this->GetGoodsInfo($val['goods_id']);

               if($goods){

                   $Goods_amount = $Goods_amount + $val['buy_num'] * $goods['price'];

               }
           }

           return $Goods_amount;
    }




   
       

}