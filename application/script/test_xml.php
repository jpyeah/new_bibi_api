 <?php

     $xml = '<xml>
  <appid><![CDATA[wx2421b1c4370ec43b]]></appid>
  <attach><![CDATA[支付测试]]></attach>
  <bank_type><![CDATA[CFT]]></bank_type>
  <fee_type><![CDATA[CNY]]></fee_type>
  <is_subscribe><![CDATA[Y]]></is_subscribe>
  <mch_id><![CDATA[10000100]]></mch_id>
  <nonce_str><![CDATA[5d2b6c2a8db53831f7eda20af46e531c]]></nonce_str>
  <openid><![CDATA[oUpF8uMEb4qRXf22hE3X68TekukE]]></openid>
  <out_trade_no><![CDATA[1409811653]]></out_trade_no>
  <result_code><![CDATA[SUCCESS]]></result_code>
  <return_code><![CDATA[SUCCESS]]></return_code>
  <sign><![CDATA[B552ED6B279343CB493C5DD0D78AB241]]></sign>
  <sub_mch_id><![CDATA[10000100]]></sub_mch_id>
  <time_end><![CDATA[20140903131540]]></time_end>
  <total_fee>1</total_fee>
  <trade_type><![CDATA[JSAPI]]></trade_type>
  <transaction_id><![CDATA[1004400740201409030005092168]]></transaction_id>
</xml>';//要发送的xml 
     $url = 'http://testapi.bibicar.cn/v3/shop/renotify';//接收XML地址 

     $header[] = "Content-type: text/xml";//定义content-type为xml 
     $ch = curl_init(); //初始化curl 
     curl_setopt($ch, CURLOPT_URL, $url);//设置链接 
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设置是否返回信息 
     curl_setopt($ch, CURLOPT_HTTPHEADER, $header);//设置HTTP头 
     curl_setopt($ch, CURLOPT_POST, 1);//设置为POST方式 
     curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);//POST数据 
     $response = curl_exec($ch);//接收返回信息 
     if(curl_errno($ch)){//出错则显示错误信息 
     print curl_error($ch); 
     } 
     curl_close($ch); //关闭curl链接 
     echo $response;//显示返回信息 

?>