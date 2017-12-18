<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/12
 * Time: 下午7:00
 */
class CarSellingExtraInfoModel extends PdoDb
{

    //public static $table = 'bibi_car_selling_list';
    public $brand_info;
    //public static $visit_user_id = 0;


    public function __construct()
    {
        parent::__construct();
        self::$table = 'bibi_car_selling_list_extra_info';
    }

    public function getExtrainfolist(){

        $sql ="SELECT * FROM bibi_car_selling_list_extra_info_list";

        $list = $this->query($sql);

        foreach($list as $k =>$val){

               switch($val['type']){
                   case 1:
                       $items1['type_name']='常用配置';
                       $items1['type_id']=1;
                       $items1['list'][]=$val;
                       break;
                   case 2:
                       $items2['type_name']='智能系统';
                       $items2['type_id']=2;
                       $items2['list'][]=$val;
                       break;
                   case 3:
                       $items3['type_name']='驾驶员辅助系统';
                       $items3['type_id']=3;
                       $items3['list'][]=$val;
                       break;
                   case 4:
                       $items4['type_name']='视觉辅助系统';
                       $items4['type_id']=4;
                       $items4['list'][]=$val;
                       break;
                   case 5:
                       $items5['type_name']='LED灯光系统';
                       $items5['type_id']=5;
                       $items5['list'][]=$val;
                       break;
                   case 6:
                       $items6['type_name']='运动系统';
                       $items6['type_id']=6;
                       $items6['list'][]=$val;
                       break;
               }
        }

        $arr=[$items1,$items2,$items3,$items4,$items5,$items6];

        return $arr;
    }

    public function updateExtraInfo($car_id,$hash,$ids){


        $sql ="SELECT * FROM bibi_car_selling_list_info WHERE car_id=".$car_id;

        $res = $this->query($sql);

        if($res){
            unset($res[0]['car_id']);
            unset($res[0]['hash']);
            unset($res[0]['id']);
            foreach($res[0] as $k => $val){

                $update[$k]=0;
            }

            $id = $this->updateByPrimaryKey('bibi_car_selling_list_info',['car_id'=>$car_id],$update);

            if($ids){
                $infos = $this->getExtraInfoByIds($ids);
                $items = array();
                foreach ($infos as $k => $val) {
                    $items[$val['alias']] = 1;
                }

                $this->updateByPrimaryKey('bibi_car_selling_list_info', ['car_id' => $car_id], $items);
            }

        }else{
            if($ids){

                $infos = $this->getExtraInfoByIds($ids);

                foreach($infos as $k => $val){
                    $items[$val['alias']]=1;
                }
                $insert = $items;
                $insert['car_id']=$car_id;
                $insert['hash']=$hash;
                $id = $this->insert('bibi_car_selling_list_info',$insert);

            }


        }

    }

    public function getExtraInfo(){

        $sql ="SELECT * FROM bibi_car_selling_list_extra_info_list";

        $sql .= $this->where;

        $list = $this->query($sql);


        return $list;
    }

    public function getExtra($hash){

        $sql ="SELECT * FROM bibi_car_selling_list_extra_info WHERE hash ='".$hash."'";

        $res = $this->query($sql);

        if($res){

            $str = $res[0]['ids'];

            $last_str = substr($str, -1);

            if($last_str == ','){
                $str = substr($str,0,strlen($str)-1);

            }


            $this->where = '  WHERE id in ('.$str.')';

            $list = $this->getExtraInfo();

            return $list ? $list : array();

        }else{
            return array();
        }

    }


    public function getExtraInfoByIds($ids){

        $sql ="SELECT * FROM bibi_car_selling_list_extra_info_list";

        $sql .= '  WHERE id in ('.$ids.')';

        $list = $this->query($sql);

        if($list){
            return $list;
        }else{
            return array();
        }
    }

    public function addExtrainfo($car_id,$hash,$ids){

           $infos = $this->getExtraInfoByIds($ids);

           foreach($infos as $k => $val){
               $items[$val['alias']]=1;
           }
           $insert = $items;
           $insert['car_id']=$car_id;
           $insert['hash']=$hash;

           $id = $this->insert('bibi_car_selling_list_info',$insert);
           return $id;

    }

    public function getInfo($car_id){

           $sql ="SELECT * FROM bibi_car_selling_list_info WHERE car_id=".$car_id;

           $res = $this->query($sql);

           $result =  $res ? $res[0]: array();

           if($result){

               foreach($result as $k =>$val){

                      if($val){
                          $items[$k]=$val;
                      }
               }
                unset($items['id']);
                unset($items['hash']);
                unset($items['car_id']);

               $sql= "SELECT * FROM bibi_car_selling_list_extra_info_list ";

               $res = $this->query($sql);
               $list=array();
               foreach($items as $k =>$val){
                   foreach($res as $j =>$h){
                          if($k == $h['alias']){
                              $list[]=$h;
                          }
                   }
               }

               return $list;

           }else{

               return array();
           }


    }


    public function getInfobyhash($hash){

        $sql ="SELECT * FROM bibi_car_selling_list_info WHERE hash= '".$hash." '";

        $res = $this->query($sql);

        $result =  $res ? $res[0]: array();

        return $result;

    }

    public function deleteCarInfoById( $carId){

        $sql = 'DELETE FROM `bibi_car_selling_list_info` WHERE  `hash`="'.$carId.'"';

        $this->execute($sql);

    }







}