<?php
/*
*
*更新bibi_no 
*
* 
 */ 
class PushcarController extends ApiYafControllerAbstract{
      
 
  public function newcarAction(){

         $CarSelling=new CarSellingModel;
         $car=$CarSelling->getnewcartopush();
         $data['series_id']=$car[0]['series_id'];
         //$data['series_id']=2045;
         $user=$CarSelling->getSameDreamCarUser($data);

         if($user){
            $arr=array();
            foreach($user['car_users'] as $k =>$val){
                    $arr[] = $val['user_id'];
            }

           $arr[]=544;
           $Msg=new MessageHelper;
           $carId=$car[0]['hash'];
           $Msg->recommendNotify($arr,$carId);

         }
            
  }

    /**
     *推送是否有未审核的车
     */
  public function checkhasAction(){

       $CarSelling = new CarSellingModel;

       $count=$CarSelling->getUnCheckedCarCount();

       if($count){
           $data['mobile']='18823732410';
           $code=$count;
           $res = Common::sendSMS($data['mobile'],array($code),"173286");
           print_r($res);
       }else{
           echo "has no checked car ";
       }


  }

  public function testcarAction(){
          $arr=544;
          $carId='576bb220c300c';
          $Msg=new MessageHelper;
          $Msg->recommendNotify($arr,$carId);
  }
  
}
