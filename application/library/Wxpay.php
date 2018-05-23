<?php
require_once "wxpay/WxPay.Api.php";
//require_once 'wxpay/WxPay.PayNotify.php';

class Wxpay extends WxPayNotify
{

	public function unifiedorder($info)
	{   
		
		//统一下单
		$input = new WxPayUnifiedOrder();

		$input->SetBody($info['name']);
		$input->SetAttach('南山分店');
		$input->SetOut_trade_no($info['order_sn']);
		$input->SetTotal_fee($info['pay_fee']*100);
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 600));
		//$input->SetGoods_tag("test");
		$input->SetNotify_url("http://testapi.bibicar.cn/v3/shop/renotify"); //https://api.bibicar.cn/v3/shop/renotify
		$input->SetTrade_type("APP");
		$result = WxPayApi::unifiedOrder($input);
		return $result;
	}

	//预约车
	public function unifiedpact($info){

        $input = new WxPayUnifiedOrder();
        $input->SetBody("预约看车");
        $input->SetAttach('吡吡汽车');
        $input->SetOut_trade_no($info['pact_no']);
        $input->SetTotal_fee(1);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        //$input->SetGoods_tag("test");
        $input->SetNotify_url("http://testapi.bibicar.cn/v3/usercarpact/wxnotify");
        $input->SetTrade_type("APP");
        $result = WxPayApi::unifiedOrder($input);
        return $result;
    }

	
	public function NotifyProcess($data, &$msg)
	{
		//echo "处理回调";
		
		
		if(!array_key_exists("openid", $data) ||
			!array_key_exists("product_id", $data))
		{
			$msg = "回调数据异常";
			return false;
		}
		 
		$openid = $data["openid"];
		$product_id = $data["product_id"];
		
		//统一下单
		$result = $this->unifiedorder($openid, $product_id);
		if(!array_key_exists("appid", $result) ||
			 !array_key_exists("mch_id", $result) ||
			 !array_key_exists("prepay_id", $result))
		{
		 	$msg = "统一下单失败";
		 	return false;
		 }
		
		$this->SetData("appid", $result["appid"]);
		$this->SetData("mch_id", $result["mch_id"]);
		$this->SetData("nonce_str", WxPayApi::getNonceStr());
		$this->SetData("prepay_id", $result["prepay_id"]);
		$this->SetData("result_code", "SUCCESS");
		$this->SetData("err_code_des", "OK");
		return true;
	}

	public function settoSign($array){
        
		//签名步骤一：按字典序排序参数
		ksort($array);
		$string =$this->ToUrltoParams($array);
		//签名步骤二：在string后加入KEY
		$string = $string . "&key=".WxPayConfig::KEY;
		//签名步骤三：MD5加密
		$string = md5($string);
		//签名步骤四：所有字符转为大写
		$result = strtoupper($string);
		return $result;

	}

		public function ToUrltoParams($array)
	{
		$buff = "";
		foreach ($array as $k => $v)
		{
			if($k != "sign" && $v != "" && !is_array($v)){
				$buff .= $k . "=" . $v . "&";
			}
		}
		
		$buff = trim($buff, "&");
		return $buff;
	}


	 public function getRandChar($length){
			   $str = null;
			   $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
			   $max = strlen($strPol)-1;

			   for($i=0;$i<$length;$i++){
			    $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
			   }

			   return $str;
	}


	public function refund(){

	     $input = new WxPayRefund();
	     $input->SetTransaction_id('4001962001201705191531388215');
         $input->SetOut_refund_no(time());
         $input->SetTotal_fee(100);
         $input->SetRefund_fee(100);
         $input->SetOp_user_id('oqPPRs53zN6c9li0Rc3q9t2x6jlg');

         $result = WxPayApi::refund($input);

         return $result;





    }





}



