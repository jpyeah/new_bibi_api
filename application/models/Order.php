<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/12
 * Time: 下午7:00
 */
class OrderModel extends PdoDb
{

    //public static $table = 'bibi_car_selling_list';
    //public static $visit_user_id = 0;


    public function __construct()
    {

        parent::__construct();
        self::$table = 'bibi_new_car_order';
    }


    public function getOrder($userId,$car_id){

           $sql = "SELECT * FROM `bibi_new_car_order` 
           WHERE user_id = ".$userId.' AND 
           car_id = "' . $car_id . '"'
           ;
           $order = $this->query($sql);

           return $order;
    }


    public function getOrderInfo($id){

        $sql = 'SELECT * FROM `bibi_new_car_order` 
           WHERE order_id = '.$id;
        ;
        $order = $this->query($sql)[0];

        $info = $this->handleOrder($order);

        return $info;

    }

    public function handleOrder($order){

        $data['order_info']=$order;

         $CarSellingModel = new CarSellingModel();

         $data['car_info']=$CarSellingModel->GetCarInfoByHash($order['car_id']);

        $data['order_log']= $this->getOrderLog($order['order_id']);

         return $data;

    }


    public function getOrderLog($id){

        $sql = 'SELECT * FROM `bibi_new_car_order_log` 
           WHERE order_id = '.$id." ORDER BY id DESC";
        ;
        $log = $this->query($sql);

        if(!$log){
            return [];
        }
        return $log;
    }


    public function getOrders($userId,$page=1,$order_status){

        $sql = '
                SELECT
                t1.order_id,t1.order_sn,t1.user_id,t1.order_amount,t1.sub_fee,t1.order_status,
                 t2.car_name, t2.image,t2.price
                FROM
                `bibi_new_car_order` AS t1
                LEFT JOIN
                `bibi_new_car_selling_list` AS t2
                ON
                t1.car_id = t2.hash
                ';
        $sqlCnt = '
                SELECT
                COUNT(t1.order_id) AS total
                FROM
                `bibi_new_car_order` AS t1
                LEFT JOIN
                `bibi_new_car_selling_list` AS t2
                ON
                t1.car_id = t2.hash
            ';

        $sql .= ' WHERE t1.user_id = '.$userId.' ';

        $sqlCnt .= ' WHERE t1.user_id = '.$userId.' ';

        if($order_status){

            $sql .= ' AND t1.order_status = '.$order_status.' ';

            $sqlCnt .= ' AND  t1.order_status = '.$order_status.' ';

        }

        $pageSize = 10;

        $number = ($page - 1) * $pageSize;

        $sql .= '  LIMIT ' . $number . ' , ' . $pageSize . ' ';

        $total = $this->query($sqlCnt)[0]['total'];

        $orders = $this->query($sql);

        foreach($orders as $k =>$val){
               $orders[$k]['order_log']=$this->getOrderLog($val['order_id']);

              $image= unserialize($val['image']);
              $orders[$k]['image']=$image['url'];
        }

        $count = count($orders);

        $list['list'] = $orders;
        $list['has_more'] = (($number + $count) < $total) ? 1 : 2;
        $list['total'] = $total;

        return $list;


    }
















}