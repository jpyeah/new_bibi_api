<?php
/**
 * Alipay.com Inc.
 * Copyright (c) 2004-2014 All Rights Reserved.
 */
require_once 'alipay/AopClient.php';
/**
 *
 * @author wangYuanWai
 * @version $Id: Test.hp, v 0.1 Aug 6, 2014 4:20:17 PM yikai.hu Exp $
 */

class Alipay{

	public function alipay($order_sn=0,$order_amount=0,$goods_name=0,$notifyUrl='https://api.bibicar.cn/v3/shop/alinotify'){
                   
					$aop = new AopClient ();
					$aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
					$aop->appId = '2016122304540684';
					$aop->rsaPrivateKeyFilePath = APPLICATION_PATH . "/application/library/alipay/key/rsa_private_key.pem";
					$aop->alipayPublicKey=APPLICATION_PATH . "/application/library/alipay/key/rsa_public_key.pem";
					$aop->apiVersion = '1.0';
					$aop->postCharset='UTF-8';
					$aop->format='json';
                    
					$request = new AlipayTradeAppPayRequest();
					$request->setNotifyUrl($notifyUrl);
					$request->setBizContent("{".
					"\"out_trade_no\":\"$order_sn\"," .
					"\"product_code\":\"QUICK_MSECURITY_PAY\"," .
					"\"total_amount\":\"$order_amount\"," .
					"\"subject\":\"$goods_name\"," .
					"}");
					$result = $aop->toexecute ($request); 
                    return $result;
                    /*
					$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
					$resultCode = $result->$responseNode->code;
					if(!empty($resultCode)&&$resultCode == 10000){
					echo "成功";
					} else {
					echo "失败";
					}

                    */

	}


	public function alirefund($order_sn,$trade_no,$refund_amount){


/*
                    $aop = new AopClient ();
                    $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
                    $aop->appId = '2016122304540684';
                    $aop->rsaPrivateKeyFilePath = APPLICATION_PATH . "/application/library/alipay/key/rsa_private_key.pem";
                    $aop->alipayPublicKey=APPLICATION_PATH . "/application/library/alipay/key/rsa_public_key.pem";
                    $aop->apiVersion = '1.0';
                    $aop->postCharset='UTF-8';
                    $aop->format='json';

                    $request = new AlipayTradeRefundRequest();

                    $request->setBizContent("{" .
                        "\"out_trade_no\":\"$order_sn\"," .
                       // "\"trade_no\":\"$trade_no\"," .
                        "\"refund_amount\":$refund_amount," .
                        "\"refund_reason\":\"正常退款\"," .
                        "  }");

                    $result = $aop->refundexecute($request);

                    return $result;
*/

        $aop = new AopClient ();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = '2016122304540684';
        $aop->rsaPrivateKeyFilePath = APPLICATION_PATH . "/application/library/alipay/key/rsa_private_key.pem";
        $aop->alipayPublicKey=APPLICATION_PATH . "/application/library/alipay/key/rsa_public_key.pem";
        $aop->apiVersion = '1.0';
       // $aop->signType = 'RSA';
        $aop->postCharset='UTF-8';
        $aop->format='json';

        $request = new AlipayTradeRefundRequest();

        $request->setBizContent("{" .
            "\"out_trade_no\":\"$order_sn\"," .
            // "\"trade_no\":\"$trade_no\"," .
            "\"refund_amount\":$refund_amount," .
           // "\"refund_reason\":\"正常退款\"," .
            "  }");

        $result = $aop->refundexecute($request);

        return $result;

    }


    //处理回调
	public function notify($data){


        $sign=$data['sign'];
        
        $sign_type=$data['sign_type'];
        unset($data['v3/shop/alinotify']);
        unset($data['v3/usercarpact/alinotify']);
        unset($data['v3/rentalcar/alinotify']);
        unset($data['sign']);
        unset($data['sign_type']);

		ksort($data);
		
		$aop = new AopClient ();
		//$aop->alipayPublicKey=APPLICATION_PATH . "/application/library/alipay/key/rsa_public_key.pem";
		//$rsaPublicKeyFilePath=APPLICATION_PATH . "/application/library/alipay/key/rsa_public_key.pem";
		$rsaPublicKeyFilePath="MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDDI6d306Q8fIfCOaTXyiUeJHkrIvYISRcc73s3vF1ZT7XN8RNPwJxo8pWaJMmvyTn9N4HQ632qJBVHf8sxHi/fEsraprwCtzvzQETrNRwVxLO5jVmRGi60j8Ue1efIlzPXV9je9mkjzOmdssymZkh2QhUrCmZYI/FCEa3/cNMW0QIDAQAB";
		$nofity_sign=$aop->generateSign($data,$sign_type);

		foreach ($data as $sysParamKey => $sysParamValue) {
			$requestsign .= "$sysParamKey=".urldecode($sysParamValue). "&";
		}
		$requestsign = substr($requestsign, 0, -1);
	

	    $pubKey=$rsaPublicKeyFilePath;
			$res = "-----BEGIN PUBLIC KEY-----\n" .
				wordwrap($pubKey, 64, "\n", true) .
				"\n-----END PUBLIC KEY-----";

		$result =openssl_verify($requestsign,base64_decode($sign), $res);
		//释放资源

        return $result;

	}


    //处理回调
    public function alinotify($data){


        $sign=$data['sign'];

        $sign_type=$data['sign_type'];
        unset($data['v3/usercarpact/selleralinotify']);
        unset($data['v3/usercarpact/alinotify']);
        unset($data['sign']);
        unset($data['sign_type']);

        ksort($data);

        $aop = new AopClient ();
        //$aop->alipayPublicKey=APPLICATION_PATH . "/application/library/alipay/key/rsa_public_key.pem";
        //$rsaPublicKeyFilePath=APPLICATION_PATH . "/application/library/alipay/key/rsa_public_key.pem";
        $rsaPublicKeyFilePath="MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDDI6d306Q8fIfCOaTXyiUeJHkrIvYISRcc73s3vF1ZT7XN8RNPwJxo8pWaJMmvyTn9N4HQ632qJBVHf8sxHi/fEsraprwCtzvzQETrNRwVxLO5jVmRGi60j8Ue1efIlzPXV9je9mkjzOmdssymZkh2QhUrCmZYI/FCEa3/cNMW0QIDAQAB";
        $nofity_sign=$aop->generateSign($data,$sign_type);

        foreach ($data as $sysParamKey => $sysParamValue) {
            $requestsign .= "$sysParamKey=".urldecode($sysParamValue). "&";
        }
        $requestsign = substr($requestsign, 0, -1);


        $pubKey=$rsaPublicKeyFilePath;
        $res = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($pubKey, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";

        $result =openssl_verify($requestsign,base64_decode($sign), $res);
        //释放资源

        return $result;

    }



}

