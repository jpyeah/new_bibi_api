<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/6
 * Time: 下午2:29
 */


class CarRentalOrderModel extends PdoDb {



    public function __construct(){

        parent::__construct();
        self::$table = 'bibi_car_selling_rental_order';
    }

    public function getRentalOrderList()
    {
        $pageSize = 10;

        $number = ($this->page-1)*$pageSize;

        $sql = '
                SELECT
                *
                FROM `bibi_car_selling_rental_order`
                ';

        $sqlCnt = '
                SELECT
                count(*) AS total
                FROM `bibi_car_selling_rental_order`
                ';

        $sql .= ' WHERE user_id= '.$this->currentUser; //ORDER BY t3.comment_id DESC;
        $sqlCnt .= ' WHERE user_id= '.$this->currentUser; //ORDER BY t3.comment_id DESC;

        $sql .= '  AND ( status = 3 OR status = 5 )'; //ORDER BY t3.comment_id DESC;
        $sqlCnt .= '  AND (status = 3 OR status = 5)'; //ORDER BY t3.comment_id DESC;

        $number = ($this->page-1)*$pageSize;

        $sql .= ' LIMIT '.$number.' , '.$pageSize.' ';


        $orders = $this->query($sql);

        $total = @$this->query($sqlCnt)[0]['total'];

        $items = array();

        $Rentalcar = new CarRentalModel();
        foreach($orders as $k => $order){

            $car_id = $order['car_id'];
            $item = $Rentalcar->GetCarInfoById($car_id);
            $orders[$k]['car_info'] = $item;
        }

        $count = count($orders);

        $list['order_list'] = $orders;
        $list['has_more'] = (($number+$count) < $total) ? 1 : 2;
        $list['total'] = $total;
        //$list['number'] = $number;

        return $list;
    }


    public function getRentalOrderListV1()
    {
        $pageSize = 10;

        $number = ($this->page-1)*$pageSize;

        $sql = '
                SELECT
                *
                FROM `bibi_car_selling_rental_order`
                ';

        $sqlCnt = '
                SELECT
                count(*) AS total
                FROM `bibi_car_selling_rental_order`
                ';

        $sql .= ' WHERE user_id= '.$this->currentUser; //ORDER BY t3.comment_id DESC;
        $sqlCnt .= ' WHERE user_id= '.$this->currentUser; //ORDER BY t3.comment_id DESC;

        $sql .= '  AND ( status = 3 OR status = 5 )'; //ORDER BY t3.comment_id DESC;
        $sqlCnt .= '  AND (status = 3 OR status = 5)'; //ORDER BY t3.comment_id DESC;

        $number = ($this->page-1)*$pageSize;

        $sql .= ' LIMIT '.$number.' , '.$pageSize.' ';


        $orders = $this->query($sql);

        $total = @$this->query($sqlCnt)[0]['total'];

        $items = array();

        $Rentalcar = new CarRentalV1Model();
        foreach($orders as $k => $order){

            $car_id = $order['car_id'];
            $item = $Rentalcar->GetCarInfoById($car_id);
            $orders[$k]['car_info'] = $item;
        }

        $count = count($orders);

        $list['order_list'] = $orders;
        $list['has_more'] = (($number+$count) < $total) ? 1 : 2;
        $list['total'] = $total;
        //$list['number'] = $number;

        return $list;
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

    public function update($where, $data){

       $res =  $this->updateByPrimaryKey('bibi_car_selling_rental_order', $where, $data);

       return $res;

    }

    public function getRentalOrderInfoByCarId($car_id){

        $sql = '
            SELECT
            rental_time_end
            FROM `bibi_car_selling_rental_order`
            WHERE car_id = "' . $car_id . '" 
            AND status = 3
            ORDER BY id DESC LIMIT 1
        ';

        $info = $this->query($sql);

        if($info){

            return $info[0];
        }else{

            return array();
        }


    }

    public function getRentalOrderInfo($order_sn){

        $sql = '
            SELECT
            *
            FROM `bibi_car_selling_rental_order`
            WHERE order_sn = "' . $order_sn . '"
        ';


        $info = $this->query($sql);

        if($info){

            $Info['order_info']=$info['0'];
            $CarRentalM =new CarRentalModel();
            $Info['car_info']  = $CarRentalM->GetCarInfoById($Info['order_info']['car_id']);
            return $Info;
        }else{

            return array();
        }

    }


    public function getRentalOrderInfoV1($order_sn){

        $sql = '
            SELECT
            *
            FROM `bibi_car_selling_rental_order`
            WHERE order_sn = "' . $order_sn . '"
        ';


        $info = $this->query($sql);

        if($info){

            $Info['order_info']=$info['0'];
            $CarRentalM =new CarRentalV1Model();
            $Info['car_info']  = $CarRentalM->GetCarInfoById($Info['order_info']['car_id']);
            return $Info;
        }else{

            return array();
        }

    }






}


