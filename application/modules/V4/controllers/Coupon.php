<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/13
 * Time: 下午6:09
 */
class CouponController extends ApiYafControllerAbstract
{

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

    //签到抽碎片
    function  getprizeAction(){

        $ChipsM = new UserChipsModel();

        $prize_arr =$ChipsM->getChipsTypeList(1);

        foreach ($prize_arr as $key => $val) {
            $arr[$val['id']] = $val['v'];
        }
        $ridk = $this->getRand($arr); //根据概率获取奖项id
        $res['yes'] = $prize_arr[$ridk-1]; //中奖项
        unset($prize_arr[$ridk-1]); //将中奖项从数组中剔除，剩下未中奖项
        shuffle($prize_arr); //打乱数组顺序
        for($i=0;$i<count($prize_arr);$i++){
            $pr[] = $prize_arr[$i];
        }
        $res['no'] = $pr;

        $userId = 389;

        $type = $res['yes']['type'];

        $chip_id = $res['yes']['id'];

        $chip = $ChipsM->getUserChipsInfo($userId,$type,$chip_id);

        if($chip){

            $ChipsM->updateChipNum($userId,$type,$chip_id,'add');

        }else{
            $properties = array();
            $properties['created_at'] = time();
            $properties['user_id'] =$userId;
            $properties['chip_id']  = $res['yes']['id'];
            $properties['type']  = $res['yes']['type'];
            $properties['chip_num']  = 1;
            $ChipsM->insert($ChipsM->tableName, $properties);
        }

        $this->send($res);
    }

    public function testAction(){

        $ChipsM = new UserChipsModel();

        $ChipsM->getUserChipsGroupBy();
    }

    //签到抽奖
    function  getDrawAction(){

        $ChipsM = new UserChipsModel();

        $prize_arr =$ChipsM->getChipsTypeList(2);

        foreach ($prize_arr as $key => $val) {
            $arr[$val['id']] = $val['v'];
        }
        $ridk = $this->getRand($arr); //根据概率获取奖项id
        $res['yes'] = $prize_arr[$ridk-1]; //中奖项
        unset($prize_arr[$ridk-1]); //将中奖项从数组中剔除，剩下未中奖项
        shuffle($prize_arr); //打乱数组顺序
        for($i=0;$i<count($prize_arr);$i++){
            $pr[] = $prize_arr[$i];
        }
        $res['no'] = $pr;
        $this->send($res);
    }

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
