<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/19
 * Time: 下午11:30
 */


class ProfileModel extends PdoDb{

    static public $table = 'bibi_user_profile';

    public function __construct(){

        parent::__construct();
    }

    public function initProfile($data){

        $this->insert(self::$table , $data);
    }


    public function updateProfileByKey($user_id , $data){

        $where = array('user_id' => $user_id);

        $result = $this->updateByPrimaryKey(self::$table, $where, $data);
        return $result;
    }

    public function getProfile($user_id){
        $table = self::$table;
        $sql = "SELECT avatar, signature, age, constellation, nickname, gender, sort,bibi_no,type,company,balance FROM {$table} WHERE `user_id` = :user_id";
        $profile = $this->query($sql, array(':user_id'=>$user_id));
        @$profile = $profile[0];

//        if($profile['year'] == 0 || $profile['month'] == 0 || $profile['day'] == 0){
//
//            $profile['birth'] = '';
//        }
//        else{
//
//            $profile['birth'] = $profile['year'] . '-' . $profile['month'] . '-' . $profile['day'];
//        }
//
//        unset($profile['year']);
//        unset($profile['month']);
//        unset($profile['day']);


        return $profile;
    }

    public function getUserMoney($user_id){

        $table = self::$table;

        $sql = "SELECT avatar,nickname,balance FROM {$table} WHERE `user_id` = :user_id";


        $profile = $this->query($sql, array(':user_id'=>$user_id));

        @$profile = $profile[0];

        return $profile;

    }



    public function getUserInfos($users){

        if($users){

            $str = '(' . implode(',' , $users) . ')';

            $sql = 'SELECT `user_id`, `nickname`, `avatar`,`type` FROM `bibi_user_profile` WHERE `user_id` in '.$str.'';

            $results = $this->query($sql);

            return $results;
        }
        else{

            return array();
        }

    }


    public function gethotgirl($page=1,$userId=0){

        $pageSize = 33;
        $number = ($page-1) * $pageSize;

        if($page < 5){

        $sql = 'SELECT 
                  user_id,nickname,avatar,sort FROM `bibi_user_profile` 
                   ORDER BY sort DESC LIMIT ' . $number . ' , ' . $pageSize .'';       
        $users = $this->query($sql);

        $total = 100;

        $items['list']=array();
        $items['list']=$users;
        $count = count($users);

        $items['has_more'] = (($number+$count+24) < $total) ? 1 : 2;
        $items['total'] = $total;

        }else{

        $items['list']=new stdClass();
        $total = 100;
        $items['has_more'] = 2;
        $items['total'] = $total;

        }
        return  $items;

    }

    public function gettypeofuser($tag,$page=1,$userId=0,$pageSize=10){

         $number = ($page-1) * $pageSize;

         $sql='SELECT  user_id,nickname,avatar,sort,signature,type,tag,intro FROM `bibi_user_profile`'; 
        
        if($tag == 1){
          
          $sql="( select user_id,nickname,avatar,sort,signature,type,tag,intro from bibi_user_profile where tag=2 limit 5 ) union ( select user_id,nickname,avatar,sort,signature,type,tag,intro  from bibi_user_profile where tag=3 limit 5 ) union ( select user_id,nickname,avatar,sort,signature,type,tag,intro  from bibi_user_profile where tag=4 limit 5 ) union ( select user_id,nickname,avatar,sort,signature,type,tag,intro  from bibi_user_profile where tag=5 limit 5 ) order by tag desc";
          
        }else if($tag == 2){
          //最牛大咖
          $sql.=' where tag = 2';
          //$sql.='ORDER BY sort DESC LIMIT ' . $number . ' , ' . $pageSize .'';
        }else if($tag  == 3){
          //职场精英
          $sql.=' where tag = 3';
          //$f='ORDER BY sort DESC LIMIT ' . $number . ' , ' . $pageSize .'';
        }else if($tag  == 4){
          //娱乐达人
          $sql.=' where tag = 4';
         // $f='ORDER BY sort DESC LIMIT ' . $number . ' , ' . $pageSize .'';
        }else if($tag  == 5){
          //专家顾问
          $sql.=' where tag = 5';
         // $f='ORDER BY sort DESC LIMIT ' . $number . ' , ' . $pageSize .'';
        }



        if($tag != 1 ){
            $sql.=' ORDER BY sort DESC LIMIT ' . $number . ' , ' . $pageSize .'';
            $sqlNearByCnt = '
            SELECT
            COUNT(id) AS total
            FROM
            `bibi_user_profile` 
            WHERE  tag ='.$tag.'
            ';
            $total = $this->query($sqlNearByCnt)[0]['total'];
            $users = $this->query($sql);

            if($users){
                foreach($users as $k => $val){

                    $friendShipM = new FriendShipModel();
                    $friendShipM->currentUser =$val['user_id'];
                    $otherId=$val['user_id'];
                    $users[$k]['fans_num'] = $friendShipM->fansNumCnt();

                    $friendShip = $friendShipM->getMyFriendShip($userId, $otherId);
                    $users[$k]['is_friend'] = isset($friendShip['user_id']) ? 1 : 2;
                }
            }

            $items['list']=array();
            $items['list']=$users;
            $count = count($users);
            $items['has_more'] = (($number + $count) < $total) ? 1 : 2;
            $items['total'] = $total;
        }else{

            $users = $this->query($sql);
            $items['list']=array();
            //$items['list']=$users;
            
            $dk=array();
            $jy=array();
            $dr=array();
            $gw=array();
            foreach($users as $k => $val){

                if($val['tag']==2){
                   $dk[$k]=array();
                   $dk[$k]=$val;
                }

                if($val['tag']==3){
                   $jy[$k]=array();
                   $jy[$k]=$val;
                }

                if($val['tag']==4){
                   $dr[$k]=array();
                   $dr[$k]=$val;
                }

                if($val['tag']==5){
                   $gw[$k]=array();
                   $gw[$k]=$val;

                }

            }

            $items['list']['dk']=array_merge($dk);
            $items['list']['jy']=array_merge($jy);
            $items['list']['dr']=array_merge($dr);
            $items['list']['gw']=array_merge($gw);

        }
        


        /*
        $items['list']=new stdClass();
        $total = 100;
        $items['has_more'] = 2;
        $items['total'] = $total;
        */
        
        return  $items;

    }



    /*
    * @param company_id 公司用户id
    * @ins 获取公司销售
     */
    public function getcompanyuserlist($page=1,$company_id){
          
        $pageSize = 10;
        $number = ($page-1) * $pageSize;

        $sql='SELECT  user_id,nickname,avatar,sort,signature,type,tag,intro FROM `bibi_user_profile`'; 

        $sql.=' where company = '.$company_id;

        //$users=$this->query($sql);

        $sql .= ' LIMIT '.$number.' , '.$pageSize.' ';



        $sqlCnt = ' SELECT   COUNT(user_id) AS total FROM `bibi_user_profile` where company ='.$company_id;

        $users = $this->query($sql);


        $total = @$this->query($sqlCnt)[0]['total'];
        
        $count = count($users);

        $list['user_list'] = $users ;
        $list['has_more'] = (($number+$count) < $total) ? 1 : 2;
        $list['total'] = $total;


        return $list;


    }



    /*
    *  获取车行
    */
    public function getCompanylist($page=1){

        if(@$this->pageSize){
            $pageSize=$this->pageSize;
        }else{
            $pageSize=20;
        }

        $number = ($page-1) *$pageSize;


        $sql ="SELECT user_id,nickname,avatar,sort,signature,type,tag,intro FROM `bibi_user_profile` WHERE type = 2 order by sort desc";

        $sqlCnt = 'SELECT count(user_id) AS total FROM `bibi_user_profile` WHERE type =2 order by sort desc';

        $sql .= ' LIMIT '.$number.' , '.$pageSize.' ';
        $users = $this->query($sql);

        $count =count($users);
        foreach($users as $k => $val){

               $carM = new CarSellingModel();
               $carM->pageSize=5;
               $carM->page =1;
            $sale=$carM->getUserPublishCar($val['user_id']);

            $sold_num=$carM->countUserCarNum($val['user_id'],4);
            $saling_num=$carM->countUserCarNum($val['user_id'],11);

            $company= new CompanyUserModel();
            $info=$company->getInfoById($val['user_id']);
            if($info){
                $users[$k]['phone']=$info['telenumber'];
                $users[$k]['address']=$info['address'];

            }

            $users[$k]['saling_num']=$saling_num;
            $users[$k]['sold_num']=$sold_num;
            $users[$k]['car_list']=$sale;

        }

        $total = @$this->query($sqlCnt)[0]['total'];


        $list['user_list'] = $users ;
        $list['has_more'] = (($number+$count) < $total) ? 1 : 2;
        $list['total'] = $total;


        return $list;


    }



    /*
    *  获取车行v2.0.0
    */
    public function getCompanylistV1($page=1){

        if(@$this->pageSize){
            $pageSize=$this->pageSize;
        }else{
            $pageSize=20;
        }

        $number = ($page-1) *$pageSize;


        $sql ="SELECT user_id,nickname,avatar,sort,signature,type,tag,intro FROM `bibi_user_profile` WHERE type = 2 order by sort desc";

        $sqlCnt = 'SELECT count(user_id) AS total FROM `bibi_user_profile` WHERE type =2 order by sort desc';

        $sql .= ' LIMIT '.$number.' , '.$pageSize.' ';
        $users = $this->query($sql);

        $count =count($users);
        foreach($users as $k => $val){

            $carM = new CarSellingV1Model();
            $carM->pageSize=5;
            $carM->page =1;
            $sale=$carM->getUserPublishCar($val['user_id']);

            $sold_num=$carM->countUserCarNum($val['user_id'],4);
            $saling_num=$carM->countUserCarNum($val['user_id'],11);

            $company= new CompanyUserModel();
            $info=$company->getInfoById($val['user_id']);
            if($info){
                $users[$k]['phone']=$info['telenumber'];
                $users[$k]['address']=$info['address'];

            }
            $users[$k]['saling_num']=$saling_num;
            $users[$k]['sold_num']=$sold_num;
            $users[$k]['car_list']=$sale;
        }

        $total = @$this->query($sqlCnt)[0]['total'];


        $list['user_list'] = $users ;
        $list['has_more'] = (($number+$count) < $total) ? 1 : 2;
        $list['total'] = $total;


        return $list;


    }


    public function updateSortNum($userId, $action='add'){

        $condition = $action == 'add' ? 'sort = sort + 1' : 'sort = sort - 1';

        $sql = '
            UPDATE
            `bibi_user_profile`
            SET
            '.$condition.'
            WHERE
            `user_id` = '.$userId.'
            ;
        ';

        $this->exec($sql);

    }



}