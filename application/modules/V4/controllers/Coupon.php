<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/13
 * Time: 下午6:09
 */
class CouponController extends ApiYafControllerAbstract
{

      public function ChoujiangAction(){

          $prize_arr = array(
              '0' => array('id'=>1,'min'=>-20,'max'=>20,'prize'=>'恭喜您获得了10元代金券','v'=>15),
              '1' => array('id'=>2,'min'=>40,'max'=>80,'prize'=>'恭喜您获得了50元代金券','v'=>3),
              '2' => array('id'=>3,'min'=>100,'max'=>140,'prize'=>'恭喜您获得了1元代金券','v'=>50),
              '3' => array('id'=>4,'min'=>160,'max'=>200,'prize'=>'恭喜您获得了100元代金券','v'=>2),
              '4' => array('id'=>5,'min'=>220,'max'=>260,'prize'=>'恭喜您获得了20元代金券','v'=>10),
              '5' => array('id'=>6,'min'=>280,'max'=>320,'prize'=>'恭喜您获得了5元代金券','v'=>20)
          );

          foreach ($prize_arr as $key => $val) {
              $arr[$val['id']] = $val['v'];
          }
          $rid = $this->getRand($arr); //根据概率获取奖项id
          $res = $prize_arr[$rid-1]; //中奖项
          $min = $res['min'];
          $max = $res['max'];
          $result['angle'] = mt_rand($min,$max); //随机生成一个角度
          $result['prize'] = $res['prize'];
          $response = $result;
          // echo json_encode($result);
          $this->send($response);
      }

    function getRand($proArr) {
        $result = '';
        //概率数组的总概率精度
        $proSum = array_sum($proArr);
        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset ($proArr);
        return $result;
    }


    public function TestRewardAction(){


        $userId = 544;
        $key = 'user_sign_time_'.$userId;
        $last_sign_time = RedisDb::getValue($key);
         $now_time = time();
        $time = $now_time-$last_sign_time;
        if($time < 86400){
            $is_sign=1;
        }else{
            $is_sign=2;
        }

        if($is_sign == 1){
            $res =  $this->getChips();
            print_r($res);
        }else{


        }
    }

    //签到抽奖
    public function SignAction(){

        $key = 'user_sign_time_'.$userId;
        $last_sign_time = RedisDb::getValue($key);
        $now_time = time();
        $time = $now_time-$last_sign_time;

        if($time < 86400){
            //已签到
            $is_sign=1;
            $messsage['message']="你已经签到过了,一天只有一次机会";
            $this->send();
        }else{
            $is_sign=2;
            //签到
            RedisDb::setValue($key,$now_time);
            $res = $this->getChips();
        }


    }

    public function TestSetRewardAction(){

        $userId = 544;
        $key = 'user_sign_time_'.$userId;

        $time =1504944412;

        RedisDb::setValue($key,$time);

        print_r("dsafsd");


    }

    //获取碎片
    public function getChips(){

        $prize_arr = array(
            '0' => array('id'=>1,'min'=>-20,'max'=>20,'prize'=>'恭喜您获得了10元代金券','v'=>15),
            '1' => array('id'=>2,'min'=>40,'max'=>80,'prize'=>'恭喜您获得了50元代金券','v'=>3),
            '2' => array('id'=>3,'min'=>100,'max'=>140,'prize'=>'恭喜您获得了1元代金券','v'=>50),
            '3' => array('id'=>4,'min'=>160,'max'=>200,'prize'=>'恭喜您获得了100元代金券','v'=>2),
            '4' => array('id'=>5,'min'=>220,'max'=>260,'prize'=>'恭喜您获得了20元代金券','v'=>10),
            '5' => array('id'=>6,'min'=>280,'max'=>320,'prize'=>'恭喜您获得了5元代金券','v'=>20)
        );

        foreach ($prize_arr as $key => $val) {
            $arr[$val['id']] = $val['v'];
        }
        $rid = $this->getRand($arr); //根据概率获取奖项id
        $res = $prize_arr[$rid-1]; //中奖项
        $min = $res['min'];
        $max = $res['max'];
        $result['angle'] = mt_rand($min,$max); //随机生成一个角度
        $result['prize'] = $res['prize'];
        $result['id'] = $res['id'];
        $response = $result;
        $this->send($response);
    }


}
