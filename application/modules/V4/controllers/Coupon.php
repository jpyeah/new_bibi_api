<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/13
 * Time: 下午6:09
 */
class CouponController extends ApiYafControllerAbstract
{

    /**
     * @api {POST} /v4/coupon/Sign 抽碎片(签到)
     * @apiName coupon  抽碎片(签到)
     * @apiGroup coupon
     * @apiDescription 抽碎片(签到)
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 1.0.0
     *
     * @apiParam {string} [device_identifier] 设备唯一标识
     * @apiParam {string} [session_id] session_id
     *
     * @apiParamExample {json} 请求样例
     *   POST  /v4/coupon/Sign
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *     }
     *   }
     *
     */
    public function  SignAction(){

        $this->required_fields = array_merge($this->required_fields, array('session_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $key = 'user_sign_time_'.$userId;
        $last_sign_time = RedisDb::getValue($key);
        $now_time = time();

        $a_date = date('Y-m-d',$last_sign_time);
        // 获取今天的 YYYY-MM-DD 格式
        $b_date = date('Y-m-d');
        // 使用IF当作字符串判断是否相等
        if($a_date==$b_date){
            //已签到
            $has_sign=2;
            $this->send_error(USER_HAS_SIGN);
        }else{
            $has_sign=1;
            RedisDb::setValue($key,$now_time);
        }

        $ChipsM = new UserChipsModel();

        $prize_arr =$ChipsM->getChipsTypeList(1);

        $items=array();
        foreach($prize_arr as $k =>$val){

            $items[$val['id']]=$val;

        }
        $prize_arr=$items;

        foreach ($prize_arr as $key => $val) {
            $arr[$val['id']] = $val['v'];
        }
        $ridk = $this->getRand($arr); //根据概率获取奖项id
        $res['yes'] = $prize_arr[$ridk]; //中奖项
        unset($prize_arr[$ridk]); //将中奖项从数组中剔除，剩下未中奖项
        shuffle($prize_arr); //打乱数组顺序
        for($i=0;$i<count($prize_arr);$i++){
            $pr[] = $prize_arr[$i];
        }
        $res['no'] = $pr;

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


        $res['has_sign']=$has_sign;

        $this->send($res);
    }

    /**
     * @api {POST} /v4/coupon/DrawPrize  抽奖(消耗碎片)
     * @apiName coupon  抽奖(消耗碎片)
     * @apiGroup coupon
     * @apiDescription 我的碎片
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 1.0.0
     *
     * @apiParam {string} [device_identifier] 设备唯一标识
     * @apiParam {string} [session_id] session_id
     *
     * @apiParamExample {json} 请求样例
     *   POST /v4/coupon/DrawPrize
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *     }
     *   }
     *
     */
    public function DrawPrizeAction(){

        $this->required_fields = array_merge($this->required_fields, array('session_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $ChipsM = new UserChipsModel();

        $res = $ChipsM->getUserChips($userId,1);

        if($res['draw_num'] == 0 ){

            $this->send_error(DRAW_HAS_NO_NUM);
        }else{
            //消耗碎片
            $res = $this->getDraw($userId);

            $this->HandleDraw($userId,1);

            $this->send($res);
        }
    }
    /**
     * @api {POST} /v4/coupon/myChips  我的碎片
     * @apiName coupon  myChips
     * @apiGroup coupon
     * @apiDescription 我的碎片
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 1.0.0
     *
     * @apiParam {string} [device_identifier] 设备唯一标识
     * @apiParam {string} [session_id] session_id
     *
     * @apiParamExample {json} 请求样例
     *   POST /v4/coupon/myChips
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *     }
     *   }
     * @apiSuccess {number} data.draw_num   抽奖次数
     * @apiSuccess {number} data.has_sign   是否签到(今日是否抽过奖)
     *
     */
    public function myChipsAction(){

        $this->required_fields = array_merge($this->required_fields, array('session_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $key = 'user_sign_time_'.$userId;
        $last_sign_time = RedisDb::getValue($key);
        $now_time = time();
        $time = $now_time-$last_sign_time;

        if($time < 86400){
            //已签到
            $has_sign=1;
        }else{
            $has_sign=2;
        }

        $ChipsM = new UserChipsModel();

        $res = $ChipsM->getUserChips($userId,1);

        if($res){

            foreach($res['chips'] as $k => $val){

                $res['chips'][$k]['chip_info']=$ChipsM->getChipInfo($val['chip_id']);
            }
        }
        $res['ward_num'] = $ChipsM->CountWardNum($userId);

        $res['has_sign']=$has_sign;

        $this->send($res);

    }

    /**
     * @api {POST} /v4/coupon/myprize  我的抽到的奖品
     * @apiName coupon  myprize
     * @apiGroup coupon
     * @apiDescription 我的抽到的奖品
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 1.0.0
     *
     * @apiParam {string} [device_identifier] 设备唯一标识
     * @apiParam {string} [session_id] session_id
     *
     * @apiParamExample {json} 请求样例
     *   POST /v4/coupon/myprize
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *     }
     *   }
     *
     */
    public function myprizeAction(){

        $this->required_fields = array_merge($this->required_fields, array('session_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $ChipsM = new UserChipsModel();

        $ChipsM->status = 1;

        $res = $ChipsM->getUserChips($userId,2);

        if($res){

            foreach($res['chips'] as $k =>$val){
                   $res['chips'][$k]['chip_info']=$ChipsM->getChipInfo($val['chip_id']);
            }

        }
        $this->send($res);
    }

    /**
     * @api {POST} /v4/coupon/Exchangeprize  兑换奖品
     * @apiName coupon  Exchangeprize
     * @apiGroup coupon
     * @apiDescription 兑换奖品
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 1.0.0
     *
     * @apiParam {string} device_identifier 设备唯一标识
     * @apiParam {string} session_id session_id
     * @apiParam {number} id 中奖Id
     * @apiParam {string} contact_name 兑奖人姓名
     * @apiParam {number} contact_phone 兑奖人电话
     *
     * @apiParamExample {json} 请求样例
     *   POST /v4/coupon/Exchangeprize
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *     }
     *   }
     *
     */

    public function ExchangeprizeAction(){

        $this->required_fields = array_merge($this->required_fields, array('session_id','id','contact_name','contact_phone'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $id = $data['id'];

        $ChipsM = new UserChipsModel();

        $res = $ChipsM->getUserChipsById($userId,2,$id);

        if($res['status'] == 2){
            $this->send_error(PRIZE_HAS_CHANGE);
        }else{
            $where['id']=$id;
            $chip['status']=2;
            $ChipsM->updateByPrimaryKey($ChipsM->tableName,$where,$chip);
            $response['message']="成功";
//            switch($res['chip_id']){
//
//                case 6:
//                    $reward_money =6999;
//                    $reward_info ="汽车隐私贴膜";
//                    break;
//                case 7:
//                    $reward_money =3000;
//                    $reward_info ="豪车租赁优惠卷";
//                    break;
//                case 8:
//                    $reward_money =1000;
//                    $reward_info ="DOD one新车记录仪";
//                    break;
//                case 9:
//                    $reward_money =100;
//                    $reward_info ="APC中心保养优惠卷";
//                    break;
//                default:
//                    $reward_money = 50;
//                    $reward_info  = "流行音乐CD";
//                    break;
//            }

            //发送短信
           // Common::sendSMS($data['contact_phone'],array($res['user_id']+10000,$reward_money,$reward_info),"208009");

            $this->send($response);
        }

    }

    //消耗碎片抽奖
    function  getDraw($userId){

        $ChipsM = new UserChipsModel();

        $prize_arr =$ChipsM->getChipsTypeList(2);

        $items=array();
        foreach($prize_arr as $k =>$val){

            $items[$val['id']]=$val;

        }
        $prize_arr=$items;
        foreach ($prize_arr as $key => $val) {
            $arr[$val['id']] = $val['v'];
        }
        $ridk = $this->getRand($arr); //根据概率获取奖项id
        $res['yes'] = $prize_arr[$ridk]; //中奖项
        unset($prize_arr[$ridk]); //将中奖项从数组中剔除，剩下未中奖项
        shuffle($prize_arr); //打乱数组顺序
        for($i=0;$i<count($prize_arr);$i++){
            $pr[] = $prize_arr[$i];
        }
        $res['no'] = $pr;

        $type = $res['yes']['type'];

        $chip_id = $res['yes']['id'];

        $properties = array();
        $properties['created_at'] = time();
        $properties['user_id'] =$userId;
        $properties['chip_id']  = $res['yes']['id'];
        $properties['type']  = $res['yes']['type'];
        $properties['chip_num']  = 1;
        $id = $ChipsM->insert($ChipsM->tableName, $properties);

        $res['id']=$id;

        return $res;

    }


    //抽奖消耗碎片
    public function HandleDraw($userId,$type){

        $ChipsM = new UserChipsModel();

        $res = $ChipsM->getUserChips($userId,$type);

        foreach( $res["chips"] as $k){
            $chip_id = $k['chip_id'];
            $ChipsM->updateChipNum($userId,$type,$chip_id,'del');
        }

    }

    //获取概率
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


    public function TestSignAction(){


        $this->required_fields = array_merge($this->required_fields, array('session_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $key = 'user_sign_time_'.$userId;
        $last_sign_time = RedisDb::getValue($key);
        $now_time = time();
        $time = $now_time-$last_sign_time;


          // RedisDb::setValue($key,$now_time);


        $a_date = date('Y-m-d',$last_sign_time);
        // 获取今天的 YYYY-MM-DD 格式
        $b_date = date('Y-m-d');
        // 使用IF当作字符串判断是否相等
        if($a_date==$b_date){
            echo "是今".$a_date;
        }else{
            echo "不是今".$a_date;
        }

//        if($time < 86400){
//            //已签到
//            $has_sign=2;
//            $this->send_error(USER_HAS_SIGN);
//        }else{
//            $has_sign=1;
//            RedisDb::setValue($key,$now_time);
//        }

    }


    public function TestSendAction(){


        //Common::sendSMS(18823732410,array('zhongjipiao','1','牙刷'),"208009");


    }





}
