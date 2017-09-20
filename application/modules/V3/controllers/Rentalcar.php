<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/13
 * Time: 下午6:09
 */
use EasyWeChat\Foundation\Application;
use EasyWeChat\Payment\Order;

class RentalcarController extends ApiYafControllerAbstract
{


    /**
     * @api {POST} /v3/rentalcar/index 车辆详情
     * @apiName rentalcar index
     * @apiGroup RentalCar
     * @apiDescription 车辆详情
     * @apiPermission anyone
     * @apiSampleRequest http://www.testapi.bibicar.cn
     * @apiVersion 1.0.0
     *
     * @apiParam {string} device_identifier 设备唯一标识
     * @apiParam {string} session_id session_id
     * @apiParam {string} car_id 车辆Id
     *
     *
     * @apiParamExample {json} 请求样例
     *   POST /v3/rentalcar/index
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "car_id":""
     *     }
     *   }
     * @apiSuccess {Object} recommon_cars   推荐车辆
     * @apiSuccess {string} is_auth   是否身份验证 1:是 2:否
     * @apiSuccess {string} rental_user   租车用户信息
     * @apiSuccess {string} share_tile   分享标题
     * @apiSuccess {string} share_url   分享链接
     * @apiSuccess {string} share_txt   分享文字
     * @apiSuccess {string} share_img   分享封面
     * @apiSuccess {Object} car_info.rental_info   租车信息
     * @apiSuccess {string} car_info.rental_info.one   1-7天租金
     * @apiSuccess {string} car_info.rental_info.two   7-15天租金
     * @apiSuccess {string} car_info.rental_info.three 15-30天租金
     * @apiSuccess {string} car_info.rental_info.four 30以上租金
     * @apiSuccess {string} car_info.rental_info.pick_address 提车地址
     * @apiSuccess {string} car_info.rental_info.pick_lat 提车纬度
     * @apiSuccess {string} car_info.rental_info.pick_lng 提车经度
     * @apiSuccess {string} car_info.rental_info.status 车辆出租状态 1:可租 2已租
     * @apiSuccess {string} car_info.rental_info.rental_end_time 被租结束时间（返回时间戳）
     * @apiSuccess {string} car_info.rental_info.deposit 订金
     * @apiSuccess {string} car_info.rental_info.subscription 押金
     *
     *
     *
     */

    public function indexAction()
    {
        $this->required_fields = array_merge($this->required_fields, array('session_id', 'car_id'));

        $data = $this->get_request_data();

        //$userId = $this->userAuth($data);
        if(@$data['session_id']){

            $sess = new SessionModel();
            $userId = $sess->Get($data);
        }
        else{

            $userId = 0;
        }

        $carModel = new CarRentalModel();

        $RentalUserModel=new CarRentalUserModel();

        $carT = $carModel::$table;

        $carId = $data['car_id'];

        $carModel->currentUser = $userId;

        $carInfo = $carModel->GetCarInfoById($carId,$userId);

        $response['car_info'] = $carInfo;

        $response['recommon_cars'] = $carModel->relatedRecommonCars();

        $Rental_user =$RentalUserModel->getRentalUserById($userId);


        if($Rental_user){
            $response['is_auth'] = 1;
            $response['rental_user']=$Rental_user;

        }else{

            $reponnse['is_auth'] = 2;
            $response['rental_user']=$Rental_user;
        }

        $visitCarM = new VisitCarModel();
        $visitCarM->car_id  = $carId;
        $visitCarM->user_id = $userId;
        $id = $visitCarM->get();

        if(!$id){

//            $properties = array();
//            $properties['created'] = time();
//            $properties['user_id'] = $userId;
//            $properties['car_id']  = $carId;
//
//            $carModel->updateByPrimaryKey(
//                $carT,
//                array('hash'=>$carId),
//                array('visit_num'=>($carInfo['visit_num']+1))
//            );
//
//            $visitCarM->insert($visitCarM->tableName, $properties);
        }

        $title = is_array($carInfo['user_info']) ?
            $carInfo['user_info']['profile']['nickname'] . '的' . $carInfo['car_name']
            : $carInfo['car_name'];

        $response['share_url']="http://custom.bibicar.cn/views/detail/rent.html?ident=".$data['device_identifier']."&session=".$data['session_id']."&car_id=".$carId;

        $response['share_title'] = $title;
        //http://m.bibicar.cn/post/index?device_identifier='.$data['device_identifier'].'&fcar_id='.$carId.'
        //$response['share_url'] = 'http://wap.bibicar.cn/car/'.$carId.'?identity='.base64_encode($data['device_identifier']);
        $response['share_txt'] = '更多精选二手车在bibi car,欢迎您来选购!';
        $response['share_img'] = isset($carInfo['files'][0]) ? $carInfo['files'][0]['file_url'] : '';

        $this->send($response);


    }

    /**
     * @api {POST} /v3/rentalcar/list 可租车辆列表
     * @apiName rentalcar list
     * @apiGroup RentalCar
     * @apiDescription 租车车辆列表
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 1.0.0
     *
     * @apiParam {string} device_identifier 设备唯一标识
     * @apiParam {number} page 设备唯一标识
     *
     * @apiParamExample {json} 请求样例
     *    POST  /v3/rentalcar/list
     *   {
     *     "data": {
     *       "device_identifier":"",
     *
     *     }
     *   }
     * @apiSuccess {Object} car_info.rental_info   租车信息
     * @apiSuccess {string} car_info.rental_info.one   1-7天租金
     * @apiSuccess {string} car_info.rental_info.two   7-15天租金
     * @apiSuccess {string} car_info.rental_info.three 15-30天租金
     * @apiSuccess {string} car_info.rental_info.four 30以上租金
     * @apiSuccess {string} car_info.rental_info.pick_address 提车地址
     * @apiSuccess {string} car_info.rental_info.pick_lat 提车纬度
     * @apiSuccess {string} car_info.rental_info.pick_lng 提车经度
     * @apiSuccess {string} car_info.rental_info.status 车辆出租状态 1:可租 2已租
     *
     */
    //session_id=session58ede340f1394&device_identifier=1d7c030c120f467e58e832cde18a4f4a&car_id=578315da9b1cd
    public function listAction(){


        $this->required_fields = array_merge($this->required_fields,array('page'));

        $data = $this->get_request_data();

        if(@$data['session_id']){
            $sess = new SessionModel();
            $userId = $sess->Get($data);
        }
        else{
            $userId = 0;
        }

        $carModel = new CarRentalModel();

        $carModel->page = $data['page'] ? ($data['page']+1) : 1;

        $lists = $carModel->getRentalCarList();

        $carModel->currentUser = $userId;

        //$lists = $carM->getCarList($userId);

        if($lists['car_list']){

            foreach($lists['car_list'] as $key => $list){

                $file = isset($list['car_info']['files'][0]) ?  $list['car_info']['files'][0] : array();

                $lists['car_list'][$key]['car_info']['files'] = array();
                $lists['car_list'][$key]['car_info']['files'][] = $file;
            }
        }
        $response = $lists;

        $this->send($response);

    }


    /**
     * @api {POST} /v3/rentalcar/uploadfile 提交租车资料
     * @apiName rentalcar uploadfile
     * @apiGroup RentalCar
     * @apiDescription 提交租车资料
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 1.0.0
     *
     * @apiParam {string} device_identifier 设备唯一标识
     * @apiParam {string} contact_name 租车用户名称
     * @apiParam {string} mobile 租车用户电话
     * @apiParam {string} card_no 身份证号
     * @apiParam {string} card_cur 身份证正面
     * @apiParam {string} card_opp 身份证反面
     * @apiParam {string} drive_cur 驾驶证正面
     * @apiParam {string} drive_opp 驾驶证反面
     *
     * @apiParamExample {json} 请求样例
     *    POST  /v3/rentalcar/uploadfile
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "card_no":"",
     *       "contact_name":"",
     *       "card_cur":"",
     *       "card_opp":"",
     *       "drive_cur":"",
     *       "drive_opp":"",
     *       "mobile":"",
     *
     *     }
     *   }
     *
     */
//session_id=session58ede340f1394&device_identifier=1d7c030c120f467e58e832cde18a4f4a&card_no=440881199001072271&contact_name=baody&card_cur=1234dsfgdsfgsdfgdsf&card_opp=1234dsfgdsfgsdfgdsf&drive_cur=1234dsfgdsfgsdfgdsf&drive_opp=1234dsfgdsfgsdfgdsf

    public function UploadfileAction(){


        $this->required_fields = array_merge($this->required_fields,array('session_id','card_no','contact_name','card_cur','card_opp','drive_cur','drive_opp'));

        $data = $this->get_request_data();



        if(@$data['session_id']){
            $sess = new SessionModel();
            $userId = $sess->Get($data);
        }
        else{
            $userId = 0;
        }


        $CarRentalUserM= new CarRentalUserModel;

        $result = $CarRentalUserM ->getUserByCardNo($userId,$data['card_no']);

        if($result){
            $this->send_error(RENTAL_USER_HAS_ERROR);
        }else{

            $CarRentalUserM->user_id     =$userId;
            $CarRentalUserM->card_no     =$data['card_no'];
            $CarRentalUserM->contact_name=$data['contact_name'];
            $CarRentalUserM->card_cur    =$data['card_cur'];
            $CarRentalUserM->card_opp    =$data['card_opp'];
            $CarRentalUserM->drive_cur   =$data['drive_cur'];
            $CarRentalUserM->drive_opp   =$data['drive_opp'];
           // $CarRentalUserM->mobile      =$data['mobile'];
            $CarRentalUserM->created_at  =time();
            $CarRentalUserM->saveProperties();
            $Id = $CarRentalUserM->CreateM();

            if($Id){

                $response['message'] ="成功";
                $this->send($response);
            }else{

                 $this->send_error(RENTAL_USER_UPLOAD_ERROR);
            }


        }


    }

    /**
     * @api {POST} /v3/rentalcar/createrentalorder 创建租车订单
     * @apiName rentalcar createrentalorder
     * @apiGroup RentalCar
     * @apiDescription 创建租车订单
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 1.0.0
     *
     * @apiParam {string} device_identifier 设备唯一标识
     * @apiParam {string} session_id 用户session
     * @apiParam {string} card_id 车辆Id
     * @apiParam {string} rental_time_start 租车开始时间
     * @apiParam {string} rental_time_end 租车结束时间
     * @apiParam {string} mobile 联系人电话
     *
     * @apiParamExample {json} 请求样例
     *    POST  /v3/rentalcar/uploadfile
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "card_id":"",
     *       "rental_time_start":"",
     *       "rental_time_end":"",
     *
     *     }
     *   }
     * @apiSuccess {Object} order_info 订单详情
     * @apiSuccess {string} order_info.order_sn   订单号
     * @apiSuccess {string} order_info.total_price   押金
     * @apiSuccess {string} order_info.status   1:待支付 2:支付失败 3:支付成功（待提车） 4:订单失败 5:订单成功
     * @apiSuccess {number} order_info.rental_time_start 租车开始时间（时间戳）
     * @apiSuccess {number} order_info.rental_time_end   租车结束时间（时间戳）
     *
     */
    //    //session_id=session58ede340f1394&device_identifier=1d7c030c120f467e58e832cde18a4f4a&car_id=578315da9b1cd

    public function CreateRentalOrderAction(){


        $this->required_fields = array_merge($this->required_fields,array('session_id','car_id','rental_time_start','rental_time_end','mobile'));

        $data = $this->get_request_data();

        if(@$data['session_id']){
            $sess = new SessionModel();
            $userId = $sess->Get($data);
        }
        else{
            $userId = 0;
        }
        $CarRentalUserM= new CarRentalUserModel;

        $where['user_id']=$userId;
        $user_update['mobile']=$data['mobile'];
        $CarRentalUserM->update($where,$user_update);

        $CarRentalM= new CarRentalModel;

        $res = $CarRentalM ->getCarRetalStatus($data['car_id']);

        $CarRentalOrder = new CarRentalOrderModel();

        if($res['status'] == 1){

            $properties['order_sn'] =$this->createorder_sn();

            $properties['car_id']  = $data['car_id'];

            $properties['rental_time_start'] = $data['rental_time_start'];

            $properties['rental_time_end'] = $data['rental_time_end'];

            $properties['user_id'] = $userId;
            $properties['created_by'] = $userId;
            $properties['created_at'] = time();
            $properties['total_price'] = $res['deposit'];

            $CarRentalOrder ->properties = $properties;

            $id = $CarRentalOrder->CreateM();

            if($id){

//                $update['status'] = 2;
//
//                $CarRentalM->updateByHash($data['car_id'],2);

                $result=$CarRentalOrder->getRentalOrderInfo($properties['order_sn'],$data['car_id']);

                $this->send($result);

            }else{

                $this->send_error(CREATE_ORDER_ERROR);//创建订单失败
            }

        }else{

            $this->send_error(RENTAL_CAR_NO_ALLOW);
        }


    }


    /**
     * @api {POST} /v3/rentalcar/rentalorderpay 创建租车支付
     * @apiName rentalcar rentalorderpay
     * @apiGroup RentalCar
     * @apiDescription 创建租车支付
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 1.0.0
     *
     * @apiParam {string} device_identifier 设备唯一标识
     * @apiParam {string} session_id 用户session
     * @apiParam {number} pay_type 支付方式 1:支付宝 2微信
     * @apiParam {number} order_sn 订单号
     *
     * @apiParamExample {json} 请求样例
     *    POST  /v3/rentalcar/uploadfile
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "pay_type":"",
     *       "order_sn":"",
     *     }
     *   }
     *
     */
    //    //session_id=session58ede340f1394&device_identifier=1d7c030c120f467e58e832cde18a4f4a&car_id=578315da9b1cd

    public function RentalOrderPayAction(){


        $this->required_fields = array_merge($this->required_fields,array('session_id','pay_type','order_sn'));

        $data = $this->get_request_data();

        if(@$data['session_id']){
            $sess = new SessionModel();
            $userId = $sess->Get($data);
        }
        else{
            $userId = 0;
        }

        $CarRentalOrder = new CarRentalOrderModel();

        $order=$CarRentalOrder->getRentalOrderInfo($data['order_sn']);


        if($order){

            if( $order['order_info']['user_id'] != $userId){
                $this->send_error(RENTAL_USER_AUTH_NO_ALOW);
            }


            if($data['pay_type'] == 2){

                $wechat = new Wechat();

                $app    = $wechat->getWechat();

                $payment = $app->payment;

                $attributes = [
                    'trade_type'       => 'APP', // JSAPI，NATIVE，APP...
                    'body'             => '吡吡汽车租车押金',
                    'detail'           => '吡吡汽车租车押金',
                    'out_trade_no'     => $order['order_info']['order_sn'],
                    'total_fee'        => $order['order_info']['total_price']*100, // 单位：分
                    'notify_url'       => 'http://testapi.bibicar.cn/v3/rentalcar/wxnotify', // 支付结果通知网址，如果不设置则会使用配置里的默认地址
                    // 'sub_openid'        => '当前用户的 openid', // 如果传入sub_openid, 请在实例化Application时, 同时传入$sub_app_id, $sub_merchant_id
                    // ...
                ];
                $order = new Order($attributes);
                $result = $payment->prepare($order);

                if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS'){
                    $prepayId = $result->prepay_id;
                    $config = $payment->configForAppPayment($prepayId);

                    $where['id'] = $order['order_info']['id'];
                    $update['pay_type']= 2;
                    $update['pay_no']  = $prepayId;
                    $CarRentalOrder->update($where,$update);
                    $config['type']="Wxpay";
                    return $this->send($config);
                }else{
                    print_r($result);exit;
                }

            }else{

                $notifyUrl="https://testapi.bibicar.cn/v3/rentalcar/alinotify";
                $alipayM=new Alipay();
                $order_sn=$order['order_info']['order_sn'];
                $order_amount=$order['order_info']['total_price'];
                $goods_name='吡吡汽车租车押金';
                $result=$alipayM->alipay($order_sn,$order_amount,$goods_name,$notifyUrl);
                $response['orderstr']=$result;
                $response['type']="Alipay";
                $where['id'] = $order['order_info']['id'];
                $update['pay_type']= 1;
                $update['pay_fee']= $order['order_info']['total_price'];
                $CarRentalOrder->update($where,$update);
                $this->send($response);

            }



        }else{

            $this->send_error(RENTAL_ORDER_NO_EXIT);


        }




    }



    /**
     * @api {POST} /v3/rentalcar/rentalorderlist 我的租车
     * @apiName rentalcar rentalorderlist
     * @apiGroup RentalCar
     * @apiDescription 我的租车
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 1.0.0
     *
     * @apiParam {string} device_identifier 设备唯一标识
     * @apiParam {string} session_id 用户session
     * @apiParam {number} page 页数
     *
     * @apiParamExample {json} 请求样例
     *    POST  /v3/rentalcar/rentalorderlist
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "page":"",
     *     }
     *   }
     *
     */

    public function RentalOrderListAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id','page'));

        $data = $this->get_request_data();

        if(@$data['session_id']){
            $sess = new SessionModel();
            $userId = $sess->Get($data);
        }
        else{
            $userId = 0;
        }
        $CarRentalOrder = new CarRentalOrderModel();

        $CarRentalOrder->page = $data['page'] ? ($data['page']+1) : 1;

        $CarRentalOrder->currentUser = $userId;

        $order=$CarRentalOrder->getRentalOrderList();
        $this->send($order);


    }

    /**
     * @api {POST} /v3/rentalcar/rentalorderindex 订单详情
     * @apiName rentalcar rentalorderindex
     * @apiGroup RentalCar
     * @apiDescription 订单详情
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 1.0.0
     *
     * @apiParam {string} device_identifier 设备唯一标识
     * @apiParam {string} session_id 用户session
     * @apiParam {number} order_sn 订单号
     *
     * @apiParamExample {json} 请求样例
     *    POST  /v3/rentalcar/rentalorderlist
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "order_sn":"",
     *     }
     *   }
     *
     */

    public function RentalOrderIndexAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id','order_sn'));

        $data = $this->get_request_data();

        if(@$data['session_id']){
            $sess = new SessionModel();
            $userId = $sess->Get($data);
        }
        else{
            $userId = 0;
        }

        $CarRentalOrder = new CarRentalOrderModel();

        $CarRentalOrder->page = $data['page'] ? ($data['page']+1) : 1;

        $CarRentalOrder->currentUser = $userId;

        $order=$CarRentalOrder->getRentalOrderInfo($data['order_sn']);
        $this->send($order);
        
    }



    /**
     *
     */
    //微信回调通知
    public function wxnotifyAction(){

        Common::globalLogRecord ( 'remote_ip_wx', $_SERVER['REMOTE_ADDR'] );
        Common::globalLogRecord ( 'request_url_wx', 'http://'. $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] );

        $wechat = new Wechat();

        $app    = $wechat->getWechat();

        $payment = $app->payment;

        $response = $app->payment->handleNotify(function($notify, $successful){
            // 使用通知里的 "微信支付订单号" 或者 "商户订单号" 去自己的数据库找到订单
           // $order = 查询订单($notify->out_trade_no);
            $CarRentalOrder = new CarRentalOrderModel();
            $order=$CarRentalOrder->getRentalOrderInfo($notify->out_trade_no);
            if (!$order) { // 如果订单不存在
                return 'Order not exist.'; // 告诉微信，我已经处理完了，订单没找到，别再通知我了
            }
            // 如果订单存在
            // 检查订单是否已经更新过支付状态
            if ($order->paid_at) { // 假设订单字段“支付时间”不为空代表已经支付
                return true; // 已经支付成功了就不再更新了
            }
            // 用户是否支付成功
            if ($successful) {
                // 不是已经支付状态则修改为已经支付状态
                $order->paid_at = time(); // 更新支付时间为当前时间
                $order->status = 'paid';

                $where['order_sn'] = $order['order_info']['order_sn'];
                $update['pay_time']= time();
                $update['status']= 3;
                $CarRentalOrder->update($where,$update);

                $CarRentalM= new CarRentalModel;
                $CarRentalM->updateByHash( $order['order_info']['car_id'],2);

            } else { // 用户支付失败
                $where['order_sn'] = $order['order_info']['order_sn'];
                $update['pay_time']= time();
                $update['status']= 2;
                $CarRentalOrder->update($where,$update);

            }

            return true; // 返回处理完成
        });
        return $response;

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

                $CarRentalOrder = new CarRentalOrderModel();
                $order=$CarRentalOrder->getRentalOrderInfo($data['out_trade_no']);

                if($order['order_info']['status'] == 1 ){
                    $where['order_sn'] =$data['out_trade_no'];
                    $update['status']  = 3;
                    $update['pay_time'] = time();
                    $update['pay_no']=$data['trade_no'];
                    $CarRentalOrder->update($where,$update);
                }
            }else if($data['trade_status'] == "TRADE_CLOSED"){

                $CarRentalOrder = new CarRentalOrderModel();
                $order=$CarRentalOrder->getRentalOrderInfo($data['out_trade_no']);
                if($order['order_info']['status'] == 1 ){
                    $where['order_sn'] =$data['out_trade_no'];
                    $update['status']  =2;
                    $update['pay_time'] = time();
                    $update['pay_no']=$data['trade_no'];
                    $CarRentalOrder->update($where,$update);
                }


            }

            return true;

            //发送通知给卖家
        }

    }




}
