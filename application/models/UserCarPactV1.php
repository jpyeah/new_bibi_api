<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/19
 * Time: 下午10:42
 */

class UserCarPactV1Model extends PdoDb{


    static public  $table = 'bibi_user_car_pact';

    public function init(){

        parent::__construct();
    }

    public function initProfile($data){

        $this->insert(self::$table , $data);
    }

    public function updatePactByKey($pact_id , $data){

        $where = array('id' => $pact_id);

        $result = $this->updateByPrimaryKey(self::$table, $where, $data);
        return $result;
    }

    public function getPact($pact_id){

        $sql = "SELECT * FROM ".self::$table." WHERE id =".$pact_id;

        $result = $this->query($sql);

        return $result[0];
    }

    public function getUserPactList($user_id){

        $sql = "SELECT * FROM ".self::$table." WHERE buyer_id =".$user_id . " AND status <> 0 AND status <> 1";

        if(@$this->status){

            $sql = "SELECT * FROM ".self::$table." WHERE (buyer_id =".$user_id." OR seller_id =".$user_id.") AND status = ".$this->status;

        }

        if(@$this->type){

            $sql = "SELECT * FROM ".self::$table." WHERE (buyer_id =".$user_id." OR seller_id =".$user_id.") AND status <> 0 AND status <> 1";

          //  $sql = "SELECT * FROM ".self::$table." WHERE (buyer_id =".$user_id." OR seller_id =".$user_id.") AND status = ".$this->status;

        }


        $result = $this->query($sql);


        if($result){
            $res = array();
            foreach($result as $k => $pact){
                $res[$k]=$this->handlePact($pact);
            }

            return $res;
        }else{
            return array();
        }

    }

    public  function getSellerPactCarList($seller_id){

        $sql = "SELECT * FROM ".self::$table." WHERE seller_id =".$seller_id;

        $result = $this->query($sql);

        if($result){

            return $result;

        }else{

            return array();
        }


    }

    public function getPactbyUser($userId,$car_id){

        $sql = "SELECT * FROM ".self::$table." WHERE car_id ="."'".$car_id."'" ."  AND buyer_id =".$userId;

        $result = $this->query($sql);

        if($result){
            return $result[0];
        }else{
            return array();
        }

    }


    /**
     * @param $UserId
     * @param $seller_id
     * //获取当前买家可以预约的车辆
     */
    public function getPactCar($userId,$seller_id){

           $car = new CarSellingV1Model();

           $car->is_pacted = 1;

           $lists=$car->getUserPublishCar($seller_id);

           foreach($lists['car_list'] as $k => $list ){
            //   $lists['car_list'][$k]['car_info'][]
               $car_id = $lists['car_list'][$k]['car_info']['car_id'];

               $res=$this->getPactbyUser($userId,$car_id);

               if($res){
                  // $lists['car_list'][$k]['car_info']['pact_id']=1;
                   $lists['car_list'][$k]['pact_info']=$res;
               }else{
                   $lists['car_list'][$k]['pact_info']=new stdClass();
               }

           }

           return $lists;

    }


    /**
     *统计卖家是否可以被预约
     */
    public function SumSellerPact($seller_id){

        $sql = "SELECT COUNT(*) as total FROM ".self::$table." WHERE seller_id =".$seller_id ."  AND status <> 4 AND status <> 6 ";

        $count=$this->query($sql);

        $total = $count[0]['total'];

        $sql = "SELECT balance FROM bibi_user_profile WHERE user_id = ".$seller_id;

        $money = $this->query($sql);

        $arr['total']=$total;

        $arr['balance']=$money[0]['balance'];

        $pact_num = $arr['balance']/100;

        if($total >= $pact_num){
            $arr['has_pact']=2;
        }else{
            $arr['has_pact']=1;
        };
        return $arr['has_pact'];

    }


    public function getPactInfo($pact_id){

           $sql = "SELECT * FROM ".self::$table." WHERE id =".$pact_id;

           $result = $this->query($sql);

           $Info = $this->handlePact($result[0]);

           return $Info;
    }

    public function getPactInfoByPactNo($pact_no){

        $sql = "SELECT * FROM ".self::$table." WHERE pact_no =".$pact_no;

        $result = $this->query($sql);

        return $result[0];
    }


    public function handlePact($pact){

        $userModel = new \UserModel;
        $profileModel = new \ProfileModel;
        $CS= new CarSellingV1Model();



        $userInfo = $userModel->getInfoById($pact['buyer_id']);
        $userInfo['profile'] = $profileModel->getProfile($pact['buyer_id']);

        $sellerInfo =$userModel->getInfoById($pact['seller_id']);
        $sellerInfo['profile'] = $profileModel->getProfile($pact['seller_id']);

        $CarInfo = $CS->GetCarInfoById($pact['car_id']);

        $res['user_info']  =$userInfo;
        $res['seller_info']=$sellerInfo;
        $res['car_info']   =$CarInfo;
        $res['pact_info']  =$pact;

        return $res;

    }


    /**
     *withdraw 提现
     */
    public  function withdrawMoney($user_id){

       $sql_money  = "SELECT money FROM bibi_user_profile WHERE user_id = {$user_id}";


       $money = $this->query($sql_money);


       $sql_buyer_money = "SELECT COUNT(*) AS total FROM ".self::$table."WHERE buyer_id = {$user_id} AND status";


       $sql_seller_money = "SELECT COUNT(*) AS total FROM ".self::$table."WHERE seller_id = {$user_id} ";


    }





}