<?php
/**
 * Created by sublime.
 * User: jpjy
 * Date: 15/10/19
 * Time: 上午11:50
 * note: 文章管理
 */
class ShopController extends ApiYafControllerAbstract
{
/**
 * @apiDefine Data
 *
 * @apiParam (data) {string}  device_identifier=ce32eaab37220890a063845bf6b6dc1a  设备唯一标示.
 * @apiParam (data) {string}  session_id=session5845346a59a31     用户session_id.
 * 
 * 
 */

/**
 * @api {POST} /v3/shop/shopslist 店铺列表
 * @apiName shop list 
 * @apiGroup GOODS
 * @apiDescription 店铺列表
 * @apiPermission anyone
 * @apiSampleRequest http://www.bibicar.cn:8090
 *
 * @apiParam {string} device_identifier 设备唯一标识
 * @apiParam {string} session_id session_id
 * @apiParam {number} page 页数
 * 
 * @apiUse Data
 * @apiParamExample {json} 请求样例
 *   POST /v3/shop/shopslist
 *   {
 *     "data": {
 *       "device_identifier":"",
 *       "session_id":"",
 *       "page":"",
 *       
 *       
 *     }
 *   }
 *  @apiSuccessExample {json} Success-Response:
 *  HTTP/1.1 200 OK
 *  {
 *    "status": 1
 *    "code": 0
 *    "data": {
 *    "shop_list": [2]
 *    0:{
 *       "shop_id": 1
 *       "image": "http://img.bibicar.cn/bibilogo.png"
 *       "shop_name": "吡吡小淇凌"
 *       "goods_num": 0
 *       "lat": 113.940273
 *       "lng": 22.491501
 *       "seller_id": 389
 *    }
 *    1:{
 *       "shop_id": 2
 *       "image": "http://img.bibicar.cn/bibilogo.png"
 *       "shop_name": "吡吡大淇凌"
 *       "goods_num": 0
 *       "lat": 113.921718
 *       "lng": 22.490032
 *       "seller_id": 389
 *    }
 *    "has_more": 2
 *    "total": 2
 *    }
 *  }
 *
 */
    //店铺列表
    public function shopslistAction(){
       
        $this->required_fields = array_merge($this->required_fields,array('session_id','page'));

        $data = $this->get_request_data();
        
        if(@$data['session_id']){

           $userId = $this->userAuth($data);
        }
        else{
            $userId = 0;
        }
        $page = $data['page'] ? ($data['page']+1) : 1;
        $shopM=new ShopModel();
        $response=$shopM->getshop($page,$userId);
        $this->send($response);
       
         
    }
/**
 * @api {POST} /v3/shop/goodslist 店铺里商品列表
 * @apiName goods list 
 * @apiGroup GOODS
 * @apiDescription 商品列表
 * @apiPermission anyone
 * @apiSampleRequest http://www.bibicar.cn:8090
 *
 * @apiParam {string} device_identifier 设备唯一标识
 * @apiParam {string} session_id session_id
 * @apiParam {number} shop_id 店铺id
 * @apiParam {number} [page] 页数
 * @apiParam {number} [order_id] 排序 0:默认 1最高价 2最低价 3 销量最高
 * @apiParam {string} [keyword] 关键字
 * @apiParam {string} [type] 1单品 2套餐,3有规格单品,4配料
 * @apiParam {string} goods_item 1甜筒 2圣代,3棉花糖
 * 
 * @apiUse Data
 * @apiParamExample {json} 请求样例
 *   POST /v3/shop/shoplist
 *   {
 *     "data": {
 *       "device_identifier":"ce32eaab37220890a063845bf6b6dc1a",
 *       "session_id":"session5845346a59a31",
 *       "page":"0",
 *       "shop_id":"1",
 *       "order_id":"0",
 *       "keyword":"",
 *       "goods_item":"",
 *       
 *       
 *     }
 *   }
 *  @apiSuccessExample {json} Success-Response:
 *  HTTP/1.1 200 OK
 *  {
 *    "status": 1
 *    "code": 0
 *    "data": {
 *    "shop_info": {
 *       "shop_id": 1
 *       "image": "http://img.bibicar.cn/bibilogo.png"
 *       "shop_name": "吡吡小淇凌"
 *       "goods_num": 0
 *       "lat": 113.940273
 *       "lng": 22.491501
 *       "seller_id": 389
 *    }
 *    "Goods_list"[1]
 *    0:{
 *       "goods_id": 1
 *       "image_url": "http://img.bibicar.cn/bibilogo.png"
 *       "goods_name": "吡吡小淇凌"
 *       "sales": 0
 *       "stock": 1
 *       "price": 20
 *       "type": 1
 *    }
 *    "has_more": 2
 *    "total": 2
 *    "order_id":0
 *    "keyword" :"冰淇凌"
 *    }
 *  }
 *
 */
     //商品列表
    public function goodslistAction(){
         
            $jsonData = require APPPATH .'/configs/JsonData.php';
            
            $this->optional_fields = array('goods_item');
            $this->required_fields = array_merge($this->required_fields, array('session_id'));
            $data = $this->get_request_data();
            $data['shop_id'] =3;
           /*
            $data['order_id']=0;
            $data['page']    =0;
           
            $data['goods_item'] =1;
            
            $data['keyword'] ="";
            */
            $data['order_id'] = $data['order_id'] ? $data['order_id'] : 0 ;
            $data['page']     = $data['page'] ? ($data['page']+1) : 1;
            $data['shop_id'] = $data['shop_id'] ? $data['shop_id'] : 0 ;

            $goodsM = new ShopGoodsModel();
            $where = 'WHERE t1.files <> "" AND t1.stock <> 0 AND t1.status = 1 ';
            if($data['goods_item']){
                 $where .= ' AND t1.goods_item = '.$data['goods_item'].' ';
            }

            if($data['type']){

                 $where .= ' AND t1.type = '.$data['type'].' ';
            }

            if($data['keyword']){
                $goodsM->keyword = $data['keyword'];
                $where .= ' AND t1.goods_name LIKE "%'.$goodsM->keyword.'%" ';
            }

            if($data['shop_id']){

                $where .= ' AND t1.shop_id = '.$data['shop_id'].' ';
            }

            $goodsM->where = $where;
            
            if(isset($jsonData['goods_info'][$data['order_id']])) {

                $goodsM->order = $jsonData['goods_info'][$data['order_id']];

            }
            $goodsM->page = $data['page'];

            $userId = $this->userAuth($data);
           

            $goodsM->currentUser = $userId;

            $lists = $goodsM->getGoodsList($userId);

            $response = $lists;
            $response['order_id'] = $data['order_id'];
            $response['keyword']   = $data['keyword'];
            $this->send($response);
     }
/**
 * @api {POST} /v3/shop/goodsindex 商品详情
 * @apiName goods index
 * @apiGroup GOODS
 * @apiDescription  商品详情
 * @apiPermission anyone
 * @apiSampleRequest http://www.bibicar.cn:8090
 *
 * @apiParam {string} device_identifier 设备唯一标识
 * @apiParam {string} session_id session_id
 * @apiParam {number} [shop_id] 店铺id
 * @apiParam {number} goods_id 商品id
 * 
 * @apiUse Data
 * @apiParamExample {json} 请求样例
 *   POST /v3/shop/shoplist
 *   {
 *     "data": {
 *       "device_identifier":"ce32eaab37220890a063845bf6b6dc1a",
 *       "session_id":"session5845346a59a31",
 *       "goods_id":"1",
 *       "shop_id":"1",
 *       
 *       
 *     }
 *   }
 *
 */
    //商品详情
    public function goodsindexAction(){
                
                $this->required_fields = array_merge($this->required_fields, array('session_id','goods_id'));

                $data = $this->get_request_data();
                $userId = $this->userAuth($data);

            
                $GoodsId=$data['goods_id'];
                
                $GoodsModel = new ShopGoodsModel();

                $GoodsModel->currentUser = $userId;

                $GoodsInfo = $GoodsModel->GetGoodsInfoById($GoodsId,$userId);
                
                $response['goods_info'] = $GoodsInfo;
                
                $this->send($response);

    }

/**
 * @api {POST} /v3/shop/addcart 商品加入购物车
 * @apiName goods addcart
 * @apiGroup GOODS
 * @apiDescription  商品详情
 * @apiPermission anyone
 * @apiSampleRequest http://www.bibicar.cn:8090
 *
 * @apiParam {string} device_identifier 设备唯一标识
 * @apiParam {string} session_id session_id
 * @apiParam {number} shop_id 店铺id
 * @apiParam {number} goods_number 数量
 * @apiParam {number} goods_id 商品id
 * @apiParam {number} sku_id 商品sku_id
 * 
 * @apiUse Data
 * @apiParamExample {json} 请求样例
 *   POST /v3/shop/addcart
 *   {
 *     "data": {
 *       "device_identifier":"ce32eaab37220890a063845bf6b6dc1a",
 *       "session_id":"session5845346a59a31",
 *       "goods_id":"1",
 *       "sku_id":"1",
 *       "goods_number":"1",
 *       "shop_id":"1",
 *       
 *       
 *     }
 *   }
 *
 */
    //加入购物车
public function addcartAction(){
        
        

        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id', 'goods_id','sku_id','goods_number','shop_id')
        );
        $data = $this->get_request_data();
        $userId = $this->userAuth($data);

        $ShopCarM = new ShopCartModel;
        $goods=$ShopCarM->get($userId,$data['goods_id'],$data['sku_id']);
        if($goods){
            $this->send_error(FEED_HAS_LIKED);
        }
        $ShopCarM = new ShopCartModel;
        $time = time();
        $properties['created'] = $time;
        $properties['user_id'] = $userId;
        $properties['goods_id'] = $data['goods_id'];
        $properties['sku_id'] = $data['sku_id'];
        $properties['shop_id'] = $data['shop_id'];
        $properties['goods_number'] = $data['goods_number'];
        $ShopCarM->properties = $properties;
        $id = $ShopCarM->CreateM();
        if($id){
          $goods=$ShopCarM->getCarlist($userId);
        }

        $this->send($goods);
    }
/**
 * @api {POST} /v3/shop/deletecart 从购物车已移除/清空购物车
 * @apiName cart deal
 * @apiGroup GOODS
 * @apiDescription  从购物车已移除/清空购物车 
 * @apiPermission anyone
 * @apiSampleRequest http://www.bibicar.cn:8090
 *
 * @apiParam {string} device_identifier 设备唯一标识
 * @apiParam {string} session_id session_id
 * @apiParam {number} [goods_id] 商品id
 * @apiParam {number} [sku_id] skuid
 * 
 * @apiUse Data
 * @apiParamExample {json} 请求样例
 *   POST /v3/shop/deletecart
 *   {
 *     "data": {
 *       "device_identifier":"ce32eaab37220890a063845bf6b6dc1a",
 *       "session_id":"session5845346a59a31",
 *       "goods_id":"1",
 *       "sku_id":"1",
 *       
 *       
 *     }
 *   }
 *
 */
//从购物车移除 /清空购物车
public function deletecartAction(){
            
            $this->required_fields = array_merge($this->required_fields,array('session_id','goods_id','sku_id'));

            $data = $this->get_request_data();
            $data['page']     = $data['page'] ? ($data['page']+1) : 1;
           
            $sess = new SessionModel();
            $userId = $sess->Get($data);
             
            $ShopCarM = new ShopCartModel;
            $result=$ShopCarM->deleteCart($userId,$data['goods_id'],$data['sku_id']);
            
            
            $goods=$ShopCarM->getCarlist($userId);
            $this->send($goods);

    }

/**
 * @api {POST} /v3/shop/cartlist 购物车列表
 * @apiName goods cartlist
 * @apiGroup GOODS
 * @apiDescription  商品详情
 * @apiPermission anyone
 * @apiSampleRequest http://www.bibicar.cn:8090
 *
 * @apiParam {string} device_identifier 设备唯一标识
 * @apiParam {string} session_id session_id
 * 
 * @apiUse Data
 * @apiParamExample {json} 请求样例
 *   POST /v3/shop/carlist
 *   {
 *     "data": {
 *       "device_identifier":"ce32eaab37220890a063845bf6b6dc1a",
 *       "session_id":"session5845346a59a31",
 *       
 *       
 *     }
 *   }
 *
 */
//购物车列表
public function cartlistAction(){
        
        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id')
        );

        $data = $this->get_request_data();
        $userId = $this->userAuth($data);
        
        $ShopCarM = new ShopCartModel;
        $goods=$ShopCarM->getCarlist($userId);

        $this->send($goods);
    }

/**
 * @api {POST} /v3/shop/createorder 购物车创建订单
 * @apiName order add
 * @apiGroup GOODS
 * @apiDescription  购物车创建订单
 * @apiPermission anyone
 * @apiSampleRequest http://www.bibicar.cn:8090
 *
 * @apiParam {string} device_identifier 设备唯一标识
 * @apiParam {string} session_id session_id
 * @apiParam {number} shop_id 店铺id
 * @apiParam {json} goods_list object
 * @apiParam {string} goods_amount 商品总价
 * @apiParam {string} order_amount 订单总价
 * 
 * @apiUse Data
 * @apiParamExample {json} 请求样例
 *   POST /v3/shop/addorder
 *   {
 *   
 *     "data": {
 *       "device_identifier":"ce32eaab37220890a063845bf6b6dc1a",
 *       "session_id":"session5845346a59a31",
 *       "shop_id":"",
 *       "goods_amount":"1",
 *       "order_amount":"1",
 *       "goods_list":"[{"goods_id":1,"sku_id":1,"buy_num":2},{"goods_id":2,"sku_id":2,"buy_num":2}]",
 *     }
 *   }
 *
 */
    //生成订单
     public function createorderAction(){
        
        
        $this->required_fields = array_merge($this->required_fields,array('session_id','shop_id','goods_amount','order_amount'));

        $data = $this->get_request_data();
        
        $userId = $this->userAuth($data);
         
        $shop_id=3;

        /*
        $data['shop_id']=1;
        $data['goods_amount']=20;
        $data['order_amount']=20;
        $data['goods_list']='[{"goods_id":1,"sku_id":1,"buy_num":2},{"goods_id":2,"sku_id":2,"buy_num":2}]';
        $userId=389;
        */
        $data['goods_list']=str_replace( '\\', '',$data['goods_list']);
       
        if (!json_decode($data['goods_list']) ){

            $this->send_error(CAR_CREATE_FILES_ERROR);
        }

        $files=json_decode($data['goods_list'], true);
        $goods_serialize=serialize($files);

        $order_sn=date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);

        $ShopOrderM = new ShopOrderModel;
        $time = time();
        $properties['order_time']       = $time;
        $properties['user_id']          = $userId;
        $properties['shop_id']          = $data['shop_id'];
        $properties['goods_serialize']  = $goods_serialize;
        $properties['goods_amount']     = $data['goods_amount'];
        $properties['order_amount']     = $data['order_amount'];
        $properties['order_status']     = 1;
        $properties['order_sn']         = $order_sn;
        $ShopOrderM->properties         = $properties;
        $order_id = $ShopOrderM->CreateM();

        if($order_id){

            foreach($files as $k => $value){ 
                $properties=array();
                $ShopOrderGoodsM = new ShopOrderGoodsModel;
                $time = time();
                $properties['order_id']          = $order_id;
                $properties['order_sn']          = $order_sn;
                $properties['goods_id']          = $value['goods_id'];
                $properties['sku_id']            = $value['sku_id'];
                $properties['buy_num']           = $value['buy_num'];
                $properties['user_id']           = $userId;
                $properties['created']           = $time;
                $ShopOrderGoodsM->properties         = $properties;
                $order_goods_id = $ShopOrderGoodsM->CreateM();

                if($order_goods_id){
                    $ShopCarM = new ShopCartModel;
                    $result=$ShopCarM->deleteCart($userId,$value['goods_id'],$value['sku_id']);
                }

            } 
        }                                                                 
        $ShopOrderM = new ShopOrderModel;
        $info=$ShopOrderM->getOrderinfo($userId,$order_id);
        $this->send($info);
     }


/**
 * @api {POST} /v3/shop/orderpay 支付订单
 * @apiName order pay
 * @apiGroup GOODS
 * @apiDescription  调起支付
 * @apiPermission anyone
 * @apiSampleRequest http://www.bibicar.cn:8090
 *
 * @apiParam {string} device_identifier 设备唯一标识
 * @apiParam {string} session_id session_id
 * @apiParam {number} pay_code 支付方式 1微信 2支付宝
 * @apiParam {number} order_id 订单ID
 * @apiParam {number} order_sn 订单号
 * @apiParam {number} pay_fee  支付款数
 * 
 * @apiUse Data
 * @apiParamExample {json} 请求样例
 *   POST /v3/shop/orderpay
 *   {
 *   
 *     "data": {
 *       "device_identifier":"ce32eaab37220890a063845bf6b6dc1a",
 *       "session_id":"session5845346a59a31",
 *       "pay_code":"1",
 *       "pay_fee":"1",
 *       "order_id":"",
 *     }
 *   }
 *  @apiSuccessExample {json} 成功返回:
 *  HTTP/1.1 200 OK
 *  {
 *    "status": 1
 *    "code": 0
 *    "data": {
 *    "appid":"wx8bac6dd603d47d15",
 *    "partnerid":"1424297802",
 *    "nonce_str":"rCXyg8Xdx9oC6PLF"
 *    "package":"Sign=WXP"
 *    "prepay_id":"wx201612230953071fffad40c90251562174",
 *    "timestamp":"14856652552"
 *    "sign": "D8C01AC2916EEB29AD8C66AF150B1047",
 *    }
 *  }
 *
 */
     public function orderpayAction(){ 
        
                $this->required_fields = array_merge($this->required_fields,array('session_id','order_id','order_sn','pay_fee','pay_code'));

                $data = $this->get_request_data();
                
                $userId = $this->userAuth($data);
               
                $ShopOrderM = new ShopOrderModel;
                $info=$ShopOrderM->getOrderinfo($userId,$data['order_id']);
              
                if($info && $data['order_sn'] == $info['order_sn'] && $info['order_status'] == 1){
                  
                        if($data['pay_code'] == 1){

                                $info['order_sn']=$data['order_sn'];
                                $info['pay_fee']= $info['order_amount'];
                                $info['name']='吡吡商品';
                              
                                $notify = new Wxpay();
                               
                                $result=$notify->unifiedorder($info);
                                
                                $sign=$notify->settoSign($result);
                               
                                if($sign == $result['sign']){
                                      $num = $this->GetRandStr(4);  
                                      
                                      
                                      $where['order_sn']=$data['order_sn'];
                                      $attr['pay_code']=1;
                                      $attr['pay_name']=$result['prepay_id'];
                                      $attr['pay_fee'] =$data['pay_fee'];
                                      $attr['pay_time'] =time();
                                      $attr['coupon'] =$num;
                                      $ShopOrderM = new ShopOrderModel;
                                      $resultinfo=$ShopOrderM->update($where,$attr);


                                      $response['appid']    =$result['appid'];
                                      $response['partnerid']=$result['mch_id'];
                                      $response['noncestr']= WxPayApi::getNonceStr();
                                      $response['package']  ="Sign=WXPay";
                                      $response['prepayid']=$result['prepay_id'];
                                      $response['timestamp']=time();
                                    
                                      $sign=$notify->settoSign($response);
                                      $response['sign']=$sign;
                                      $response['type']="Wxpay";
                                      $this->send($response);
                                }else{
                                      $this->send_error(CAR_CREATE_FILES_ERROR);

                                }
                        }elseif($data['pay_code'] == 2){
                                
                               
                                //待优化
                                $num = $this->GetRandStr(4);  
                                $where['order_sn']=$data['order_sn'];
                                $attr['pay_code']=2;
                                $attr['pay_time']=time();
                                $attr['coupon']=$num;
                                $ShopOrderM = new ShopOrderModel;
                                $result=$ShopOrderM->update($where,$attr);

                                $alipayM=new Alipay();
                                $order_sn=$data['order_sn'];
                                $order_amount=$info['order_amount'];
                                $goods_name='吡吡商品';
                                $result=$alipayM->alipay($order_sn,$order_amount,$goods_name);
                                $response['orderstr']=$result;
                                $response['type']="Alipay";
                                $this->send($response);
                               
                        }

                }else{
                        
                        $this->send_error(CAR_CREATE_FILES_ERROR);//订单支付出现错误
                }
                

     }
     //微信回调通知
public function renotifyAction(){

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
            $ShopOrderM = new ShopOrderModel;
            $info=$ShopOrderM->getOrderinfobyordersn($result['out_trade_no']);

            if( $info[0]['order_status'] == 1 ){
                $ShopOrderM->UpdateOrders($result['out_trade_no'],2); 
            }
           
            $msg = "OK";
            $WxPayNotify->SetReturn_code("SUCCESS");
            $WxPayNotify->SetReturn_msg($msg);

            WxpayApi::replyNotify($WxPayNotify->ToXml());
           
        }else{
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
                $ShopOrderM = new ShopOrderModel;
                $info=$ShopOrderM->getOrderinfobyordersn($data['out_trade_no']);
                
                 if( @$info[0]['order_status'] == 1 ){
                     
                     $where['order_sn']=$data['out_trade_no'];
                     $attr['pay_name']=$data['trade_no'];
                     $attr['pay_fee']=$data['total_amount'];
                     $attr['order_status']=2;
                     $ShopOrderM->update($where,$attr); 
                 }
        }
        
}
  


/**
 * @api {POST} /v3/shop/orderindex 订单详情
 * @apiName order index
 * @apiGroup GOODS
 * @apiDescription  订单详情
 * @apiPermission anyone
 * @apiSampleRequest http://www.bibicar.cn:8090
 *
 * @apiParam {string} device_identifier 设备唯一标识
 * @apiParam {string} session_id session_id
 * @apiParam {number} order_id 订单id
 * 
 * @apiUse Data
 * @apiParamExample {json} 请求样例
 *   POST /v3/shop/shoplist
 *   {
 *   
 *     "data": {
 *       "device_identifier":"ce32eaab37220890a063845bf6b6dc1a",
 *       "session_id":"session5845346a59a31",
 *       "order_id":"",
 *     }
 *   }
 *
 */
public function orderindexAction(){
      
        $time = time();
        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id','order_sn')
        );
        $data = $this->get_request_data();
        $userId = $this->userAuth($data);

        $ShopOrderM = new ShopOrderModel;
        $info=$ShopOrderM->getinfo($data['order_sn']);
        
        $this->send($info);
            
}
/**
 * @api {POST} /v3/shop/orderlist 订单列表
 * @apiName order list
 * @apiGroup GOODS
 * @apiDescription  订单列表
 * @apiPermission anyone
 * @apiSampleRequest http://www.bibicar.cn:8090
 *
 * @apiParam {string} device_identifier 设备唯一标识
 * @apiParam {string} session_id session_id
 * 
 * @apiUse Data
 * @apiParamExample {json} 请求样例
 *   POST /v3/shop/shoplist
 *   {
 *     "data": {
 *       "device_identifier":"ce32eaab37220890a063845bf6b6dc1a",
 *       "session_id":"session5845346a59a31",
 *     }
 *   }
 *
 */
public function orderlistAction(){
      
        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id')
        );
        $data = $this->get_request_data();
        $userId = $this->userAuth($data);

        $ShopOrderM = new ShopOrderModel;
        $info=$ShopOrderM->getOrderlist($userId);
        $this->send($info);
            
}

/**
 * @api {POST} /v3/shop/canselorder 取消订单
 * @apiName order cansel
 * @apiGroup GOODS
 * @apiDescription  取消订单
 * @apiPermission anyone
 * @apiSampleRequest http://www.bibicar.cn:8090
 *
 * @apiParam {string} device_identifier 设备唯一标识
 * @apiParam {string} session_id session_id
 * @apiParam {number} order_id 订单ID
 * 
 * @apiUse Data
 * @apiParamExample {json} 请求样例
 *   POST /v3/shop/canselorder 
 *   {
 *     "data": {
 *       "device_identifier":"ce32eaab37220890a063845bf6b6dc1a",
 *       "session_id":"session5845346a59a31",
 *       "order_id":"session5845346a59a31",
 *     }
 *   }
 *
 */

//暂时废弃
public function canselorderAction(){
      
        
        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id','order_id')
        );
        $data = $this->get_request_data();
        $userId = $this->userAuth($data);
        

        $ShopOrderM = new ShopOrderModel;
        $status=4;
        $info=$ShopOrderM->ChangeOrders($userId,$data['order_id'],$status);
        $this->send($info);
            
}


/**
 * @api {POST} /v3/shop/shoppos 改变位置
 * @apiName shop postion
 * @apiGroup GOODS
 * @apiDescription  改变位置
 * @apiPermission anyone
 * @apiSampleRequest https://testapi.bibicar.cn
 *
 * @apiParam {string} device_identifier 设备唯一标识
 * @apiParam {string} session_id session_id
 * @apiParam {number} shop_id 店铺Id
 * @apiParam {number} lat 位置纬度
 * @apiParam {number} lng 位置经度
 * 
 * @apiUse Data
 * @apiParamExample {json} 请求样例
 *   POST /v3/shop/shoppos
 *   {
 *     "data": {
 *       "device_identifier":"ce32eaab37220890a063845bf6b6dc1a",
 *       "session_id":"session5845346a59a31",
 *       "shop_id":"session5845346a59a31",
 *     }
 *   }
 *
 */
public function shopposAction(){
      
        
        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id','shop_id','lat','lng')
        );
        $data = $this->get_request_data();
        $userId = $this->userAuth($data);
        $where['shop_id']=$data['shop_id'];
        $attr['lat']=$data['lat'];
        $attr['lng']=$data['lng'];
        $ShopM = new ShopModel;
        $result=$ShopM->update($where,$attr);
        $response['return_code']="success";
        $this->send( $response);
            
}

/**
 * @api {POST} /v3/shop/checkcoupon 输入劵码
 * @apiName order coupon
 * @apiGroup GOODS
 * @apiDescription  输入劵码
 * @apiPermission anyone
 * @apiSampleRequest https://testapi.bibicar.cn
 *
 * @apiParam {string} device_identifier 设备唯一标识
 * @apiParam {string} session_id session_id
 * @apiParam {string} coupon 劵码
 * 
 * @apiUse Data
 * @apiParamExample {json} 请求样例
 *   POST /v3/shop/checkcoupon
 *   {
 *     "data": {
 *       "device_identifier":"df4871c207120a5d73407318477b97b2",
 *       "session_id":"session5840e2727c418",
 *       "coupon":"hkhhhhhn",
 *     }
 *   }
 *
 */
public function checkcouponAction(){
      
        
        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id','coupon')
        );
        $data = $this->get_request_data();
        $userId = $this->userAuth($data);

        $ShopM = new ShopOrderModel;
        $paramkey='coupon';
        $paramvalue=$data['coupon'];


        $result=$ShopM->checkOrderinfobyparam($paramkey,$paramvalue);
        if(!$result){
            $response['list']=array();
        }else{
            $response['list']=$result;
        }
        
        $this->send($response);
            
}

/**
 * @api {POST} /v3/shop/checkorder 改变订单
 * @apiName order change
 * @apiGroup GOODS
 * @apiDescription  改变订单
 * @apiPermission anyone
 * @apiSampleRequest https://testapi.bibicar.cn
 *
 * @apiParam {string} device_identifier 设备唯一标识
 * @apiParam {string} session_id session_id
 * @apiParam {string} coupon 劵码
 * @apiParam {string} order_sn 订单号
 * 
 * @apiUse Data
 * @apiParamExample {json} 请求样例
 *   POST /v3/shop/checkorder
 *   {
 *     "data": {
 *       "device_identifier":"ce32eaab37220890a063845bf6b6dc1a",
 *       "session_id":"session5845346a59a31",
 *       "coupon":"TEDEDED",
 *       "order_sn":"201621252562255",
 *     }
 *   }
 *
 */
public function checkorderAction(){
      
        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id','coupon','order_sn')
        );
        $data = $this->get_request_data();
        $userId = $this->userAuth($data);

        $where['order_sn']=$data['order_sn'];
        $where['coupon']=$data['coupon'];
        $attr['order_status']=3;
        $ShopM = new ShopOrderModel;
        $result=$ShopM->update($where,$attr);
       
        if($result){
            $ShopOrderM = new ShopOrderModel;
            $info=$ShopOrderM->getOrderinfobyparam('order_sn',$data['order_sn']);
            if($info){
                $user=$info[0]['user_id'];
                $mh = new MessageHelper;
                $userM = new ProfileModel();
                $profile = $userM->getProfile($user);
                $content = '你的劵已使用';
                $mh->refreshNotify($user,$content);
            }
           

            $response['order_info']=$info;
            $response['return_code']="success";
            $response['return_content']="修改成功";
        }else{
            $response['return_code']="success";
            $response['return_content']="修改失败";
        }
        $this->send( $response);
            
}



 public function GetRandStr($len)   
{  
    
    $chars = array(   
        "A", "B", "C", "D", "E", "F", "G",    
        "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",    
        "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",    
        "3", "4", "5", "6", "7", "8", "9"   
    );   
     
    $charsLen = count($chars) - 1;   
    shuffle($chars);     
    $output = "";   
    for ($i=0; $i<$len; $i++)   
    {   
        $output .= $chars[mt_rand(0, $charsLen)];   
    }    
    
    return $output; 

}  


public function testAction(){
     $ShopOrderM = new ShopOrderModel;
     $info=$ShopOrderM->getOrderinfobyordersn('2016123057559798');
     $this->send($info);
}





}

