<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/6
 * Time: 下午2:29
 */


class ShopCartModel extends PdoDb {

    public $like_id;
    public $feed_id;
    public $user_id;
    public $created;

    public function __construct(){

        parent::__construct();
        self::$table = 'bibi_shop_cart';
    }

    public function saveProperties(){

    }
    

    

    public function getCarlist($userId){

            $sql = 'SELECT
                id,user_id,goods_id,goods_number,sku_id,created
                FROM
                  bibi_shop_cart
                WHERE
                  `user_id` = '.$userId;
            $list=$this->query($sql);

            $ShopGoodsM=new ShopGoodsModel;
            foreach($list as $key =>$value){

                   
                   $goodsinfo=$ShopGoodsM->GetGoodsInfo($value['goods_id']);
                   $list[$key]['shop_id']=$goodsinfo['shop_id'];
                   $list[$key]['goodsinfo']=$goodsinfo;
                   $skuinfo=$ShopGoodsM->getsku($value['sku_id']);
                   $list[$key]['skuinfo']=$skuinfo;
            }

            return $list;

    }


    public function handlecart($list){

           foreach($list as $key =>$value){
                  $value['goods_id'];
                  $goodsM = new  ShopGoodsModel;
                  $goodsinfo=$goodsM->GetGoodsInfoById($value['goods_id']);
                  $value['sku_id'];
           }
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
               $info["skuinfo"]=array();

         }

           return $info;
         
    }



    public function get($userId,$goodsId,$skuId)
    {
         
         $sql = 'SELECT
                  `id`
                FROM
                  bibi_shop_cart
                WHERE
                  `user_id` = '.$userId.' AND `goods_id` = "'.$goodsId.'" ';

        if($skuId){
            $sql .=' AND `sku_id`='.$skuId;
        }
         $item = @$this->query($sql)[0];
         return $item;
        /*
                 $key = 'usershopcart_'.$this->user_id.'_'.$this->feed_id.'';
                    if($item){
                        
                        $visitId = $item['id'];
                        $result=RedisDb::setValue($key,$visitId);
                       
                        return $visitId;
                    }
                    else{

                        RedisDb::setValue($key, 0);

                        return 0;
                    }
                


                $visitId = RedisDb::getValue($key);
                   
                if(!$visitId){

                    $sql = 'SELECT
                          `id`
                        FROM
                          '.$this->tableName.'
                        WHERE
                          `user_id` = '.$this->user_id.' AND `feed_id` = "'.$this->feed_id.'" ';


                    $item = @$this->query($sql)[0];
                   
                    if($item){

                        $visitId = $item['id'];
                        RedisDb::setValue($key,$visitId);

                        return $visitId;
                    }
                    else{

                        RedisDb::setValue($key, 0);

                        return 0;
                    }

                }
                else{
                   
                    return $visitId;
                }
             */ 
    }

    public function deleteCart($userId=0,$goodsId=0,$skuId=0){
        
        $sql = ' DELETE FROM `bibi_shop_cart` WHERE `user_id` = '.$userId;
        
        if($goodsId && $skuId){
            $sql.= ' AND `goods_id` = '.$goodsId.' AND `sku_id` = '.$skuId.' ';
        }

       $result=$this->execute($sql);

       return $result;

    }




    public function handleshop($shops){

        return $shops;
    }


    public function dealFilesWithString($goods_list)
    {

        $filesInfo = array();

        $files = json_decode($goods_list, true);

        return $files;

    }


}


