<?php
/**
 * Created by PhpStorm.
 * User: jp
 * Date: 16/4/14
 * Time: 14:51
 */
use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
class YichearticleController extends ApiYafControllerAbstract {


    public function getarticlebyoneAction(){
          
          exit;
      $url ="http://api.ycapp.yiche.com/appnews/GetStructNews?newsId=62244&ts=20161128204736&plat=2&theme=0&version=7.4";
           $html=file_get_contents($url);
           $data=json_decode($html,true)['data'];
            
           
           $title=$data['title'];
           $source=$data['src'];
           $html_url=$data['shareData']["newsLink"];
           $content=$data["content"];
           foreach($content as $key=>$value){
                   
                   if($value['type']==2){
                       $url=$value['content'];
                       break;
                   }
           }
           
           /*
           $items=array();
           foreach($content as $key =>$value){
                  $items[$key]["type"]=$value["type"];
                  $items[$key]["content"] =$value["content"];

           }
           print_r($items);exit;
           */
           $feed_type=4;
           $user_id  =389;
           $created=time();
           $post_content=$title;
           $post_file=serialize($content);
           $image_url=$data['shareData']["newsImg"];
           $feed_from=$source;

$sql = "INSERT INTO bibi_feeds(feed_type,user_id,post_content,post_files,image_url,created,updated,feed_from,html_url) VALUES("."'".$feed_type."'".","."'".$user_id."'".","."'".$post_content."'".","."'".$post_file."'".","."'".$image_url."'".","."'".$created."'".","."'".$created."'".","."'".$source."'".","."'".$html_url."'".")";
           exit;
           $pdo = new PdoDb;
           print_r($sql);
           $list = $pdo->query($sql);
           print_r($list);
    }

    public function pushtouserAction(){

            exit;
            $pdo = new PdoDb;
            $sql = "SELECT user_id,vin_no,engine_no,verify_status,car_no From bibi_car_selling_list WHERE car_type = 3 AND verify_status = 11";
            $list = $pdo->query($sql);
            foreach($list as $key =>$value){

                   $classno =substr($value['vin_no'], -6);
                   $engineno=substr($value['engine_no'], -6);
                   $hphm    =$value['car_no'];
                   $city    =$value[''];

                   $wz=new WeiZhang();
                    $time=time();
                    $HascheckcarM= new HascheckcarModel();
                    $HascheckcarM->user_id  = $value['user_id'];
                    $HascheckcarM->city     = $city;
                    $HascheckcarM->hphm     = $hphm;
                    $HascheckcarM->engineno = $engineno;
                    $HascheckcarM->classno  = $classno;
                    $HascheckcarM->created  = $time;
                    $HascheckcarM->saveProperties();
                    $HascheckcarId = $HascheckcarM->CreateM();
                    /*
                    $city=$data['city'];
                    $hphm=$data['hphm'];   //车牌号码
                    $classno=$data['classno'];    //车架号
                    $engineno=$data['engineno'];    //发动机号
                     */
                    $result=$wz->query($city,$hphm,$engineno,$classno);

            }
            /* 
            $toId=544; 
            $carId="5848d1c2f1e87";
            $mh = new MessageHelper;
            $result=$mh->wzNotify($toId,$carId);
            */
            print_r($list);exit;
            
    }
    



}