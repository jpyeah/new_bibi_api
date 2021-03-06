<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/19
 * Time: 下午10:42
 */

class AppModel extends PdoDb{


    static public  $table = 'bibi_device_info';

    public function init(){

        parent::__construct();
    }

    public function registerDevice($data){

        $id = $this->insert(self::$table , $data);

        RedisDb::setValue('di_'.$data['device_identifier'].'', true);


        return $id;

    }

    public function getDevice($device_identifier){


        $di = RedisDb::getValue('di_'.$device_identifier.'');

        if(!$di){
            $table = self::$table;
            //查找是否有该device_identifier
            $sql = "SELECT id FROM {$table} WHERE `device_identifier` = :device_identifier";
            $result = $this->query($sql, array(':device_identifier'=>$device_identifier));
            return $result;
        }
        else{

            return $di;
        }


    }

    public function getIdentifierInfo($device_identifier){
            $table = self::$table;
            //查找是否有该device_identifier
            $sql = "SELECT device_resolution FROM {$table} WHERE `device_identifier` = :device_identifier ORDER BY id DESC LIMIT 1";
            $result = $this->query($sql, array(':device_identifier'=>$device_identifier));

            $str = $result[0]['device_resolution'];
            $num = strlen($str);
            switch($num ){
                case  7:
                      $size = explode('*',$str)[0];

                      if($size == 320){
                          $num = 1;
                      }elseif( $size == 375) {
                          $num = 2;
                      }elseif($size == 414){
                          $num = 3;
                      }
                      break;
                case  8:
                      $num = 3;
                    //$size = explode('*',$str)[0];
                      break;
                case  9:
                      $num = 3;
                      $size = explode('*',$str)[0];
                      break;
            }
            return  $num;
    }


    public function getAppVersion($type){

        $sql = "SELECT * FROM bibi_new_version WHERE  type =".$type;

        $result = $this->query($sql);

        if($result){
            return $result[0];
        }else{
            return  array();
        }

    }

    public function getStartImg(){

        $sql="SELECT * FROM bibi_new_app_start_img WHERE `status` = 1";

        $result = $this->query($sql);

        if($result){

              $list = array();
              foreach($result as $k){
                     $list[]=$k['img_href'];
              }
              return $list;

        }else{

            return array();
        }
    }



} 