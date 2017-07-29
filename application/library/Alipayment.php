<?php
/**
 * Alipay.com Inc.
 * Copyright (c) 2004-2014 All Rights Reserved.
 */
require_once  APPLICATION_PATH .'/application/vendor/riverslei/payment/autoload.php';
require_once  APPLICATION_PATH .'/application/vendor/riverslei/payment/examples/testNotify.php';

use Payment\Common\PayException;
use Payment\Client\Refund;
use Payment\Config;

use Payment\Client\Notify;

use TodChan\Alipay\AopClient;
use TodChan\Alipay\Request\AlipayTradeRefundRequest;


/**
 *
 * @author wangYuanWai
 * @version $Id: Test.hp, v 0.1 Aug 6, 2014 4:20:17 PM yikai.hu Exp $
 */

class Alipayment{

    public function alitest($order_sn,$trade_no,$refund_amount){



       // $aliConfig = require_once __DIR__ . '/../aliconfig.php';

        $aliConfig = [

            'use_sandbox'               => false,// 是否使用沙盒模式

            'partner'                   => '2088521058960402',
            'app_id'                    => '2016122304540684',
            'sign_type'                 => 'RSA',// RSA  RSA2
            'ali_public_key'            => APPLICATION_PATH . "/application/library/alipay/key/rsa_public_key.pem",
            'rsa_private_key'           => APPLICATION_PATH . "/application/library/alipay/key/rsa_private_key.pem",

            // 可以填写文件路径，或者密钥字符串  当前字符串是 rsa2 的支付宝公钥(开放平台获取)

            // 可以填写文件路径，或者密钥字符串  我的沙箱模式，rsa与rsa2的私钥相同，为了方便测试

            'limit_pay'                 => [

            ],// 用户不可用指定渠道支付当有多个渠道时用“,”分隔

            // 与业务相关参数
            'notify_url'                => 'https://api.bibicarc.cn/v3/notify/ali',
            'return_url'                => 'https://helei112g.github.io/',

            'return_raw'                => true,// 在处理回调时，是否直接返回原始数据，默认为 true

        ];
        $refundNo = time() . rand(1000, 9999);
        $data = [
            'out_trade_no' => $order_sn,
            'refund_fee' => $refund_amount,
            'reason' => '测试帐号退款',
            'refund_no' => $refundNo,
        ];

        try {
            $ret = Refund::run(Config::ALI_REFUND, $aliConfig, $data);
        } catch (PayException $e) {
            echo $e->errorMessage();
            exit;
        }

        return $ret;
       // echo json_encode($ret, JSON_UNESCAPED_UNICODE);

    }


	public function alirefund($order_sn,$trade_no,$refund_amount,$refundNo){


        $aliConfig =[

            'use_sandbox'               => false,// 是否使用沙盒模式

            'partner'                   => '2088521058960402',
            'app_id'                    => '2016122304540684',
            'sign_type'                 => 'RSA',// RSA  RSA2
           'ali_public_key'            => APPLICATION_PATH . "/application/library/alipay/alikey/rsa_public_key.pem",
            'rsa_private_key'           => APPLICATION_PATH . "/application/library/alipay/alikey/rsa_private_key.pem",

//            'ali_public_key'            => 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC2hiUZWGGTBu9P3nrsw1zm1XJRL1iy+37Xobp6S+Utj4fRLzCg5IgMCILo0UnSgK6F6f1FOSj6gsEcuRKAbMMvGFcm48MN+exEf6C+OdTILsZn+pcZeTcCYCh+obXeF5c2E0pk5z30M7UmOAQ10dFLjfQaTj8gT3EeGXYi1wP0swIDAQAB',
//            'rsa_private_key'           => 'MIICXQIBAAKBgQC2hiUZWGGTBu9P3nrsw1zm1XJRL1iy+37Xobp6S+Utj4fRLzCg5IgMCILo0UnSgK6F6f1FOSj6gsEcuRKAbMMvGFcm48MN+exEf6C+OdTILsZn+pcZeTcCYCh+obXeF5c2E0pk5z30M7UmOAQ10dFLjfQaTj8gT3EeGXYi1wP0swIDAQABAoGBAIGnxM6+Q4HHmVOo/LUXCfVHhk85TM7HbBEM54RcSU4V+SqVVPvVmvbBTQzJLqGDm3WxA6KaugtJupgGt6fWmsayUkiAS5Vi7R0GAanEAqmSGX82jffTUSZrtEcM6mXgO6WU2F7x5XSXTSteNUG63inXIVrjCMj8Hxhc3Y705g4BAkEA8wm7GRAfMzkt668RjstprdFSiiMNjkei1zH5/4bYxlf8fox1s7DxRnTSFUxCD5RVLOO7+SFeHMYGHqI63CkdgQJBAMBCMtdQLC+juWs5yhyLXhqG/JsFHmuVhWhIvB9uhM1wVPlq7wvCB69KO/GIO3cDNUfOViXZRihfsFiWArEFFDMCQFBugv3raPfxz3G1UZE5XnMI2FEhAqZ4rLqdLohTX0Bc9BIJeBaM03ymwrQLtb0kMQAXKilr0pKhMntG40XjUYECQFRR9EhYgjiWnvC3FLx2J1yNDWbT1OasWilFlTRX3WjYtnv5eUP34jTv4uBotmPZBVor1b6dz1ZPuWDw0ddfed8CQQDMSiZo1DQyau2TUkZFyKjV7zKVwXPUGlSowu5byWRv8eAqgQvB2pJz1cv3n6PQcIHdMdYjwTUU7Z0R5Ds2uVjB',

            'limit_pay'                 => [

            ],// 用户不可用指定渠道支付当有多个渠道时用“,”分隔

            // 与业务相关参数
            'notify_url'                => 'https://api.bibicar.cn/v3/notify/alinotify',
            'return_url'                => 'https://api.bibicar.cn/',
            'return_raw'                => true,// 在处理回调时，是否直接返回原始数据，默认为 true
        ];


        $data = [
            'out_trade_no' => $order_sn,
            'refund_fee' => $refund_amount,
            'reason' => '测试帐号退款',
            'refund_no' => $refundNo,
        ];

        try {
            $ret = Refund::run(Config::ALI_REFUND, $aliConfig, $data);
        } catch (PayException $e) {
            echo $e->errorMessage();
            exit;
        }

        return $ret;


    }


    public function alinotify(){



        $callback = new Alipaynotify();

        $type = 'ali_charge';// ali_charge wx_charge  cmb_charge

	    $config =  [

            'use_sandbox'               => false,// 是否使用沙盒模式

            'partner'                   => '2088521058960402',
            'app_id'                    => '2016122304540684',
            'sign_type'                 => 'RSA',// RSA  RSA2
//            'ali_public_key'            => APPLICATION_PATH . "/application/library/alipay/key/rsa_public_key.pem",
//            'rsa_private_key'           => APPLICATION_PATH . "/application/library/alipay/key/rsa_private_key.pem",

            'ali_public_key'            => 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC2hiUZWGGTBu9P3nrsw1zm1XJRL1iy+37Xobp6S+Utj4fRLzCg5IgMCILo0UnSgK6F6f1FOSj6gsEcuRKAbMMvGFcm48MN+exEf6C+OdTILsZn+pcZeTcCYCh+obXeF5c2E0pk5z30M7UmOAQ10dFLjfQaTj8gT3EeGXYi1wP0swIDAQAB',
            'rsa_private_key'           => 'MIICXQIBAAKBgQC2hiUZWGGTBu9P3nrsw1zm1XJRL1iy+37Xobp6S+Utj4fRLzCg5IgMCILo0UnSgK6F6f1FOSj6gsEcuRKAbMMvGFcm48MN+exEf6C+OdTILsZn+pcZeTcCYCh+obXeF5c2E0pk5z30M7UmOAQ10dFLjfQaTj8gT3EeGXYi1wP0swIDAQABAoGBAIGnxM6+Q4HHmVOo/LUXCfVHhk85TM7HbBEM54RcSU4V+SqVVPvVmvbBTQzJLqGDm3WxA6KaugtJupgGt6fWmsayUkiAS5Vi7R0GAanEAqmSGX82jffTUSZrtEcM6mXgO6WU2F7x5XSXTSteNUG63inXIVrjCMj8Hxhc3Y705g4BAkEA8wm7GRAfMzkt668RjstprdFSiiMNjkei1zH5/4bYxlf8fox1s7DxRnTSFUxCD5RVLOO7+SFeHMYGHqI63CkdgQJBAMBCMtdQLC+juWs5yhyLXhqG/JsFHmuVhWhIvB9uhM1wVPlq7wvCB69KO/GIO3cDNUfOViXZRihfsFiWArEFFDMCQFBugv3raPfxz3G1UZE5XnMI2FEhAqZ4rLqdLohTX0Bc9BIJeBaM03ymwrQLtb0kMQAXKilr0pKhMntG40XjUYECQFRR9EhYgjiWnvC3FLx2J1yNDWbT1OasWilFlTRX3WjYtnv5eUP34jTv4uBotmPZBVor1b6dz1ZPuWDw0ddfed8CQQDMSiZo1DQyau2TUkZFyKjV7zKVwXPUGlSowu5byWRv8eAqgQvB2pJz1cv3n6PQcIHdMdYjwTUU7Z0R5Ds2uVjB',

            // 可以填写文件路径，或者密钥字符串  当前字符串是 rsa2 的支付宝公钥(开放平台获取)

            // 可以填写文件路径，或者密钥字符串  我的沙箱模式，rsa与rsa2的私钥相同，为了方便测试

            'limit_pay'                 => [

            ],// 用户不可用指定渠道支付当有多个渠道时用“,”分隔

            // 与业务相关参数
            'notify_url'                => 'https://testapi.bibicar.cn/v3/notify/alinotify',
            'return_url'                => 'https://testapi.bibicar.cn/',
            'return_raw'                => false,// 在处理回调时，是否直接返回原始数据，默认为 true
        ];

        try {

            $retData = Notify::getNotifyData($type, $config);// 获取第三方的原始数据，未进行签名检查

            $ret = Notify::run($type, $config, $callback);// 处理回调，内部进行了签名检查

        } catch (PayException $e) {
            echo $e->errorMessage();
            exit;
        }

        //print_r($ret);exit;

        return $ret;



    }




}

