<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/6
 * Time: 下午2:29
 */


class ShopOrderGoodsModel extends PdoDb {



    public function __construct(){

        parent::__construct();
        self::$table = 'bibi_shop_order_goods';
    }

    public function saveProperties(){

    }
    

  


}


