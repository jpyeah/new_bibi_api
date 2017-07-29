<?php
/**
 * Created by PhpStorm.
 * User: jp
 * Date: 16/4/14
 * Time: 14:51
 */

class YouxinController extends ApiYafControllerAbstract {


    public function getbrandAction(){
           
           $sql = "SELECT brand_id,brand_name FROM bibi_car_brand_list WHERE is_hot =1";

           $pdo = new PdoDb;
           $list = $pdo->query($sql);
           $num = 0;
           foreach($list as $k => $val){
                   $sql = "SELECT baidu_brand_id,brand_name FROM brand_list WHERE brand_name ="."'".$val['brand_name']."'";
                   $result = $pdo->query($sql);
                   if(!$result){
                      print_r($val['brand_name']."<br/>");
                   }else{
                      $sql = "UPDATE brand_list SET brand_id = ".$val['brand_id'].",is_hot = 1 WHERE baidu_brand_id =".$result[0]['baidu_brand_id'];
                      $sql1 = "UPDATE brand_series SET brand_id = ".$val['brand_id']." WHERE baidu_brand_id =".$result[0]['baidu_brand_id'];
                      $results = $pdo->query($sql);
                      $resultst = $pdo->query($sql1);
                      print_r($resultst);
                      print_r("<br/>");
                   }
                   

           }
          
    }


    public function getseriesAction(){
           
           $sql = "SELECT brand_id,brand_name FROM bibi_car_brand_list WHERE is_hot =1";

           $pdo = new PdoDb;
           $list = $pdo->query($sql);
           $num = 0;
           foreach($list as $k => $val){
                   $sql = "SELECT brand_id,brand_series_name,brand_series_id FROM bibi_car_brand_series WHERE brand_id =".$val['brand_id'];
                   $result = $pdo->query($sql);
         /*          if(!$result){
                      print_r($val['brand_name']);
                      print_r("<br />");
                      print_r("<br />");
                      print_r("<br />");
                   }else{
                      print_r($result);
                      print_r("<br />");
                      print_r("<br />");
                      print_r("<br />");
                   }
          */
                   foreach($result as $d => $j){
                          
                          $sql = "SELECT brand_id,brand_series_name,brand_series_id FROM brand_series WHERE series_name ="."'".$j['brand_series_name']."'";
                          $result = $pdo->query($sql);
                          
                           if(!$result){
                              
                              print_r($j['brand_id']);
                              print_r("<br />");
                              print_r($j['brand_series_name']);
                              print_r("<br />");
                              print_r("<br />");
                              print_r("<br />");
                           }else{
                              foreach($result as $i =>$h){
                                 $sql = "UPDATE brand_series SET series_id = ".$j['brand_series_id']." WHERE brand_series_id =".$h['brand_series_id'];
                                 $results = $pdo->query($sql);
                                 print_r($results);
                                 print_r("<br />");
                                 print_r("<br />");
                                 print_r("<br />");
                              }
                              

                           }


                   }

           }
          
    }
  public function changebrandAction(){
         $pdo = new PdoDb;
         $sql = "SELECT brand_id,series_id,car_id,id,price FROM youxin_car_list";
        
         $list = $pdo->query($sql);

         foreach($list as $k => $val){

         $sql="SELECT brand_id,series_id FROM youxin_brand_series WHERE brand_series_id =".$val['series_id'];
         $res = $pdo->query($sql);
            if($res){

               $sql="UPDATE youxin_car_list SET bibi_series_id=".$res[0]['series_id'].",bibi_brand_id=".$res[0]['brand_id'].",is_change=1  WHERE car_id =".$val['car_id'];
               $info = $pdo->query($sql);
               $price=explode("  ", $val['price']);
               $price=str_replace("万", " ", $price[0]);
               $sql="UPDATE youxin_selling_list SET brand_id=".$res[0]['brand_id'].",series_id =".$res[0]['series_id'].",price =".$price.",is_change=1 WHERE car_id =".$val['car_id'];
               $ret = $pdo->query($sql);
              
            }

         }

  }

   //优信车辆替换配对品牌
   public function checkcarAction(){
          
           $sql = "SELECT brand_id,series_id,model_id,car_id,mileage,car_name,files,board_time,price,exchange_time,car_color,contact_name,contact_phone,contact_address,is_transfer,updated,maintain,insurance_due_time,check_expiration_time FROM youxin_selling_list WHERE is_change = 1 AND model_id is not null AND hash is null ";
           $pdo = new PdoDb;
           $list = $pdo->query($sql);

           foreach($list as $k => $val){
                  
                   $data["brand_id"]=$val['brand_id'];
                   $data["series_id"]=$val['series_id'];
                   //model_id
                   $se_sql ="SELECT model_id FROM bibi_car_series_model WHERE series_id =".$data["series_id"];
                   $se_ret = $pdo->query($se_sql);
                   $data["model_id"]=$se_ret[0]['model_id'];
                   $data['car_type']=2;
                   $data['car_status']=1;
                   $data['car_name']=$val['car_name'];

                   $data["verify_status"] =11;
                   $data["car_intro"] ="本车来源第三方平台二手车,想了解更多信息请联系客服";
                   $data['hash']=uniqid();

                   if (strpos($val['mileage'],'万公里')){
                   $data["mileage"]=str_replace("万公里", " ", $val['mileage']);
                   }elseif(strpos($val['mileage'],'公里')){
                   $data["mileage"]=str_replace("公里", " ", $val['mileage'])/10000;
                   }

                   $data["board_time"]=date("Y",strtotime($val['board_time']));
                   $data["price"] =$val['price'];
                   $data["car_color"]=$this->deal_color($val['car_color']);

                   $data["platform_id"] =1;
                   $data["platform_url"] ="http://che.xin.com/".$val['car_id'].".html";
                   $data["platform_name"] ="优信二手车";
                   $data["platform_location"]="http://che.xin.com";

                   $data["contact_name"] ="优信二手车";
                   $data["contact_phone"]="400-113-8778";
                   $data["contact_address"] ="南山";
                   $data['files'] =$val['files'];
                   $data['exchange_time']=str_replace("次", " ", $val['exchange_time']);
                   
                   if ($val['maintain']=="4S店定期"){
                       $data["maintain"]=1;
                   }elseif($val['mileage']=="4S店非定期"){
                       $data["maintain"]=2;
                   }else{
                       $data["maintain"]=0;
                   }
                   $data["is_transfer"]=$val['is_transfer'];

                   $data["insurance_due_time"]=date("y-m-d",strtotime($val['insurance_due_time']));
                   $data["check_expiration_time"]=date("y-m-d",strtotime($val['check_expiration_time']));
                   $data['created']=strtotime($val['updated']);
                   $data['updated']=strtotime($val['updated']);
                   //print_r($data);
                   //$sql = "INSERT INTO  bibi_car_selling_list(brand_id,series_id,model_id,car_id,mileage,board_time,price,exchange_time,color,contact_name,contact_phone,contact_address,is_transfer,insurance_due_time,check_expiration_time) VALUES($brand_id,$series_id,$model_id,$mileage,$board_time,$price,$exchange_time,$color,$contact_name,$contact_phone,$contact_address,$is_transfer,$insurance_due_time,$check_expiration_time)";
                   //print_r($data);
                   $ret=$pdo->insert('bibi_car_selling_list',$data);
                if($ret){
                   $sql2="UPDATE youxin_selling_list SET hash="."'".$data["hash"]."'"." WHERE car_id =".$val['car_id'];
                   $ret = $pdo->query($sql2);
                   print_r($ret);
                 }
          
          



           }



                  

    
   }

   public function test_priceAction(){
            $val['mileage']="1公里";

      
            if (strpos($val['mileage'],'万公里')){
                   $data["mileage"]=str_replace("万公里", " ", $val['mileage']);
            }elseif(strpos($val['mileage'],'公里')){
                   $data["mileage"]=str_replace("公里", " ", $val['mileage'])/10000;
            }
                   
          print_r($data);
   }

   public function deal_color($type){
         
        switch ($type){
                  
                   case '黑色':
                        $color =1;
                        break;
                   case '红色':
                        $color =2;
                        break;
                   case '深灰色':
                        $color =3;
                        break;
                   case '粉红色':
                        $color =4;
                        break;
                   case '银灰色':
                        $color =5;
                        break;
                   case '紫色':
                        $color =6;
                        break;
                   case '白色':
                        $color =7;
                        break;
                   case '蓝色':
                        $color =8;
                        break;
                   case '香槟色':
                        $color =9;
                        break;
                   case '绿色':
                        $color =10;
                        break;
                   case '黄色':
                        $color =11;
                        break;
                   case '咖啡色':
                        $color =12;
                        break;
                   case '橙色':
                        $color =13;
                        break;
                   case '多彩色':
                        $color =14;
                        break;
                   default:
                        $color =1;

           }
        return $color;

   }





}