<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/13
 * Time: 下午6:09
 */
class OrderController extends ApiYafControllerAbstract
{


    /**
     * @api {POST} /v1/order/index 订单详情
     * @apiName order index
     * @apiGroup Order
     * @apiDescription 订单详情
     * @apiPermission anyone
     * @apiSampleRequest http://new.bibicar.cn
     *
     * @apiParam {string} device_identifier 设备唯一标识
     * @apiParam {string} session_id session_id
     * @apiParam {string} order_id 订单ID
     *
     * @apiParamExample {json} 请求样例
     *   POST /v1/order/index
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "order_id":"",
     *
     *     }
     *   }
     *
     */
    public function indexAction()
    {

        $this->required_fields = array_merge($this->required_fields, array('session_id', 'order_id'));

        $data = $this->get_request_data();

        //$userId = $this->userAuth($data);
        if($data['session_id']){

            $sess = new SessionModel();
            $userId = $sess->Get($data);
        }else{
            $userId = 0;
        }

        $OrderModel = new OrderModel();

        $order = $OrderModel->getOrderInfo($data['order_id']);

        if(!$order){
            return $this->send_error(HAS_EXSIT);
        }

        $this->send($order);
    }

    /**
     * @api {POST} /v1/order/create 创建订单
     * @apiName order create
     * @apiGroup Order
     * @apiDescription 创建订单
     * @apiPermission anyone
     * @apiSampleRequest http://new.bibicar.cn
     *
     * @apiParam {string} device_identifier 设备唯一标识
     * @apiParam {string} session_id session_id
     * @apiParam {string} car_id 车辆ID
     * @apiParam {string} contact_name 联系人姓名
     * @apiParam {string} contact_phone 联系人电话
     * @apiParam {string} order_amount 订单总额
     * @apiParam {string} sub_fee 订金
     *
     * @apiParamExample {json} 请求样例
     *   POST /v1/order/create
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "car_id":"",
     *       "contact_name":"",
     *       "contact_phone":"",
     *       "order_amount":"",
     *       "sub_fee":"",
     *
     *     }
     *   }
     *
     */
    public function createAction(){

        $this->required_fields = array_merge($this->required_fields, array('session_id', 'car_id','contact_name','contact_phone','order_amount','sub_fee'));

        $data = $this->get_request_data();

        //$userId = $this->userAuth($data);
        if($data['session_id']){

            $sess = new SessionModel();
            $userId = $sess->Get($data);
        }else{

            $userId = 0;
        }

        if(!$userId){

            return $this->send_error(USER_AUTH_FAIL);
        }

        $OrderModel = new OrderModel();

        $order = $OrderModel->getOrder($userId,$data['car_id']);

        if($order){
            return $this->send_error(HAS_EXSIT);
        }

        $properties = array();
        $properties['user_id'] = $userId;
        $properties['car_id']  = $data['car_id'];
        $properties['contact_name']  = $data['contact_name'];
        $properties['contact_phone']  = $data['contact_phone'];
        $properties['sub_fee']  = $data['sub_fee'];
        $properties['order_amount']  = $data['order_amount'];
        $created = time();
        $properties['created_at'] = $created;

        $OrderModel = new OrderModel();
        $OrderModel->properties = $properties;
        $id = $OrderModel->CreateM();

        if($id){

            $OrderModel = new OrderModel();

            $order = $OrderModel->getOrderInfo($id);

            $this->send($order);

        }

    }


    /**
     * @api {POST} /v1/order/list 订单列表
     * @apiName order list
     * @apiGroup Order
     * @apiDescription 订单列表
     * @apiPermission anyone
     * @apiSampleRequest http://new.bibicar.cn
     *
     * @apiParam {string} device_identifier 设备唯一标识
     * @apiParam {string} session_id session_id
     * @apiParam {string} page 页码
     * @apiParam {string} order_status 订单状态  1: 待签约2:运输中3:已到店 4:已关闭
     *
     * @apiParamExample {json} 请求样例
     *   POST /v1/order/list
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "page":"",
     *
     *     }
     *   }
     *
     */
    public function listAction(){

        $this->required_fields = array_merge($this->required_fields, array('session_id','page'));

        $data = $this->get_request_data();

        if($data['session_id']){

            $sess = new SessionModel();
            $userId = $sess->Get($data);
        }else{

            $userId = 0;
        }

        if(!$userId){

            return $this->send_error(USER_AUTH_FAIL);
        }

        $data['page']     = $data['page'] ? ($data['page']+1) : 1;

        $data['order_status']     = isset($data['order_status']) ? $data['order_status'] : 0;

        $OrderModel = new OrderModel();

        $orders = $OrderModel->getOrders($userId,$data['page'],$data['order_status']);

        $this->send($orders);

    }












}
