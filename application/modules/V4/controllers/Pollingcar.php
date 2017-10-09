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
     * @api {POST} /v4/PollingCar/PollingCarPay 查维保（支付费用）
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
                'notify_url'       => 'http://api.bibicar.cn/v4/pollingcar/pollingwxnotify', // 支付结果通知网址，如果不设置则会使用配置里的默认地址
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
                $where['type']=1;

                $pdo->insert('bibi_chedang_report',$where);
                $config['report_sn']=$order_sn;
                return $this->send($config);
            }

        }else{

            $notifyUrl="https://api.bibicar.cn/v4/pollingcar/pollingalinotify";
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
            $where['type']=1;

            $pdo->insert('bibi_chedang_report',$where);

            $response['report_sn']=$order_sn;

            $this->send($response);

        }
    }

    /**
     * @api {POST} /v4/pollingcar/PollingCarList  维保列表
     * @apiName pollingcar PollingCarList
     * @apiGroup PollingCar
     * @apiDescription 维保列表
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.0.0
     *
     * @apiParam {string} device_identifier 设备唯一标识
     * @apiParam {string} session_id session_id
     * @apiParam {string} page 页码
     *
     * @apiParamExample {json} 请求样例
     *   POST /v4/pollingcar/PollingCarList
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *
     *     }
     *   }
     *
     * @apiSuccess {number} report.status 1:待支付  2:支付成功(报告生成中) 3:支付失败 4:报告已生成 5:报告异常
     *
     */
    public function PollingCarListAction(){

        $this->required_fields = array_merge($this->required_fields, array('session_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $PollingCarM =new PollingCarModel();

        $PollingCarM->page= $data['page'] ? ($data['page']+1) : 1 ;

        $lists = $PollingCarM->getReportList($userId,1);

        foreach($lists["list"] as $k =>$val){

            $lists["list"][$k]['report'] =json_decode($val['report']);
        }
        $this->send($lists);

    }


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

        $str='number=ip46a71&key=81f6ed334a34150fe78de9d376be899c7a512709';

        $secret_key =md5($str);
        // 15900
        $url ='http://120.24.3.137/outapi/v1/get_report?secret_key='.$secret_key."&partner_number=ip46a71&apply_id=".$report['apply_id'];

        $html=file_get_contents($url);

        $result=json_decode($html,true);

        if($result['status'] == "success"){

            $pdo = new PdoDb;

            $where['apply_id'] = $report['apply_id'];

            $report['report']=json_encode($result);

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

        if($status == 101 && $apply_id){
            //已生成报告
            $this->BuildReport($apply_id);
        }else if($status == 104){
            //该车辆未在4S店有保养记录
            $pdo = new PdoDb;
            $where['apply_id'] = $apply_id;
            $report['status']= 5;
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
            $update['report']=json_encode($result, JSON_UNESCAPED_UNICODE);
            $update['status']=4;
            $pdo->updateByPrimaryKey('bibi_chedang_report',$where,$update);
        }else{
            $pdo = new PdoDb;
            $where['apply_id'] = $apply_id;
            $update['report']=json_encode($result, JSON_UNESCAPED_UNICODE);
            $update['status']=5;
            $pdo->updateByPrimaryKey('bibi_chedang_report',$where,$update);
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
            $where['report'] =json_encode($result, JSON_UNESCAPED_UNICODE);
            $update['apply_id']=$result['apply_id'];
            $pdo->updateByPrimaryKey('bibi_chedang_report',$where,$update);
        }else{
            $pdo = new PdoDb;
            $where['report_sn'] = $report_sn;
            $where['report'] = json_encode($result, JSON_UNESCAPED_UNICODE);
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


    /**
     * @api {POST} /v4/PollingCar/CheckIns 查出险(支付)
     * @apiName PollingCar  CheckIns
     * @apiGroup PollingCar
     * @apiDescription 查出险(支付)
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 1.0.0
     *
     * @apiParam {string} device_identifier 设备唯一标识
     * @apiParam {string} session_id session_id
     * @apiParam {number} pay_type 支付类型 1：支付宝 2：微信
     * @apiParam {number} s_type  1：vin码搜索 2：车牌号搜索
     * @apiParam {string} [vin] vin码 s_type = 1必须
     * @apiParam {string} [plate] 车牌号 s_type=2 必须
     *
     *
     * @apiParamExample {json} 请求样例
     *   POST /v4/car/getPollingCarBrand
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "vin": "LGBP12E21DY196239/LSVCB2NP8GN018247/LGBH12E27DY161905",
     *       "plate":"粤B1P8V8/豫SD6258/粤B12345/粤B6KB87",
     *
     *     }
     *   }
     *
     */
    public function CheckInsAction(){

        $this->required_fields = array_merge($this->required_fields, array('session_id','pay_type','s_type'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);


        if($data['s_type'] == 1 ){

              if(!$data['vin']){
                  $this->send_error(NOT_ENOUGH_ARGS);
              }
        }else{

            if(!$data['plate']){
                $this->send_error(NOT_ENOUGH_ARGS);
            }
        }

        $order_sn = $this->createorder_sn();

        $pay_fee =0.01;

        if($data['pay_type'] == 2){

            $wechat = new Wechat();

            $app    = $wechat->getWechat();

            $payment = $app->payment;

            $attributes = [
                'trade_type'       => 'APP', // JSAPI，NATIVE，APP...
                'body'             => '车辆出险报告',
                'detail'           => '车辆出险报告',
                'out_trade_no'     => $order_sn,
                'total_fee'        => $pay_fee *100, // 单位：分
                'notify_url'       => 'http://api.bibicar.cn/v4/pollingcar/inswxnotify', // 支付结果通知网址，如果不设置则会使用配置里的默认地址
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
                $where['vin']=@$data['vin'];
                $where['plate']=@$data['plate'];
                $where['user_id']= $userId;
                $where['report_sn']= $order_sn;
                $where['pay_type']=2;
                $where['pay_fee']=$pay_fee;
                $where['type']=2;

                $pdo->insert('bibi_chedang_report',$where);
                $config['report_sn']=$order_sn;
                return $this->send($config);
            }

        }else{

            $notifyUrl="https://api.bibicar.cn/v4/pollingcar/insalinotify";
            $alipayM=new Alipay();
            $order_amount=$pay_fee;
            $goods_name='车辆出险报告';
            $result=$alipayM->alipay($order_sn,$order_amount,$goods_name,$notifyUrl);
            $response['orderstr']=$result;
            $response['type']="Alipay";

            $pdo = new PdoDb;
            $where['created_at']=time();
            $where['status']=1;
            $where['vin']=@$data['vin'];
            $where['plate']=@$data['plate'];
            $where['user_id']= $userId;
            $where['report_sn']= $order_sn;
            $where['pay_type']=1;
            $where['pay_fee']=$pay_fee;
            $where['type']=2;

            $pdo->insert('bibi_chedang_report',$where);

            $response['report_sn']=$order_sn;

            $this->send($response);

        }
    }


    /**
     * @api {POST} /v4/pollingcar/Inslist  出险列表
     * @apiName pollingcar Inslist
     * @apiGroup PollingCar
     * @apiDescription 查询出险列表
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.0.0
     *
     * @apiParam {string} device_identifier 设备唯一标识
     * @apiParam {string} session_id session_id
     * @apiParam {string} page 页码
     *
     * @apiParamExample {json} 请求样例
     *   POST /v4/pollingcar/Inslist
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *
     *     }
     *   }
     *
     * @apiSuccess {number} report.status 1:待支付  2:支付成功(报告生成中) 3:支付失败 4:报告已生成 5:报告异常
     *
     */
    public function InslistAction()
    {

        $this->required_fields = array_merge($this->required_fields, array('session_id','page'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $PollingCarM = new PollingCarModel();

        $PollingCarM->page= $data['page'] ? ($data['page']+1) : 1 ;

        $lists = $PollingCarM->getReportList($userId,2);

        foreach ($lists["list"] as $k => $val) {

            $lists["list"][$k]['report'] = json_decode($val['report']);
        }
        $this->send($lists);

    }


    //微信回调通知
    public function inswxnotifyAction(){

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

                //生成报告（待做成异步）
                $this->ApplyIns($notify->out_trade_no);

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
    public function insalinotifyAction(){
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

                    //生成报告（待做成异步）
                    $this->ApplyIns($data['out_trade_no']);

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


    public function ApplyIns($report_sn){

        $PollingCarM =new PollingCarModel();
        $report = $PollingCarM->getReport($report_sn);

        $str='number=ip46a71&key=81f6ed334a34150fe78de9d376be899c7a512709';
        $secret_key =md5($str);

        if($report['vin']){

            $url ='http://120.24.3.137/outapi/v1/insures?secret_key='.$secret_key."&partner_number=ip46a71&s_type=1&vin=".$report['vin'];


        }else if($report['plate']){

            $url ='http://120.24.3.137/outapi/v1/insures?secret_key='.$secret_key."&partner_number=ip46a71&s_type=2&plate=".$report['plate'];

        }
        $html=file_get_contents($url);

        $result=json_decode($html,true);

        if($result['status'] == "success"){
            $pdo = new PdoDb;
            $where['report_sn'] = $report_sn;
            $update['report']=json_encode($result, JSON_UNESCAPED_UNICODE);
            $update['status']=4;
            $pdo->updateByPrimaryKey('bibi_chedang_report',$where,$update);
        }else{
            $pdo = new PdoDb;
            $where['report_sn'] = $report_sn;
            $update['report']=json_encode($result, JSON_UNESCAPED_UNICODE);
            $update['status']=5;
            $pdo->updateByPrimaryKey('bibi_chedang_report',$where,$update);
        }

    }

   //查出现
   public function  ApplyInsAction(){


       $str='number=ip46a71&key=81f6ed334a34150fe78de9d376be899c7a512709';

       $secret_key =md5($str);


       $url ='http://120.24.3.137/outapi/v1/insures?secret_key='.$secret_key."&partner_number=ip46a71&s_type=1&vin=LSVET69F1B2444483";
       // $url ='http://120.24.3.137/outapi/v1/get_report?secret_key='.$secret_key."&partner_number=ip46a71&apply_id=15900";


       $html=file_get_contents($url);
       $data=json_decode($html,true);

       print_r($data);exit;
   }

//
//    //查出现详情
//    public function  chuxianinfoAction(){
//
//
//        $str='number=ip46a71&key=81f6ed334a34150fe78de9d376be899c7a512709';
//
//        $secret_key =md5($str);
//
//        // $secret_key='81f6ed334a34150fe78de9d376be899c7a512709';
//
//        //品牌检查
//
//        $url ='http://120.24.3.137/outapi/v1/insure_detail?secret_key='.$secret_key."&partner_number=ip46a71&insure_id=1";
//
//        //vin码查询接口
//
//        // $url ='http://120.24.3.137/outapi/v1/vin_search?secret_key='.$secret_key."&partner_number=ip46a71&vin=LGBP12E21DY196239&brand_id=4";
//
//        //详细报告
//
//        // $url ='http://120.24.3.137/outapi/v1/get_report?secret_key='.$secret_key."&partner_number=ip46a71&apply_id=15900";
//
//        $html=file_get_contents($url);
//        $data=json_decode($html,true);
//
//         print_r($data);exit;
//    }
//
//
//
//    //查出现条数
//    public function  chuxiannumAction(){
//
//
//        $str='number=ip46a71&key=81f6ed334a34150fe78de9d376be899c7a512709';
//
//        $secret_key =md5($str);
//
//        // $secret_key='81f6ed334a34150fe78de9d376be899c7a512709';
//
//        //品牌检查
//
//        $url ='http://120.24.3.137/outapi/v1/check_insure?secret_key='.$secret_key."&partner_number=ip46a71&vin=LGBP12E21DY196239&s_type=1&vin=LGBP12E21DY196239";
//
//        //vin码查询接口
//
//        // $url ='http://120.24.3.137/outapi/v1/vin_search?secret_key='.$secret_key."&partner_number=ip46a71&vin=LGBP12E21DY196239&brand_id=4";
//
//        //详细报告
//
//        // $url ='http://120.24.3.137/outapi/v1/get_report?secret_key='.$secret_key."&partner_number=ip46a71&apply_id=15900";
//
//        $html=file_get_contents($url);
//        $data=json_decode($html,true);
//
//        print_r($data);exit;
//    }


    /**
     * @api {POST} /v4/pollingcar/checkcarrule  查询违章
     * @apiName pollingcar checkcarrule
     * @apiGroup PollingCar
     * @apiDescription 查询违章接口
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.0.0
     *
     * @apiParam {string} [device_identifier] 设备唯一标识
     * @apiParam {string} [session_id] session_id
     * @apiParam {string} [city] 城市编码
     * @apiParam {string} [hphm] 车牌号(粤AN324Y)
     * @apiParam {string} [classno]  车架后六位
     * @apiParam {string} [engineno]  发动机号六位
     *
     * @apiParamExample {json} 请求样例
     *   POST /v3/car/checkcar
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "hphm":"粤AN324Y",
     *       "classno":"741406",
     *       "engineno":"604825",
     *       "city":"GD_GZ",
     *
     *     }
     *   }
     *
     */

    public function checkcarruleAction(){

        $this->required_fields = array_merge($this->required_fields, array('session_id','city','hphm','classno','engineno'));
        $data = $this->get_request_data();
        $userId = $this->userAuth($data);

        $wz=new WeiZhang();
//        $data['city']="GD_GZ";
//         $data['hphm']="粤AN324Y";
//         $data['classno']="741406";
//         $data['engineno']="604825";
//         $userId='389';

        $time=time();
        $HascheckcarM= new HascheckcarModel();
        $HascheckcarM->user_id  = $userId;
        $HascheckcarM->city     = $data['city'];
        $HascheckcarM->hphm     = $data['hphm'];
        $HascheckcarM->engineno = $data['engineno'];
        $HascheckcarM->classno  = $data['classno'];
        $HascheckcarM->created  = $time;
        $HascheckcarM->saveProperties();
        $HascheckcarId = $HascheckcarM->CreateM();

        $city=$data['city'];
        $hphm=$data['hphm'];   //车牌号码
        $classno=$data['classno'];    //车架号
        $engineno=$data['engineno'];    //发动机号

        $result=$wz->query($city,$hphm,$engineno,$classno);

        if($result['resultcode'] == 200){
            $where['id']=$HascheckcarId;
            $report['report']= json_encode($result["result"],JSON_UNESCAPED_UNICODE);
            $HascheckcarM->updateByPrimaryKey($HascheckcarM->tablename,$where,$report);

        }

        $this->send($result);



    }
    /**
     * @api {POST} /v4/pollingcar/checkcarrulelist  违章列表
     * @apiName pollingcar checkcarrulelist
     * @apiGroup PollingCar
     * @apiDescription 违章列表
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.0.0
     *
     * @apiParam {string} device_identifier 设备唯一标识
     * @apiParam {string} session_id session_id
     * @apiParam {string} page  页码
     *
     * @apiParamExample {json} 请求样例
     *   POST /v4/pollingcar/checkcarrulelist
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *
     *     }
     *   }
     *
     */

    public function checkcarrulelistAction(){

        $this->required_fields = array_merge($this->required_fields, array('session_id','page'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $HascheckcarM= new HascheckcarModel();

        //@$data['page'] ? ($data['page']+1) : 1 ;
        $HascheckcarM->page=$data['page'] ? ($data['page']+1) : 1 ;

        $result =  $HascheckcarM->getList($userId);

        $this->send($result);
    }

    /**
     * @api {POST} /v4/pollingcar/getreport  查看详情(维保／出险)
     * @apiName pollingcar getreport
     * @apiGroup PollingCar
     * @apiDescription 查看详情(维保／出险)
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.0.0
     *
     * @apiParam {string} device_identifier 设备唯一标识
     * @apiParam {string} session_id session_id
     * @apiParam {string} report_sn  报告单号
     *
     * @apiParamExample {json} 请求样例
     *   POST /v4/pollingcar/getreport
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *
     *     }
     *   }
     *
     * @apiSuccess {number} report.status 1:待支付  2:支付成功(报告生成中) 3:支付失败 4:报告已生成 5:报告异常
     *
     */
    public function getReportAction(){

        $this->required_fields = array_merge($this->required_fields, array('session_id','report_sn'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $PollingCarM = new PollingCarModel();

        $report= $PollingCarM->getReport($data['report_sn']);

        $report['brand_info']=$PollingCarM->getReprotBrand($report['brand_id']);

        $report['report']=json_decode($report['report']);

        $this->send($report);
    }


    public function historyAction(){


        $this->required_fields = array_merge($this->required_fields, array('session_id','page'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $HascheckcarM= new HascheckcarModel();

        //@$data['page'] ? ($data['page']+1) : 1 ;
        $HascheckcarM->page=$data['page'] ? ($data['page']+1) : 1 ;

        $result =  $HascheckcarM->getList($userId);

        //出险
        $PollingCarM = new PollingCarModel();

        $PollingCarM->page= $data['page'] ? ($data['page']+1) : 1 ;

        $lists = $PollingCarM->getReportList($userId);

      //  print_r($lists);exit;

        foreach ($lists as $k => $val) {


            $lists[$k]['report'] = json_decode($val['report']);
        }

        $response['carrule']=$result;

        $response['report']=$lists;

        $this->send($response);

    }


    public function CharuAction(){


//        $str='number=ip46a71&key=81f6ed334a34150fe78de9d376be899c7a512709';
//
//        $secret_key =md5($str);
//
//        //维保
//        $url ='http://120.24.3.137/outapi/v1/get_report?secret_key='.$secret_key."&partner_number=ip46a71&apply_id=15900";
//
//        $html=file_get_contents($url);
//
//        $result=json_decode($html,true);
//
//        $report_sn = "2017091548991014";
//
//        if($result['status'] == "success"){
//            $pdo = new PdoDb;
//            $where['report_sn'] = $report_sn;
//            $update['report']=json_encode($result, JSON_UNESCAPED_UNICODE);
//            $update['status']=4;
//            $pdo->updateByPrimaryKey('bibi_chedang_report',$where,$update);
//        }else{
//            $pdo = new PdoDb;
//            $where['report_sn'] = $report_sn;
//            $update['report']=json_encode($result, JSON_UNESCAPED_UNICODE);
//            $update['status']=5;
//            $pdo->updateByPrimaryKey('bibi_chedang_report',$where,$update);
//        }

//出险
        $report_sn = "2017091555484950";

        $str='number=ip46a71&key=81f6ed334a34150fe78de9d376be899c7a512709';
        $secret_key =md5($str);

        //LGBP12E21DY196239;

        $vin = "LGBP12E21DY196239";

        $url ='http://120.24.3.137/outapi/v1/insures?secret_key='.$secret_key."&partner_number=ip46a71&vin=".$vin."&s_type=1&plate=粤B6KB87";

        $html=file_get_contents($url);

        $result=json_decode($html,true);

        //print_r($result);exit;

        if($result['status'] == "success"){
            $pdo = new PdoDb;
            $where['report_sn'] = $report_sn;
            $update['report']=json_encode($result, JSON_UNESCAPED_UNICODE);
            $update['status']=4;
            $pdo->updateByPrimaryKey('bibi_chedang_report',$where,$update);
        }else{
            $pdo = new PdoDb;
            $where['report_sn'] = $report_sn;
            $update['report']=json_encode($result, JSON_UNESCAPED_UNICODE);
            $update['status']=5;
            $pdo->updateByPrimaryKey('bibi_chedang_report',$where,$update);
        }



    }





}
