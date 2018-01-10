<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/6
 * Time: 下午2:29
 */


class ShopOrderModel extends PdoDb {



    public function __construct(){

        parent::__construct();
        self::$table = 'bibi_shop_order';
    }

    public function saveProperties(){

    }
    

    public function getOrderinfo($userId=0,$orderId=0){

            $sql = 'SELECT
                order_id,order_sn,shop_id,goods_amount,order_time,order_amount,order_status,pay_code,pay_name,pay_time,user_id,order_time
                FROM
                  bibi_shop_order
                WHERE
                  `user_id` = '.$userId.'
                AND `order_id` ='.$orderId;

            @$info=$this->query($sql)[0];
            
            $info['goods_list']=$this->getordergoods($orderId);

            return $info;

    }

    public function getinfo($ordersn){

            $sql = 'SELECT
                order_id,order_sn,shop_id,goods_amount,order_time,order_amount,order_status,pay_code,pay_name,pay_time,user_id,order_time,contact_phone,contact_name,contact_address
                FROM
                  bibi_shop_order
                WHERE
                 `order_sn` ='.$ordersn;
                
            @$info=$this->query($sql)[0];
            
            $info['goods_list']=$this->getordergoods($info['order_id']);

            return $info;
    }
    
    public function getOrderinfobyordersn($order_sn){

            $sql='SELECT
            order_id,order_sn,shop_id,goods_amount,order_time,order_amount,pay_fee,pay_code,pay_name,pay_time,user_id,order_status,coupon
            FROM
            bibi_shop_order
            WHERE
            order_sn =' ."'".$order_sn."'". '
            ';
            $list=$this->query($sql);

            return $list;

    }

    public function getOrderinfobyparam($paramkey,$paramvalue){

            $sql='SELECT
            order_id,order_sn,shop_id,goods_amount,order_time,order_amount,pay_fee,pay_code,pay_name,pay_time,user_id,order_status,coupon
            FROM
            bibi_shop_order
            WHERE
            '.$paramkey.' =' ."'".$paramvalue."'". '
            AND (order_status = 2 OR order_status = 3)
            ';
            $list=$this->query($sql);

            return $list;

    }

    public function checkOrderinfobyparam($paramkey,$paramvalue){

            $sql='SELECT
            order_id,order_sn,shop_id,goods_amount,order_time,order_amount,pay_fee,pay_code,pay_name,pay_time,user_id,order_status,coupon
            FROM
            bibi_shop_order
            WHERE
            '.$paramkey.' =' ."'".$paramvalue."'". '
            AND order_status = 2 
            ';
            $list=$this->query($sql);

            if($list){
               return $list;
            }else{
               return array();
            }

            

    }


    public function getordergoods($orderId){
            $sql='SELECT
                 goods_id,buy_num,sku_id,user_id
                 FROM 
                 bibi_shop_order_goods
                 WHERE 
                 order_id ='.$orderId.'
             ';
             $goodslist=$this->query($sql);
            
             $ShopGoodsM=new ShopGoodsModel;
             foreach($goodslist as $k => $val){

                    $goodslist[$k]['goodsinfo']=$ShopGoodsM->GetGoodsInfo($val['goods_id']);
                    $goodslist[$k]['skuinfo']  =$ShopGoodsM->getsku($val['sku_id']);
             }
        
           return $goodslist;
    }


    public function getOrderlist($userId){

           $sql='SELECT
            order_id,order_sn,shop_id,goods_amount,order_time,order_amount,pay_fee,pay_code,pay_name,pay_time,user_id,order_status,coupon,contact_phone,contact_name,contact_address
            FROM
            bibi_shop_order
            WHERE
            user_id =' .$userId. '
            AND (order_status = 2 OR order_status = 3)
            ';
            $list=$this->query($sql);
           
            foreach($list as $key =>$value){
                    $list[$key]['goodslist']=$this->getordergoods($value['order_id']);
            }
            return $list;
    }


    public function ChangeOrders($userId,$orderId,$status){

        $sql = '
            UPDATE
            `bibi_shop_order`
            SET
            order_status = '.$status.'
            WHERE
            `user_id` = '.$userId.'
            AND `order_id`= '.$orderId.'
            ;
        ';

        $this->exec($sql);
    }

    public function UpdateOrders($ordersn,$status){

        $sql = '
            UPDATE
            `bibi_shop_order`
            SET
            order_status = '.$status.'
            WHERE
            `order_sn`= '.$ordersn.'
            ;
        ';

        $this->exec($sql);
    }

     public function UpdateValbyOrdersn($ordersn,$name,$value){

        $sql = '
            UPDATE
            `bibi_shop_order`
            SET
            '.$name.' = '."'".$value."'".'
            WHERE
            `order_sn`= '.$ordersn.'
            ;
        ';
       
        $this->exec($sql);
    }


    public function update($where,$data){

         $result=$this->updateByPrimaryKey(self::$table,$where,$data);
         return $result;
    }




}


