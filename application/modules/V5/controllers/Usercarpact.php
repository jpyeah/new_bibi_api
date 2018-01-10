<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/2
 * Time: 下午6:41
 */

use EasyWeChat\Foundation\Application;
use EasyWeChat\Payment\Order;

use Payment\Client\Notify;

class UsercarpactController extends ApiYafControllerAbstract {


    //session_id=session5650660854db1&device_identifier=de762bd50f3e985476cb1fcfdd8886ab
    //买家 session_id=session58cb4211e4b2b&device_identifier=df4871c207120a5d73407318477b97b2  user_id = 544;
    //卖家 device_identifier=1d7c030c120f467e58e832cde18a4f4a&session_id=session58df1710ca231  user_id = 389;
    /**
     * @api {POST} /v5/UserCarPact/checkstatus 查看是否有预约
     * @apiName UserCarPact checkstatus
     * @apiGroup UserCarPact
     * @apiDescription
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     *
     * @apiParam {string} device_identifier device_identifier
     * @apiParam {string} session_id session_id
     * @apiParam {string} car_id  车辆id
     * @apiVersion 2.5.4
     *
     * @apiParamExample {json} 请求样例
     *   POST /v5/UserCarPact/pactcreate
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "car_id":"",
     *
     *     }
     *   }
     *
     * @apiSuccess {json} pact_info 预约详情.
     * @apiSuccess {json} user_info 当前用户信息
     * @apiSuccess {json} seller_info 当前车主信息
     * @apiSuccess {json} car_info 车辆信息
     * @apiSuccess {string} description 当有只返回car_info时,说明当前用户还没有预约当前车辆。
     * @apiSuccess {string} pact_info.id pact_id 预约Id
     * @apiSuccess {string} pact_info.buyer_id buyer_id 预约用户Id
     * @apiSuccess {string} pact_info.seller_id seller_id 预约车主Id
     * @apiSuccess {string} pact_info.status 状态  0:买家点击预约 1:买家付款失败 2 买家付款成功 3:卖家已确认 4:双方履约(订单完成) 5:双方履约失败,6:客服(介入)(订单完成)
     *
     *
     */

    public function CheckStatusAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id','car_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $UserCarPact=new UserCarPactV1Model;

        $result = $UserCarPact->getPactbyUser($userId,$data['car_id']);

        if($result){

            $response=$UserCarPact->getPactInfo($result['id']);

            $this->send($response);
        }else{

            $CS= new CarSellingV5Model();

            $CarInfo = $CS->GetCarInfoById($data['car_id']);

            $response['car_info'] = $CarInfo;

            $this->send($response);
        }

    }

    /**
     * @api {POST} /v5/usercarpact/updateCarPactStatus 修改车辆预约状态
     * @apiName UserCarPact  updateCarPactStatus
     * @apiGroup UserCarPact
     * @apiDescription
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     *
     * @apiParam {string} device_identifier device_identifier
     * @apiParam {string} session_id session_id
     * @apiParam {string} car_id 车辆id
     * @apiParam {number} is_pacted 1：可预约 2:不可预约
     * @apiVersion 2.5.4
     *
     * @apiParamExample {json} 请求样例
     *   POST /v5/UserCarPact/pactcreate
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "car_id":"",
     *       "is_pacted",
     *
     *
     *     }
     *   }
     *
     *
     */

    public function updateCarPactStatusAction(){


           $this->required_fields = array_merge($this->required_fields,array('session_id','car_id','is_pacted'));

           $data = $this->get_request_data();

           $userId = $this->userAuth($data);

           $CS= new CarSellingV5Model();

           $CarInfo = $CS->GetCarInfoById($data['car_id']);

           $seller_id=$CarInfo['user_info']['user_id'];

           if($seller_id != $userId){

                $this->send_error(PACT_CAR_NOT_AUTH);
           }

           if($data['is_pacted'] == 1){

           $UserCarPact = new UserCarPactV1Model();

           $if_pact=$UserCarPact->SumSellerPact($userId);

               if($if_pact == 2){

                   $this->send_error(SELLER_PACT_CAR_ENOUGH_MONEY);
               }

           }

           $update['is_pacted'] = $data['is_pacted'];

           $CS->updataPactByKey($data['car_id'],$update);

           $CarInfo=$CS->GetCarInfoById($data['car_id']);

           $this->send($CarInfo);

    }

    /**
     * @api {POST} /v5/usercarpact/getsellerpactcar 获取可以预约的车辆
     * @apiName UserCarPact getsellerpactcar
     * @apiGroup UserCarPact
     * @apiDescription
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.5.4
     *
     * @apiParam {string} device_identifier device_identifier
     * @apiParam {string} session_id session_id
     * @apiParam {string} seller_id
     *
     * @apiParamExample {json} 请求样例
     *   POST /v5/usercarpact/getsellerpactcar
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "seller_id":"",
     *
     *
     *     }
     *   }
     *
     * @apiSuccess {json} pact_info 预约详情
     * @apiSuccess {json} car_info 车辆信息
     * @apiSuccess {string} car_info.is_pacted 是否可以被预约,1:是 2:否
     * @apiSuccess {string} pact_info.id pact_id 预约Id
     * @apiSuccess {string} pact_info.buyer_id buyer_id 预约用户Id
     * @apiSuccess {string} pact_info.seller_id seller_id 预约车主Id
     * @apiSuccess {string} pact_info.status 状态  0:买家点击预约 1:买家付款失败 2 买家付款成功 3:卖家已确认 4:双方履约(订单完成) 5:双方履约失败,6:客服(介入)(订单完成)
     *
     */
    public function getSellerPactCarAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id','seller_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $UserCarPact=new UserCarPactV1Model();

        $list=$UserCarPact->getPactCarV1($userId,$data['seller_id']);

        $this->send($list);

    }



    /**
     *
     * @api {POST} /v5/UserCarPact/getpactinfo 预约详情
     * @apiName UserCarPact getpactinfo
     * @apiGroup UserCarPact
     * @apiDescription
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     *
     * @apiParam {string} device_identifier device_identifier
     * @apiParam {string} session_id session_id
     * @apiParam {string} pact_id 预约Id
     * @apiVersion 2.5.4
     *
     *
     * @apiParamExample {json} 请求样例
     *   POST /v5/UserCarPact/pactpay
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "pact_id":"",
     *
     *
     *     }
     *   }
     *
     *
     */

    public function getPactInfoAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id','pact_id'));

        $data =$this->get_request_data();

        $userId = $this->userAuth($data);

        $UserCarPact=new UserCarPactV1Model();

        $pact_info=$UserCarPact->getPact($data['pact_id']);

        $CS= new CarSellingV5Model();

        $CarInfo = $CS->GetCarInfoById($pact_info['car_id']);


        $response['pact_info'] = $pact_info;
        $response['car_info'] =  $CarInfo;

        $this->send($response);

    }



    public function getSumSellerPactAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id','seller_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $UserCarPact=new UserCarPactV1Model();

        $res=$UserCarPact->SumSellerPact($data['seller_id']);

        $this->send($res);
    }

    /**
     * @api {POST} /v5/UserCarPact/pactcreate 创建预约
     * @apiName UserCarPact pactcreate
     * @apiGroup UserCarPact
     * @apiDescription
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     *
     * @apiParam {string} device_identifier device_identifier
     * @apiParam {string} session_id session_id
     * @apiParam {string} car_id 车辆Id
     * @apiVersion 2.5.4
     *
     * @apiParamExample {json} 请求样例
     *   POST /v4/UserCarPact/pactcreate
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "car_id":"",
     *
     *
     *     }
     *   }
     * @apiSuccess {json} pact_info 预约详情.
     * @apiSuccess {json} user_info 当前用户信息
     * @apiSuccess {json} seller_info 当前车主信息
     * @apiSuccess {json} car_info 车辆信息
     * @apiSuccess {string} pact_info.id pact_id 预约Id
     * @apiSuccess {string} pact_info.buyer_id buyer_id 预约用户Id
     * @apiSuccess {string} pact_info.seller_id seller_id 预约车主Id
     * @apiSuccess {string} pact_info.status 状态  0:买家点击预约 1:买家付款失败 2 买家付款成功 3:卖家已确认 4:双方履约(订单完成) 5:双方履约失败,6:客服(介入)(订单完成)
     *
     */
    public function PactCreateAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id','car_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $UserCarPact=new UserCarPactV1Model;

        $res=$UserCarPact->getPactbyUser($userId,$data['car_id']);

        if($res){
            $response['pact_info']=$res;
            $this->send($response);
            return;
        }

        $CS= new CarSellingV5Model();

        $CarInfo = $CS->GetCarInfoById($data['car_id']);

        $seller_id=$CarInfo['user_info']['user_id'];

        if($seller_id == $userId){

            $this->send_error(PACT_CAR_NOT_AUTH);
        }
        //判断是否
        $if_pact=$UserCarPact->SumSellerPact($seller_id);

        if($if_pact == 2){

            $this->send_error(SELLER_PACT_CAR_NOT_ALLOW);
        }

        $PactInfo = array();

        $time=time();
        $PactInfo['created']=$time;
        $PactInfo['updated']=$time;
        $PactInfo['buyer_id']=$userId;
        $PactInfo['seller_id']=$seller_id;
        $PactInfo['car_id']=$data['car_id'];
        $PactInfo['pact_no']=$this->generateOrderSn();

        $info=$UserCarPact->initProfile($PactInfo);

       // print_r($info);exit;

        $response['pact_info'] = $UserCarPact->getPactInfoByPactNo($PactInfo['pact_no']);
        $response['car_info']   =$CarInfo;

        $this->send($response);


    }
    /**
 *
 * @api {POST} /v5/UserCarPact/Pactpay 预约支付
 * @apiName UserCarPact pactpay
 * @apiGroup UserCarPact
 * @apiDescription
 * @apiPermission anyone
 * @apiSampleRequest http://testapi.bibicar.cn
 * @apiVersion 2.5.4
 * @apiParam {string} device_identifier device_identifier
 * @apiParam {string} session_id session_id
 * @apiParam {string} pact_id 预约Id
 * @apiParam {string} pay_type 支付方式 2:wxpay 1:alipay
 * @apiParam {string} pact_time 预约时间(10位时间戳)
 *
 *
 * @apiParamExample {json} 请求样例
 *   POST /v5/UserCarPact/pactpay
 *   {
 *     "data": {
 *       "device_identifier":"",
 *       "session_id":"",
 *       "pact_id":"",
 *
 *
 *     }
 *   }
 *
 * @apiSuccess {json} pact_info 预约详情.
 * @apiSuccess {json} user_info 当前用户信息
 * @apiSuccess {json} seller_info 当前车主信息
 * @apiSuccess {json} car_info 车辆信息
 * @apiSuccess {string} pact_info.id pact_id 预约Id
 * @apiSuccess {string} pact_info.buyer_id buyer_id 预约用户Id
 * @apiSuccess {string} pact_info.seller_id seller_id 预约车主Id
 * @apiSuccess {string} pact_info.status 状态  0:买家点击预约 1:买家付款失败 2 买家付款成功 3:卖家已确认 4:双方履约(订单完成) 5:双方履约失败,6:客服(介入)(订单完成)
 *
 */

    public function PactPayAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id','pact_id','pay_type','pact_time'));

        $data =$this->get_request_data();


        $userId = $this->userAuth($data);


        $UserCarPact=new UserCarPactV1Model();

        $pact_info=$UserCarPact->getPact($data['pact_id']);

        if($userId != $pact_info['buyer_id']){

            $this->send_error(PACT_CAR_NOT_AUTH);
        }


        if($data['pay_type'] == 2){


            $notify = new Wxpay();

            $info['pact_no']=$pact_info['pact_no'];

            $result=$notify->unifiedpact($info);

            if($result['result_code'] == 'FAIL'){

                $this->send_error($result['err_code_des']);


            }

            $sign=$notify->settoSign($result);

            if($sign == $result['sign']){
                $response['appid']    =$result['appid'];
                $response['partnerid']=$result['mch_id'];
                $response['noncestr']= WxPayApi::getNonceStr();
                $response['package']  ="Sign=WXPay";
                $response['prepayid']=$result['prepay_id'];
                $response['timestamp']=time();

                $update['pay_no'] = $result['prepay_id'];
                $update['pact_time']=$data['pact_time'];
                $update['pay_type'] = 2;
                $UserCarPact->updatePactByKey($data['pact_id'],$update);

                $sign=$notify->settoSign($response);
                $response['sign']=$sign;
                $response['type']="Wxpay";
                $this->send($response);
            }else{
                $this->send_error(CAR_CREATE_FILES_ERROR);

            }


        }else{

            $notifyUrl="https://api.bibicar.cn/v3/usercarpact/alinotify";
            $info['pact_no']=$pact_info['pact_no'];
            $alipayM=new Alipay();
            $order_sn=$pact_info['pact_no'];
            $order_amount=0.01;
            $goods_name='吡吡预约';
            $result=$alipayM->alipay($order_sn,$order_amount,$goods_name,$notifyUrl);
            $response['orderstr']=$result;
            $response['type']="Alipay";
            $update['pay_no'] = $order_sn;
            $update['pay_type'] = 1;
            $update['pact_time']=$data['pact_time'];
            $UserCarPact->updatePactByKey($data['pact_id'],$update);
            $this->send($response);

        }

    }

    /**
     *
     * @api {POST} /v5/UserCarPact/Pactpaycancel 支付取消
     * @apiName UserCarPact pactpaycancel
     * @apiGroup UserCarPact
     * @apiDescription
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.5.4
     *
     * @apiParam {string} device_identifier device_identifier
     * @apiParam {string} session_id session_id
     * @apiParam {string} pact_id 预约Id
     * @apiParam {string} pay_type 支付方式 2:wxpay 1:alipay
     * @apiParam {string} pact_time 预约时间(10位时间戳)
     *
     *
     * @apiParamExample {json} 请求样例
     *   POST /v5/UserCarPact/pactpay
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "pact_id":"",
     *
     *
     *     }
     *   }
     *
     *
     */

    public function PactPaycancelAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id','pact_id'));

        $data =$this->get_request_data();


        $userId = $this->userAuth($data);


        $UserCarPact=new UserCarPactV1Model();

        $pact_info=$UserCarPact->getPact($data['pact_id']);

        if($userId != $pact_info['buyer_id']){

            $this->send_error(PACT_CAR_NOT_AUTH);
        }

        if($pact_info['status'] != 0 ){

            $this->send_error(CANCEL_PACT_CAR_ERROR);
        }

        $update['pay_type']= 0;
        $update['updated']= time();

        $UserCarPact->updatePactByKey($data['pact_id'],$update);

        $response['return_code']='SUCCESS';

        $this->send($response);

    }


    /**
     * @api {POST} /v5/UserCarPact/Sellerconfirm 卖家确认预约
     * @apiName UserCarPact Sellerconfirm
     * @apiGroup UserCarPact
     * @apiDescription
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.5.4
     *
     * @apiParam {string} device_identifier device_identifier
     * @apiParam {string} session_id session_id
     * @apiParam {string} pact_id 预约Id
     * @apiParam {string} address 详细地址
     * @apiParam {string} pact_time 预约时间
     * @apiParam {string} lat 纬度
     * @apiParam {string} lng 经度
     *
     * @apiParamExample {json} 请求样例
     *   POST /v5/UserCarPact/pactcreate
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "pact_id":"",
     *     }
     *   }
     *
     *
     * @apiSuccess {json} pact_info 预约详情.
     * @apiSuccess {json} user_info 当前用户信息
     * @apiSuccess {json} seller_info 当前车主信息
     * @apiSuccess {json} car_info 车辆信息
     * @apiSuccess {string} pact_info.id pact_id 预约Id
     * @apiSuccess {string} pact_info.buyer_id buyer_id 预约用户Id
     * @apiSuccess {string} pact_info.seller_id seller_id 预约车主Id
     * @apiSuccess {string} pact_info.status 状态  0:买家点击预约 1:买家付款失败 2 买家付款成功 3:卖家已确认 4:双方履约(订单完成) 5:双方履约失败,6:客服(介入)(订单完成)
     *
     *
     *
     */

    public function SellerConfirmAction(){


        $this->required_fields = array_merge($this->required_fields,array('session_id','pact_id','address','lat','lng'));

        $data =$this->get_request_data();

        $userId = $this->userAuth($data);

        $UserCarPact=new UserCarPactV1Model();

        $pact_info=$UserCarPact->getPact($data['pact_id']);

        if($userId != $pact_info['seller_id']){
            $this->send_error(PACT_CAR_NOT_AUTH);
        }

        if($pact_info['status'] != 2){
            $this->send_error(CONFIRM_PACT_CAR_NOT_ALLOW);
        }

        $update['status']  = 3;
        $update['updated'] = time();
        $update['address'] = $data['address'];
        $update['pact_time'] = $data['pact_time'];
        $update['lat']     = $data['lat'];
        $update['lng']     = $data['lng'];

        $res = $UserCarPact->updatePactByKey($data['pact_id'],$update);

        $response = $UserCarPact->getPact($data['pact_id']);

        $this->send($response);

    }

    /**
     * @api {POST} /v5/UserCarPact/pactconfirm 确认对方履约
     * @apiName UserCarPact pactconfirm
     * @apiGroup UserCarPact
     * @apiDescription
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.5.4
     *
     * @apiParam {string} device_identifier device_identifier
     * @apiParam {string} session_id session_id
     * @apiParam {string} pact_id 预约Id
     * @apiParam {number} type 当前用户角色  1 buyer :买家2:seller 卖家
     * @apiParam {number} status  对方是否履约 1:是 2:否
     * @apiParam {string} des  履约原因描述
     * @apiParam {string} lat 纬度
     * @apiParam {string} lng 经度
     *
     * @apiParamExample {json} 请求样例
     *   POST /v5/UserCarPact/pactconfirm
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "pact_id":"",
     *
     *
     *     }
     *   }
     * @apiSuccess {json} pact_info 预约详情.
     * @apiSuccess {json} user_info 当前用户信息
     * @apiSuccess {json} seller_info 当前车主信息
     * @apiSuccess {json} car_info 车辆信息
     * @apiSuccess {string} pact_info.id pact_id 预约Id
     * @apiSuccess {string} pact_info.id buyer_des 买家履约详情描述 seller_des 卖家履约详情描述
     * @apiSuccess {string} pact_info.buyer_id buyer_id 预约用户Id
     * @apiSuccess {string} pact_info.seller_id seller_id 预约车主Id
     * @apiSuccess {string} pact_info.status 状态  0:买家点击预约 1:买家付款失败 2 买家付款成功 3:卖家已确认 4:双方履约(订单完成) 5:双方履约失败,6:客服(介入)(订单完成)
     *
     *
     *
     */

    public function PactConfirmAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id','pact_id','lat','lng','type','status'));

        $data =$this->get_request_data();

        $userId = $this->userAuth($data);

        $UserCarPact = new UserCarPactV1Model();

        $Pact_Info = $UserCarPact->getPact($data['pact_id']);

        if($Pact_Info['status'] != 3){

            $this->send_error(CONFIRM_PACT_CAR_NOT_NULL);
        }

        if($data['type'] == 1){

            if($Pact_Info['buyer_id'] != $userId){

                $this->send_error(PACT_CAR_NOT_AUTH);
            }

            if($Pact_Info['buyer_status'] == 1){

                if($data['status'] == 2){
                    $update['status'] = 5;
                }else{
                    $update['status'] = 4;

                }

            }elseif($Pact_Info['buyer_status'] == 2){

                $update['status'] =5;

            }

            $update['seller_status'] = $data['status'];

            if(@$data['des']){
                $update['buyer_des'] =$data['des'];
            }

            $update['buyer_lat']=$data['lat'];
            $update['buyer_lng']=$data['lng'];

        }else{

            if($Pact_Info['seller_id'] != $userId){

                $this->send_error(PACT_CAR_NOT_AUTH);
            }



            if($Pact_Info['seller_status'] == 1){

                if($data['status'] == 2){

                    $update['status'] = 5;
                }else{

                    $update['status'] = 4;

                }

            }elseif($Pact_Info['seller_status'] == 2){

                $update['status'] = 5;

            }

            $update['buyer_status'] = $data['status'];

            if(@$data['des']){
                $update['seller_des'] =$data['des'];
            }

            $update['seller_lat']=$data['lat'];
            $update['seller_lng']=$data['lng'];

        }



        $update['updated'] =time();

        $res= $UserCarPact->updatePactByKey($data['pact_id'],$update);

        if($update['status'] == 4){

            $this->BuyerRefundMoney($Pact_Info['id']);
            //自动退款给用户
        }


        $response = $UserCarPact->getPact($data['pact_id']);

        $this->send($response);






    }



    /**
     *
     */
    //微信回调通知
    public function wxnotifyAction(){

        Common::globalLogRecord ( 'remote_ip_wx', $_SERVER['REMOTE_ADDR'] );
        Common::globalLogRecord ( 'request_url_wx', 'http://'. $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] );

        //获取通知的数据
        //$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $xml =file_get_contents("php://input");
        //如果返回成功则验证签名
        $WxPayNotify=new WxPayNotify();
        try {
            $result =$WxPayNotify->HandleXML($xml);
            Common::globalLogRecord('wxpay_renotify',http_build_query($result));
        } catch (WxPayException $e){
            $msg = $e->errorMessage();
            return false;
        }

        if($result['result_code']=="SUCCESS"){

            $UserCarPact=new UserCarPactV1Model();
            $pact_info=$UserCarPact->getPactInfoByPactNo($result['out_trade_no']);

            if( $pact_info['status'] == 0 ){
                $update['status']  = 2;
                $update['updated'] = time();
                $UserCarPact->updatePactByKey($pact_info['id'],$update);
            }

            //给卖家发通知给卖家

            $msg = "OK";
            $WxPayNotify->SetReturn_code("SUCCESS");
            $WxPayNotify->SetReturn_msg($msg);

            WxpayApi::replyNotify($WxPayNotify->ToXml());

        }else{

            $UserCarPact=new UserCarPactV1Model();
            $pact_info=$UserCarPact->getPactInfoByPactNo($result['out_trade_no']);

            if( $pact_info['status'] == 0 ){
                $update['status']  = 1;
                $update['updated'] = time();
                $UserCarPact->updatePactByKey($pact_info['id'],$update);
            }

            $msg="error";
            $WxPayNotify->SetReturn_code("FAIL");
            $WxPayNotify->SetReturn_msg($msg);
            WxpayApi::replyNotify($WxPayNotify->ToXml());
        }

    }

    //支付宝回调通知
    public function alinotifyAction(){
        $data=$_REQUEST;
        Common::globalLogRecord ( 'remote_ip', $_SERVER['REMOTE_ADDR'] );
        Common::globalLogRecord ( 'request_url', 'http://'. $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] );
        Common::globalLogRecord ( 'request_args', http_build_query ( $data ) );

        //如果返回成功则验证签名
        $Alipay=new Alipay();

        $result=$Alipay->notify($data);
        Common::globalLogRecord ('request_result', $result );

        if($result){

            if($data['trade_status'] == "TRADE_SUCCESS"){

                $UserCarPact=new UserCarPactV1Model();
                $pact_info=$UserCarPact->getPactInfoByPactNo($data['out_trade_no']);

                if($pact_info['status'] == 0 ){
                    $update['status']  = 2;
                    $update['updated'] = time();
                    $update['pay_no']=$data['trade_no'];
                    $UserCarPact->updatePactByKey($pact_info['id'],$update);
                }


            }else if($data['trade_status'] == "TRADE_CLOSED"){


                $UserCarPact=new UserCarPactV1Model();
                $pact_info=$UserCarPact->getPactInfoByPactNo($data['out_trade_no']);

                if($pact_info['status'] == 0 ){
                    $update['status']  = 1;
                    $update['updated'] = time();
                    $update['pay_no']=$data['trade_no'];
                    $UserCarPact->updatePactByKey($pact_info['id'],$update);
                }


            }

            //发送通知给卖家
        }

    }

    public function generateOrderSn(){

        $yCode = array(1,2,3,4,5,6,7,8,9);
        $orderSn = $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));

        return $orderSn;

    }


    public function RechargeAction(){

        $wechat = new Wechat();

        $app = $wechat->getWechat();

        $payment = $app->payment;

        $transactionId = "4001962001201705191536277494";

        $refundNo      = "13545458545";

        $result = $payment->refundByTransactionId($transactionId, $refundNo, 100); // 总金额 100 退款 100，操作员：商户号

        print_r($result);exit;


   }


    /**
     * @api {POST} /v5/UserCarPact/withdraw  卖家支付预约
     * @apiName UserCarPact withdraw
     * @apiGroup UserCarPact
     * @apiDescription
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.5.4
     *
     * @apiParam {string} device_identifier device_identifier
     * @apiParam {string} session_id session_id
     * @apiParam {money} money 预约金(整数 单位：元)
     * @apiParam {string} pay_type 支付方式 2:wxpay 1:alipay
     *
     *
     * @apiParamExample {json} 请求样例
     *   POST /v5/UserCarPact/pactconfirm
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "money":"",
     *       "pay_type":"",
     *
     *     }
     *   }
     *
     *
     *
     */
   public function WithDrawAction(){

       $this->required_fields = array_merge($this->required_fields,array('session_id','money','pay_type'));

       $data =$this->get_request_data();

       $userId = $this->userAuth($data);

       $out_trade_no = $this->generateOrderSn();


       if($data['pay_type'] == 2){

           $money = $data['money'];

           $wechat = new Wechat();

           $app = $wechat->getWechat();


           $attributes = [
               'trade_type'       => 'APP', // JSAPI，NATIVE，APP...
               'body'             => '吡吡汽车预约支付服务',
               'detail'           => '吡吡汽车预约支付服务',
               'out_trade_no'     =>  $out_trade_no,
               'total_fee'        => $money, // 单位：分
               'notify_url'       => 'http://api.bibicar.cn/v3/usercarpact/withdrawwxnotify', // 支付结果通知网址，如果不设置则会使用配置里的默认地址
           ];
           $order = new Order($attributes);

           $payment = $app->payment;

           $result = $payment->prepare($order);

           if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS'){
               $prepayId = $result->prepay_id;

               $UserCarPactSeller=new UserCarPactSellerModel();

               $time = time();
               $PactInfo['created']=$time;
               $PactInfo['updated']=$time;
               $PactInfo['pay_no']=$prepayId;
               $PactInfo['pay_type']=2;
               $PactInfo['user_id']=$userId;
               $PactInfo['pact_no']=$out_trade_no;
               $PactInfo['status'] = 1;
               $PactInfo['money'] = $money;

               $info=$UserCarPactSeller->initProfile($PactInfo);

           }

           $config = $payment->configForAppPayment($prepayId);

           $response = $config;

           $response['type']="Wxpay";

           $this->send($response);

       }else{

           $UserCarPactSeller=new UserCarPactSellerModel();

           $notifyUrl="https://api.bibicar.cn/v3/usercarpact/selleralinotify";

           $alipayM=new Alipay();

           $order_sn=$out_trade_no;

           $order_amount=$data['money'];

           $goods_name='吡吡预约金';

           $result=$alipayM->alipay($order_sn,$order_amount,$goods_name,$notifyUrl);

           $response['orderstr']=$result;

           $response['type']="Alipay";

           $time = time();
           $PactInfo['created']=$time;
           $PactInfo['updated']=$time;
           $PactInfo['pay_type']=1;
           $PactInfo['user_id']=$userId;
           $PactInfo['pact_no']=$out_trade_no;
           $PactInfo['status'] = 1;
           $PactInfo['money'] = $order_amount;
           $info=$UserCarPactSeller->initProfile($PactInfo);

           $this->send($response);

       }

   }


    /**
     *卖家支付预约回调
     */

    public function WithdrawwxnotifyAction(){

       Common::globalLogRecord ( 'remote_ip_wx', $_SERVER['REMOTE_ADDR'] );
       Common::globalLogRecord ( 'request_url_wx', 'http://'. $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] );
       Common::globalLogRecord ( 'request', $_REQUEST );

       $wechat = new Wechat();

       $app = $wechat->getWechat();

       $response = $app->payment->handleNotify(function($notify, $successful){
           $out_trade_no  =$notify->out_trade_no;

           $UserCarPactSeller=new UserCarPactSellerModel();

           $pact = $UserCarPactSeller->getSellerPactInfoByPactNo($out_trade_no);

           if (!$pact) { // 如果订单不存在
               return 'Order not exist.'; // 告诉微信，我已经处理完了，订单没找到，别再通知我了
           }
           // 如果订单存在
           // 检查订单是否已经更新过支付状态
//           if ($pact['status'] == 2) { // 假设订单字段“支付时间”不为空代表已经支付
//               return true; // 已经支付成功了就不再更新了
//           }

           if($pact['status'] == 3){
               return true;
           }
           // 用户是否支付成功
           if ($successful) {
               // 不是已经支付状态则修改为已经支付状态
               $update['updated'] = time();

               $update['status']  = 3;

               $Profile = new ProfileModel();

               $Profile= $Profile->getProfile($pact['user_id']);

               $money  = $Profile['balance'] + $pact['money'];

               //$update['money']= $money;

               $pro_update['balance'] = $money;

               $Profiles = new ProfileModel();

               $Profiles->updateProfileByKey($pact['user_id'],$pro_update);

           } else { // 用户支付失败

               $update['updated'] =time();

               $update['status']  = 2;

           }
           $UserCarPactSeller->updatePactByKey($pact['id'],$update);

           return true; // 或者错误消息
       });

       return $response;

   }

    //支付宝回调通知
    public function SelleralinotifyAction(){


        $data=$_REQUEST;
        Common::globalLogRecord ( 'remote_ip', $_SERVER['REMOTE_ADDR'] );
        Common::globalLogRecord ( 'request_url', 'http://'. $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] );
        Common::globalLogRecord ( 'request_args', http_build_query ( $data ) );

        //如果返回成功则验证签名
        $Alipay=new Alipay();

        $result=$Alipay->alinotify($data);
        Common::globalLogRecord ('request_result', $result );

        if($result){

            if($data['trade_status'] == "TRADE_SUCCESS"){

                $UserCarPactSeller=new UserCarPactSellerModel();

                $pact = $UserCarPactSeller->getSellerPactInfoByPactNo($data['out_trade_no']);

                if( $pact['status'] == 1 ){

                    $update['status']  = 3;

                    $update['updated'] = time();

                    $update['pay_no']=$data['trade_no'];

                    $UserCarPactSeller->updatePactByKey($pact['id'],$update);

                    $Profile = new ProfileModel();

                    $Profile= $Profile->getProfile($pact['user_id']);

                    $money  = $Profile['balance'] + $pact['money'];

                    $update['money']= $money;

                    $pro_update['balance'] = $money;

                    $Profiles = new ProfileModel();

                    $Profiles->updateProfileByKey($pact['user_id'],$pro_update);
                }


            }else if($data['trade_status'] == "TRADE_CLOSED"){

                $UserCarPactSeller=new UserCarPactSellerModel();

                $pact = $UserCarPactSeller->getSellerPactInfoByPactNo($data['out_trade_no']);
                if( $pact['status'] == 1 ){

                    $update['status']  = 2;

                    $update['updated'] = time();

                    $update['pay_no']=$data['trade_no'];

                    $UserCarPactSeller->updatePactByKey($pact['id'],$update);

                }


            }





        }

    }
    
    /**
     * @api {POST} /v5/UserCarPact/buyerrefund 买家退款
     * @apiName UserCarPact buyerrefund
     * @apiGroup UserCarPact
     * @apiDescription
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.5.4
     *
     * @apiParam {string} device_identifier device_identifier
     * @apiParam {string} session_id session_id
     * @apiParam {number} pact_id 预约Id
     *
     *
     * @apiParamExample {json} 请求样例
     *   POST /v5/UserCarPact/pactconfirm
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "pact_id":"",
     *
     *     }
     *   }
     *
     *
     *
     */
   public function BuyerRefundAction(){


       $this->required_fields = array_merge($this->required_fields,array('session_id','pact_id'));

       $data =$this->get_request_data();

       $userId = $this->userAuth($data);

       $UserCarPact = new UserCarPactV1Model();

       $Pact_Info = $UserCarPact->getPact($data['pact_id']);

       if($Pact_Info){

               if($Pact_Info['buyer_id'] != $userId){

                   $this->send_error(PACT_CAR_NOT_AUTH);
               }

               if( $Pact_Info['status'] != 4 || $Pact_Info['status'] != 6){

                   $this->send_error(REFUND_PACT_CAR_ERROR);

               }

               //if($Pact_Info['pay_type']) 判断支付方式

               $wechat = new Wechat();

               $app = $wechat->getWechat();

               $payment = $app->payment;

               $transactionId = $Pact_Info['pay_no'];

               $refundNo      = time();

               $result = $payment->refundByTransactionId($transactionId, $refundNo,10000); // 总金额 100 退款 100，操作员：商户号

               if($result['return_code'] == 'SUCCESS'){

                   $update['refund_no']=$result['refund_id'];

                   $update['status']   = 7;

                   $UserCarPact->updatePactByKey($data['pact_id'],$update);

                   $Profile = new ProfileModel();

                   $Profile= $Profile->getProfile($Pact_Info['buyer_id']);

                   $money  = $Profile['balance'] - 100;

                   $pro_update['balance'] = $money;

                   $Profiles = new ProfileModel();

                   $Profiles->updateProfileByKey($Pact_Info['buyer_id'],$pro_update);

                   $this->send($result);

               }else{

                   $this->send_error(REFUND_PACT_CAR_ERROR);
               }

       }else{

           $this->send_error(REFUND_PACT_CAR_ERROR);

       }
   }

    /**
     * @api {POST} /v5/UserCarPact/sellerrefund 卖家退款
     * @apiName UserCarPact sellerrefund
     * @apiGroup UserCarPact
     * @apiDescription
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.5.4
     *
     * @apiParam {string} device_identifier device_identifier
     * @apiParam {string} session_id session_id
     *
     *
     * @apiParamExample {json} 请求样例
     *   POST /v5/UserCarPact/pactconfirm
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *
     *
     *     }
     *   }
     *
     *
     *
     */
    public function SellerRefundAction(){


        $this->required_fields = array_merge($this->required_fields,array('session_id'));

        $data =$this->get_request_data();

        $userId = $this->userAuth($data);

        $UserCarPactSeller = new UserCarPactSellerModel();

        $UserCarPact = new UserCarPactV1Model();

        $Pact_Info = $UserCarPactSeller->getSellerPactList($userId);

        if($Pact_Info){

            if($Pact_Info[0]['user_id'] != $userId){

                $this->send_error(PACT_CAR_NOT_AUTH);
            }

            $Pactlist = $UserCarPact->getSellerPactCarList($userId);

            if($Pactlist){

                foreach($Pactlist as $k => $pact ){

                        if($pact['status']  != 4 || $pact['status']  != 6 ){
                           $this->send_error(REFUND_PACT_CAR_FAIL);
                           return;
                        }
                }

            }
            $code = 1;
            foreach($Pact_Info as $k => $pact ){

                if($pact['status'] == 3 || $pact['status'] == 5){

                    if($pact['pay_type'] == 2){
                        //weixin
                        $wechat = new Wechat();

                        $app = $wechat->getWechat();

                        $payment = $app->payment;

                        $transactionId = $pact['pay_no'];
                        $orderNo = $pact['pact_no'];

                        $refundNo      = time();

                        $result = $payment->refund($orderNo, $refundNo, $pact['money']); // 总金额 100 退款 100，操作员：商户号

                        //  $result = $payment->refundByTransactionId($transactionId, $refundNo,$pact['money']); // 总金额 100 退款 100，操作员：商户号

                        if($result['return_code'] == 'SUCCESS'){
                            //result_code  FAIL

                            if($result['result_code'] == "SUCCESS"){

                                $update['refund_no']=$result['refund_id'];

                                $update['status']   = 4;

                                $UserCarPactSeller->updatePactByKey($pact['id'],$update);

                                $Profile = new ProfileModel();

                                $Profile = $Profile->getProfile($pact['user_id']);

                                $money   = $Profile['balance'] - $pact['money'];

                                $pro_update['balance'] = $money;

                                $Profiles = new ProfileModel();

                                $Profiles->updateProfileByKey($pact['user_id'],$pro_update);

                                // $this->send($result);

                            }else{

                                $code = 2;

                                $update['refund_no']=$result['refund_id'];

                                $update['status']   = 5;

                                $UserCarPactSeller->updatePactByKey($pact['id'],$update);

                                //$this->send($result);
                            }

                        }else{

                            $code = 2;

                            $update['refund_no']=$result['refund_id'];

                            $update['status']   = 5;

                            $UserCarPactSeller->updatePactByKey($pact['id'],$update);

                            //$this->send($result);
                        }

                    }else{

                          //alipay

                        $Alipay=new Alipayment();

                        $order_sn = $pact['pact_no'];

                        $trade_no =$pact['pay_no'];

                        $refund_amount = $pact['money'];

                        $refundNo = time() . rand(1000, 9999);

                        $result = $Alipay->alirefund($order_sn,$trade_no,$refund_amount, $refundNo );

                        if($result['code'] == '10000' && $result['msg'] == "Success"){

                            $update['refund_no']= $refundNo;

                            $update['status']   = 4;

                            $UserCarPactSeller->updatePactByKey($pact['id'],$update);

                            $Profile = new ProfileModel();

                            $Profile= $Profile->getProfile($pact['user_id']);

                            $money  = $Profile['balance'] - $pact['money'];

                            $pro_update['balance'] = $money;

                            $Profiles = new ProfileModel();

                            $Profiles->updateProfileByKey($pact['user_id'],$pro_update);
                            //成功

                        }else{
                            $code = 2;
                        }


                    }



                }


            }

            if($code == 1){

                $response['msg'] ="Success";

                $this->send($response);
            }else{
                $this->send_error(REFUND_PACT_CAR_ERROR);
            }

        }else{

            $this->send_error(REFUND_PACT_CAR_ERROR);
        }


    }


    /**
     * @api {POST} /v5/UserCarPact/sellerpactlist 卖家预约金列表
     * @apiName UserCarPact SellerPactList
     * @apiGroup UserCarPact
     * @apiDescription
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.5.4
     *
     * @apiParam {string} device_identifier device_identifier
     * @apiParam {string} session_id session_id
     *
     *
     * @apiParamExample {json} 请求样例
     *   POST /v5/UserCarPact/pactconfirm
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *
     *
     *     }
     *   }
     *
     *
     *
     */
    public function SellerPactListAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id'));

        $data =$this->get_request_data();

        $userId = $this->userAuth($data);

        $UserCarPactSeller = new UserCarPactSellerModel();

        $Pact_Info = $UserCarPactSeller->getSellerPactList($userId);

        $this->send($Pact_Info);

    }

    /**
     * @api {POST} /v5/UserCarPact/buyerpactlist 买家预约订单列表
     * @apiName UserCarPact buyerPactList
     * @apiGroup UserCarPact
     * @apiDescription
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.5.4
     *
     * @apiParam {string} device_identifier device_identifier
     * @apiParam {string} session_id session_id
     *
     *
     * @apiParamExample {json} 请求样例
     *   POST /v5/UserCarPact/pactconfirm
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *
     *
     *     }
     *   }
     *
     *
     *
     */
   public function BuyerPactlistAction(){


       $this->required_fields = array_merge($this->required_fields,array('session_id'));

       $data =$this->get_request_data();

       $userId = $this->userAuth($data);

       $UserCarPact = new UserCarPactV1Model();

       $UserCarPact->type = 1;

       $pactlist = $UserCarPact->getUserPactList($userId);

       $this->send($pactlist);

   }


    /**
     * @api {POST} /v5/UserCarPact/getusermoney 获取余额
     * @apiName UserCarPact getusermoney
     * @apiGroup UserCarPact
     * @apiDescription
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.5.4
     *
     * @apiParam {string} device_identifier device_identifier
     * @apiParam {string} session_id session_id
     *
     *
     * @apiParamExample {json} 请求样例
     *   POST /v5/UserCarPact/pactconfirm
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *
     *
     *     }
     *   }
     *
     *
     *
     */
    public function getUserMoneyAction(){


        $this->required_fields = array_merge($this->required_fields,array('session_id'));

        $data =$this->get_request_data();

        $userId = $this->userAuth($data);

        $Profile = new ProfileModel();

        $money = $Profile->getUserMoney($userId);

        $this->send($money);


    }


    public function BuyerRefundMoney($pact_id){

        $UserCarPact = new UserCarPactV1Model();

        $Pact_Info = $UserCarPact->getPact($pact_id);

        if($Pact_Info){


            if( $Pact_Info['status'] == 4 || $Pact_Info['status'] == 6 ){


                    if($Pact_Info['pay_type'] == 2){


                        $wechat = new Wechat();

                        $app = $wechat->getWechat();

                        $payment = $app->payment;

                        $orderNo  = $Pact_Info['pact_no'];

                        $refundNo      = time();

                        $result = $payment->refund($orderNo, $refundNo, 1); // 总金额 100 退款 100，操作员：商户号

                        if($result['return_code'] == 'SUCCESS'){

                            if($result['result_code'] == "SUCCESS") {

                                $update['refund_no'] = $result['refund_id'];

                                $update['status'] = 7;

                                $UserCarPact->updatePactByKey($pact_id, $update);


                            }

                        }


                    }else{

                        //alipay

                        $Alipay=new Alipayment();

                        $order_sn = $Pact_Info['pact_no'];

                        $trade_no =$Pact_Info['pay_no'];

                        $refund_amount =0.01;

                        $refundNo = time() . rand(1000, 9999);

                        $result = $Alipay->alirefund($order_sn,$trade_no,$refund_amount,$refundNo);

                        if($result['code'] == '10000' && $result['msg'] == "Success"){

                            $update['refund_no']= $refundNo;

                            $update['status']   = 7;

                            $UserCarPact->updatePactByKey($pact_id,$update);


                        }
                    }

            }else{

                $this->send_error(REFUND_PACT_CAR_ERROR);

            }


        }else{

            $this->send_error(REFUND_PACT_CAR_ERROR);

        }
    }














}