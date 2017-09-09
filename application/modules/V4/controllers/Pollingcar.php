<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/13
 * Time: 下午6:09
 */
use EasyWeChat\Foundation\Application;
use EasyWeChat\Payment\Order;
class PollingCarController extends ApiYafControllerAbstract
{

    /**
     * @api {POST} /v4/PollingCar/getBrand  获取车车当品牌Id
     * @apiName PollingCar  getPollingCarBrand
     * @apiGroup PollingCar
     * @apiDescription 获取车车当品牌
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 1.0.0
     *
     * @apiParam {string} [device_identifier] 设备唯一标识
     * @apiParam {string} [session_id] session_id
     *
     *
     * @apiParamExample {json} 请求样例
     *   POST /v4/car/getBrand
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *
     *     }
     *   }
     *
     */
    public function getbrandAction(){

        $this->required_fields = array_merge($this->required_fields, array('session_id'));

        $data = $this->get_request_data();

        $PollingCarM =new PollingCarModel();

        $brand_list = $PollingCarM->geChedangBrandList();

        $this->send($brand_list);

    }

    /**
     * @api {POST} /v4/PollingCar/PollingCarPay 支付检测费用
     * @apiName PollingCar  PollingCarPay
     * @apiGroup PollingCar
     * @apiDescription 支付检测费用
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 1.0.0
     *
     * @apiParam {string} device_identifier 设备唯一标识
     * @apiParam {string} session_id session_id
     * @apiParam {number} pay_type 支付类型 1：支付宝 2：微信
     * @apiParam {string} vin 车架号
     * @apiParam {number} brand_id 车当品牌Id
     *
     *
     * @apiParamExample {json} 请求样例
     *   POST /v4/car/getPollingCarBrand
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *
     *     }
     *   }
     *
     */
    public function PollingCarPayAction(){

        $this->required_fields = array_merge($this->required_fields, array('session_id','pay_type','vin','brand_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $order_sn = $this->createorder_sn();

        $pay_fee =25;

        if($data['pay_type'] == 2){

            $wechat = new Wechat();

            $app    = $wechat->getWechat();

            $payment = $app->payment;

            $attributes = [
                'trade_type'       => 'APP', // JSAPI，NATIVE，APP...
                'body'             => '车辆检测报告',
                'detail'           => '车辆检测报告',
                'out_trade_no'     => $order_sn,
                'total_fee'        => $pay_fee *100, // 单位：分
                'notify_url'       => 'http://testapi.bibicar.cn/v4/car/pollingwxnotify', // 支付结果通知网址，如果不设置则会使用配置里的默认地址
                // 'sub_openid'        => '当前用户的 openid', // 如果传入sub_openid, 请在实例化Application时, 同时传入$sub_app_id, $sub_merchant_id
                // ...
            ];
            $order = new Order($attributes);
            $result = $payment->prepare($order);

            if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS'){
                $prepayId = $result->prepay_id;
                $config = $payment->configForAppPayment($prepayId);
                $config['type']="Wxpay";

                $pdo = new PdoDb;
                $where['created_at']=time();
                $where['status']=1;
                $where['vin']=$data['vin'];
                $where['brand_id']=$data['brand_id'];
                $where['user_id']= $userId;
                $where['report_sn']= $order_sn;
                $where['pay_type']=2;
                $where['pay_fee']=$pay_fee;

                $pdo->insert('bibi_chedang_report',$where);
                $config['report_sn']=$order_sn;
                return $this->send($config);
            }

        }else{

            $notifyUrl="https://testapi.bibicar.cn/v4/car/pollingalinotify";
            $alipayM=new Alipay();
            $order_amount=$pay_fee;
            $goods_name='车辆检测报告';
            $result=$alipayM->alipay($order_sn,$order_amount,$goods_name,$notifyUrl);
            $response['orderstr']=$result;
            $response['type']="Alipay";

            $pdo = new PdoDb;
            $where['created_at']=time();
            $where['status']=1;
            $where['vin']=$data['vin'];
            $where['brand_id']=$data['brand_id'];
            $where['user_id']= $userId;
            $where['report_sn']= $order_sn;
            $where['pay_type']=1;
            $where['pay_fee']=$pay_fee;

            $pdo->insert('bibi_chedang_report',$where);

            $response['report_sn']=$order_sn;

            $this->send($response);

        }
    }


    /**
     * @api {POST} /v4/PollingCar/PollingCarList  查询历史
     * @apiName PollingCar  PollingCarList
     * @apiGroup PollingCar
     * @apiDescription 查询历史
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 1.0.0
     *
     * @apiParam {string} [device_identifier] 设备唯一标识
     * @apiParam {string} [session_id] session_id
     *
     * @apiParamExample {json} 请求样例
     *   POST /v4/PollingCar/PollingCarList
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *     }
     *   }
     *
     */
    public function PollingCarListAction(){

        $this->required_fields = array_merge($this->required_fields, array('session_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $PollingCarM =new PollingCarModel();

        $lists = $PollingCarM->getReportList($userId);

        foreach($lists as $k =>$val){

            $lists[$k]['report'] =json_decode($val['report']);
        }
        $this->send($lists);

    }



    /**
     * @api {POST} /v4/PollingCar/PollingCarPort  查看车辆检修报告
     * @apiName PollingCar  PollingCarPort
     * @apiGroup PollingCar
     * @apiDescription 车辆检修生成报告
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 1.0.0
     *
     * @apiParam {string} [device_identifier] 设备唯一标识
     * @apiParam {string} [session_id] session_id
     * @apiParam {string} [report_sn] 报告单号
     *
     * @apiParamExample {json} 请求样例
     *   POST /v4/PollingCar/PollingCarVin
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *     }
     *   }
     *
     */
    public function PollingCarPortAction(){

        $this->required_fields = array_merge($this->required_fields, array('session_id','report_sn'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        //检查是否支付成功
        $PollingCarM =new PollingCarModel();
        $report = $PollingCarM->getReport($data['report_sn']);

        if( $report &&  $report['status'] != 2){
            $this->send_error(RENTAL_USER_AUTH_NO_ALOW);
        }

        if(!$report['apply_id']){

            $this->send_error(NOT_FOUND);
        }

        if($report['user_id'] != $userId){

            $this->send_error(PACT_CAR_NOT_AUTH);
        }

       // $report['apply_id']=15900;

        $str='number=ip46a71&key=81f6ed334a34150fe78de9d376be899c7a512709';

        $secret_key =md5($str);
        // 15900
        $url ='http://120.24.3.137/outapi/v1/get_report?secret_key='.$secret_key."&partner_number=ip46a71&apply_id=".$report['apply_id'];

        $html=file_get_contents($url);

        $result=json_decode($html,true);

        if($result['status'] == "success"){

            $pdo = new PdoDb;

            $where['apply_id'] = $data['apply_id'];

            $report['report']=json_encode($result['data']);

            $pdo->updateByPrimaryKey('bibi_chedang_report',$where,$report);

        }else{

            $this->send($result);
        }

    }

    public function getUserPollingReport(){

        $this->required_fields = array_merge($this->required_fields, array('session_id','report_sn'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

    }


    public function PolingCarCallbackAction(){
        $apply_id = $_GET['apply_id'];
        $status   = $_GET['status'];
        if($status == 101){
            //已生成报告
            $this->BuildReport($apply_id);
        }else if($status == 104){
            //该车辆未在4S店有保养记录
            $pdo = new PdoDb;
            $where['apply_id'] = $apply_id;
            $report['status']= 4;
            $pdo->updateByPrimaryKey('bibi_chedang_report',$where,$report);
        }else{
            //其他异常
            $pdo = new PdoDb;
            $where['apply_id'] = $apply_id;
            $report['status']= 5;
            $pdo->updateByPrimaryKey('bibi_chedang_report',$where,$report);
        }
        return true;
    }

    public function BuildReport($apply_id){

        $str='number=ip46a71&key=81f6ed334a34150fe78de9d376be899c7a512709';
        $secret_key =md5($str);
        //详细报告
        // 15900
        $url ='http://120.24.3.137/outapi/v1/get_report?secret_key='.$secret_key."&partner_number=ip46a71&apply_id=".$apply_id;
        $html=file_get_contents($url);
        $result=json_decode($html,true);

        if($result['status'] == "success"){
            $pdo = new PdoDb;
            $where['apply_id'] = $apply_id;
            $update['report']=json_encode($result['data'], JSON_UNESCAPED_UNICODE);
            $update['status']=3;
            $pdo->updateByPrimaryKey('bibi_chedang_report',$where,$update);
        }else{
            return $result;
        }
    }


    //微信回调通知
    public function pollingwxnotifyAction(){

        Common::globalLogRecord ( 'remote_ip_wx', $_SERVER['REMOTE_ADDR'] );
        Common::globalLogRecord ( 'request_url_wx', 'http://'. $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] );

        $wechat = new Wechat();

        $app    = $wechat->getWechat();

        $payment = $app->payment;

        $response = $app->payment->handleNotify(function($notify, $successful){
            // 使用通知里的 "微信支付订单号" 或者 "商户订单号" 去自己的数据库找到订单
            // $order = 查询订单($notify->out_trade_no);
            $PollingCarM =new PollingCarModel();
            $report = $PollingCarM->getReport($notify->out_trade_no);
            if (!$report) { // 如果订单不存在
                return 'Order not exist.'; // 告诉微信，我已经处理完了，订单没找到，别再通知我了
            }
            // 如果订单存在
            // 检查订单是否已经更新过支付状态
            if ($report['status'] == 2) { // 假设订单字段“支付时间”不为空代表已经支付
                return true; // 已经支付成功了就不再更新了
            }
            // 用户是否支付成功
            if ($successful) {
                // 不是已经支付状态则修改为已经支付状态

                $pdo = new PdoDb;

                $where['report_sn'] =$notify->out_trade_no;

                $update['status']=2;

                $pdo->updateByPrimaryKey('bibi_chedang_report',$where,$update);

                //生成报告
                $this->PollingCarApply($notify->out_trade_no);


            } else { // 用户支付失败

                $pdo = new PdoDb;
                $where['report_sn'] =$notify->out_trade_no;
                $update['status']=3;
                $pdo->updateByPrimaryKey('bibi_chedang_report',$where,$update);

            }

            return true; // 返回处理完成
        });
        return $response;

    }
    //支付宝回调通知
    public function pollingalinotifyAction(){
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

                $PollingCarM =new PollingCarModel();
                $report = $PollingCarM->getReport($data['out_trade_no']);


                if($report ['status'] == 1 ){

                    $pdo = new PdoDb;

                    $where['report_sn'] =$data['out_trade_no'];

                    $report['status']=2;

                    $pdo->updateByPrimaryKey('bibi_chedang_report',$where,$report);

                    //生成报告
                    $this->PollingCarApply($data['out_trade_no']);

                }
            }else if($data['trade_status'] == "TRADE_CLOSED"){

                $PollingCarM =new PollingCarModel();
                $report = $PollingCarM->getReport($data['out_trade_no']);

                if($report ['status'] == 1 ){
                    $pdo = new PdoDb;

                    $where['report_sn'] =$data['out_trade_no'];

                    $report['status']=3;

                    $pdo->updateByPrimaryKey('bibi_chedang_report',$where,$report);
                }

            }
            return true;
        }

    }

    public function PollingCarApply($report_sn){

        $PollingCarM =new PollingCarModel();
        $report = $PollingCarM->getReport($report_sn);

        $str='number=ip46a71&key=81f6ed334a34150fe78de9d376be899c7a512709';
        $secret_key =md5($str);

        $url ='http://120.24.3.137/outapi/v1/vin_search?secret_key='.$secret_key."&partner_number=ip46a71&vin=".$report['vin']."&brand_id=".$report['brand_id'];
        //详细报告
        // $url ='http://120.24.3.137/outapi/v1/get_report?secret_key='.$secret_key."&partner_number=ip46a71&apply_id=15900";
        $html=file_get_contents($url);
        $result=json_decode($html,true);
        if($result['status'] == "success" ){
            $pdo = new PdoDb;
            $where['report_sn'] = $report_sn;
            $update['apply_id']=$result['apply_id'];
            $pdo->updateByPrimaryKey('bibi_chedang_report',$where,$update);
        }

    }

    public function PollingCarBrand($brand_id,$vin){


        $str='number=ip46a71&key=81f6ed334a34150fe78de9d376be899c7a512709';

        $secret_key =md5($str);

        // $secret_key='81f6ed334a34150fe78de9d376be899c7a512709';

        //品牌检查

        $url ='http://120.24.3.137/outapi/v1/check_brand?secret_key='.$secret_key."&partner_number=ip46a71&vin=LGBP12E21DY196239";

        //vin码查询接口

        // $url ='http://120.24.3.137/outapi/v1/vin_search?secret_key='.$secret_key."&partner_number=ip46a71&vin=LGBP12E21DY196239&brand_id=4";

        //详细报告

        // $url ='http://120.24.3.137/outapi/v1/get_report?secret_key='.$secret_key."&partner_number=ip46a71&apply_id=15900";

        $html=file_get_contents($url);
        $data=json_decode($html,true)['data'];
    }

    //废弃
    public function PollingCarApply123(){

        $this->required_fields = array_merge($this->required_fields, array('session_id','report_sn'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        //检查是否支付成功
        $PollingCarM =new PollingCarModel();
        $report = $PollingCarM->getReport($data['report_sn']);

        if( $report &&  $report['status'] != 2){
            $this->send_error(RENTAL_USER_AUTH_NO_ALOW);
        }

        if($report['user_id'] != $userId){

            $this->send_error(PACT_CAR_NOT_AUTH);
        }

        $str='number=ip46a71&key=81f6ed334a34150fe78de9d376be899c7a512709';
        $secret_key =md5($str);

//        $report['vin']="LGBP12E21DY196239";
//
//        $report['brand_id']=5;

        $url ='http://120.24.3.137/outapi/v1/vin_search?secret_key='.$secret_key."&partner_number=ip46a71&vin=".$report['vin']."&brand_id=".$report['brand_id'];
        //详细报告
        // $url ='http://120.24.3.137/outapi/v1/get_report?secret_key='.$secret_key."&partner_number=ip46a71&apply_id=15900";
        $html=file_get_contents($url);
        $result=json_decode($html,true);
        if($result['status'] == "success" ){
            $pdo = new PdoDb;
            $where['report_sn'] = $data['report_sn'];
            $update['apply_id']=$result['apply_id'];
            $pdo->updateByPrimaryKey('bibi_chedang_report',$where,$update);
            $this->send($result);
        }else{
            $this->send($result);
        }
    }

}
