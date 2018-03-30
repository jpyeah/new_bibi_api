<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/6/27
 * Time: 00:21
 */

class MessageHelper{

    public $handler;

    const SYSTEM_TYPE = 1;
    const COMMENT_TYPE = 2;
    const LIKE_TYPE = 3;

    const RCLOUD_APP_KEY='sfci50a7c23ei';
    const RCLOUD_APP_SECRET='rTdKRNXsuO';

    public function __construct(){

        $this->handler = new RcloudServerAPI(self::RCLOUD_APP_KEY, self::RCLOUD_APP_SECRET);

    }

    public function commentNotify($toId,$content){

        $data['content'] = $content;
        $data = json_encode($data);

        $rs = $this->handler->messagePublish(self::COMMENT_TYPE, array($toId),"RC:TxtMsg",$data,$content);

        return $rs;
    }

    public function likeNotify($toId,$content){

        $data['content'] = $content;
        $data = json_encode($data);

        $rs = $this->handler->messagePublish(self::LIKE_TYPE, array($toId),"RC:TxtMsg",$data,$content);

        return $rs;
    }

    public function recommendNotify($toId, $carId){

        if(!is_array($toId)){
             $toId=array($toId);
        }
        $carModel = new CarSellingModel();
        $carInfo = $carModel->GetCarInfoById($carId);

        $imgUrl = $carInfo['files'][0]['file_url'];
        $content['title'] = '车辆推荐';
        $content['content'] = $carInfo['car_name'];
        $content['imageUri'] = $imgUrl;
        $content['url'] = 'bibicar://gotoCar?car_id='.$carInfo['car_id'];
        $content = json_encode($content);
        $pushContent="你有新的车辆推荐";
        
        $rs = $this->handler->messagePublish(self::SYSTEM_TYPE,$toId,"RC:ImgTextMsg",$content,$pushContent);
        return $rs;
    }

    public function testNotify($toId, $carId){


        $carModel = new CarSellingModel();
        $carInfo = $carModel->GetCarInfoById($carId);

        $imgUrl = $carInfo['files'][0]['file_url'];
        $content['title'] = '版本更新，及时收到更有料的资讯';
        $content['content'] = $carInfo['car_name'];
        $content['imageUri'] = $imgUrl;
        $content['url'] = 'http://baidu.com';
        $content = json_encode($content);
        $pushContent="赶紧更新版本了";

        $rs = $this->handler->messagePublish(self::SYSTEM_TYPE,array(544),"RC:ImgTextMsg",$content,$pushContent);
        return $rs;
    }

    //自定义图文消息推送(文章活动推送) type 1: 文章 2: 活动
    public function ArtNotify($toId, $post_id){

        if(!is_array($toId)){
            $toId=array($toId,5673,5815);
        }
        $content['title'] = 'BiBi探店第4期 | 独家授权：如果想把爱车换成宾利，这家店或许是你最好的选择';
        $content['content'] = "";
        $content['imageUri'] = "http://img.bibicar.cn/Fokh-uLD4h3Gr71rHmxZa9P7IIF-";
        $content['url'] = '';
        $content['type'] = 1;
        $content['related_id'] =7151;
        $content = json_encode($content);
        $pushContent="BiBi探店第4期|独家授权：如果想把爱车换成宾利，这家店或许是你最好的选择";
        $pushData = $content;
        $rs = $this->handler->messagePublish(self::SYSTEM_TYPE,$toId,"BiBi:ArtMsg",$content,$pushContent,$pushData);
        return $rs;
    }

    public function PushNotify(){

        $platform=array("ios","android");
        $audience['userid']=array(544,389,5815,342);
        $audience['is_to_all']=false;

      // $notification 按操作系统类型推送消息内容，如 platform 中设置了给 ios 和 android 系统推送消息，而在 notifications 中只设置了 ios 的推送内容，则 android 的推送内容为最初 alert 设置的内容。（非必传）
      $notification["alert"] = "test";//	默认推送消息内容，如填写了 ios 或 android 下的 alert 时，则推送内容以对应平台系统的 alert 为准。（必传）
      //$notification["ios"]="" //设置 iOS 平台下的推送及附加信息。（非必传）
     //$notification["android"] 设置 Android 平台下的推送及附加信息。（非必传）
//      $notification["ios"]["alert"]="test-ios"; //ios平台下的推送消息内容，传入后默认的推送消息内容失效，不能为空。（非必传）
//        $notification["ios"]["extras"]["type"]=1; //ios平台下的附加信息，如果开发者自己需要，可以自己在 App 端进行解析。（非必传）
//        $notification["ios"]["extras"]["id"]=7151;  //ios平台下的附加信息，如果开发者自己需要，可以自己在 App 端进行解析。（非必传）
//     $notification[ android ][ alert ] android平台下的推送消息内容，传入后默认的推送消息内容失效，不能为空。（非必传）
//     $notification[ android ][ extras ]  android平台下的附加信息，如果开发者自己需要，可以自己在 App 端进行解析。（非必传）
        $notification["alert"]="test-push"; //ios平台下的推送消息内容，传入后默认的推送消息内容失效，不能为空。（非必传）
       $notification["ios"]["extras"]["type"]=1; //ios平台下的附加信息，如果开发者自己需要，可以自己在 App 端进行解析。（非必传）
        $notification["ios"]["extras"]["id"]=7151;  //ios平台下的附加信息，如果开发者自己需要，可以自己在 App 端进行解析。（非必传）
        $rs = $this->handler->push( $platform,$audience,$notification );
        return $rs;
    }

    //自定义图文消息推送(文章活动推送) type 1: 文章 2: 活动
    public function ActNotify($toId, $carId){
        if(!is_array($toId)){
            $toId=array($toId,5673,5815);
        }
        $imgUrl = "http://img.bibicar.cn/1506483583.9144";
        $content['title'] = 'BiBiCar最新活动:分享赢豪礼，抱得美人归';
        $content['content'] = " ";
        $content['imageUri'] = $imgUrl;
        $content['url'] = 'http://share.bibicar.cn/views/center/raffle.html';
        $content['type'] = 2;
        $content['related_id'] =0;
        $content = json_encode($content);
        $pushContent="BiBiCar最新活动:分享赢豪礼，抱得美人归";
        $pushData = $content;
        $rs = $this->handler->messagePublish(self::SYSTEM_TYPE,$toId,"BiBi:ArtMsg",$content,$pushContent,$pushData);
        return $rs;
    }
    //自定义图文消息推送(车辆推送)
    public function CarNotify($toId, $carId){

        if(!is_array($toId)){
            $toId=array($toId,5673,5815);
        }

        $carModel = new CarSellingV5Model();
        $carInfo = $carModel->GetCarInfoById($carId);

        $title = '天呐，您的梦想车——'.$carInfo['brand_info']['brand_name'].$carInfo['series_info']['series_name'].'上新啦';
        $imgUrl = "http://img.bibicar.cn/bibilogo.png";
        $content['title'] = $title;
        $content['content'] = $carInfo['car_name'];
        $content['imageUri'] = $carInfo['files'][0]['file_url'];
        $content['url'] = '';
        $content['type'] = 3;
        $content['related_id'] =$carId;
        $content['car_info']=$carInfo['board_time']."/".$carInfo['mileage']."万公里";
        $content['price']=$carInfo['price'];
        $content = json_encode($content);
        $pushContent=$title;
        $pushData = $content;
        $rs = $this->handler->messagePublish(self::SYSTEM_TYPE,$toId,"BiBi:CarMsg",$content,$pushContent,$pushData);
        return $rs;
    }
    //自定义文字消息推送(车辆推送)
    public function TextNotify($toId,$carId){
        if(!is_array($toId)){
            $toId=array($toId,5673,5815);
        }
        $content="恭喜您的车辆——奥迪A6已经通过审核！";
        $data['content'] = $content;
        $data = json_encode($data);
        $pushContent="车辆通过审核";
        $rs = $this->handler->messagePublish(self::SYSTEM_TYPE,$toId,"BiBi:TextMsg",$data,'恭喜您的车辆——奥迪A6已经通过审核');
        return $rs;
    }


    public function systemNotify($toId,$content){
       
        $data['content'] = $content;
        $data = json_encode($data);
        $rs = $this->handler->messagePublish(self::SYSTEM_TYPE, array($toId),"RC:TxtMsg",$data,'你有新的消息');
        return $rs;
    }

    public function refreshNotify($toId,$content){
       
        $json = array('content'=>$content , 'extra'=>'fresh');

        $json = json_encode($json);

        $rs = $this->handler->messagePublish(self::SYSTEM_TYPE , array($toId),"RC:TxtMsg",$json,'你有新的消息');

        return $rs;
    }

    public function wzNotify($toId, $info){

       $info = '{
			"date":"2013-12-29 11:57:29",
			"area":"316省道53KM+200M",
			"act":"16362 : 驾驶中型以上载客载货汽车、校车、危险物品运输车辆以外的其他机动车在高速公路以外的道路上行驶超过规定时速20%以上未达50%的",
			"code":"",
			"fen":"6",
			"money":"100",
			"handled":"0"
			}';

        $content = json_decode($info,true);

        $json = array('content'=>$content , 'extra'=>'');

        $json = json_encode($json);

        $rs = $this->handler->messagePublish(self::SYSTEM_TYPE , array($toId),"BBMsg",$json,'你有新的违章消息');

        return $rs;
    }
}