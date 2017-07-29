<?php
/**
 * Created by sublime.
 * User: jpjy
 * Date: 15/10/19
 * Time: 上午11:50
 * note: 文章管理
 */

class NotifyController extends ApiYafControllerAbstract
{

    public function alipayAction(){
            $info['order_sn']=time();
            $info['order_amount']=1;
            $info['goods_name']='冰淇1';
           $alipayM=new Alipay();
           $result=$alipayM->alipay($info);
           $response['orderstr']=$result;
           print_r($response);

    }

   



}

