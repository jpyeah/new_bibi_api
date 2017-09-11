<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/6
 * Time: 下午1:30
 */
class MyFocusModel extends PdoDb
{

    public function __construct()
    {

        parent::__construct();
        self::$table = 'bibi_my_focus';
    }

    public function saveProperties()
    {
        $this->properties['user_id'] = $this->user_id;
        $this->properties['type_id']= $this->type_id;
        $this->properties['type']= $this->type;
        $this->properties['created_at'] = $this->created_at;
    }


    public function getUserFocus($userId,$page=1){

        $pageSize = 10;
        $number = ($page-1)*$pageSize;
        $sqlFocus = '
            SELECT
            friendship_id
            FROM
            `bibi_friendship` 
            WHERE user_id = '.$userId.'
        ';
        $result =$this->query($sqlFocus);
        $result = $this->implodeArrayByKey( 'friendship_id', $result);
        $sql ='
           SELECT
           t1.id,t1.type,t1.type_id,t1.created_at,t1.user_id,
           t2.avatar,t2.nickname
           FROM `bibi_my_focus` as t1
           LEFT JOIN `bibi_user_profile` as t2
           ON t2.user_id = t1.user_id
          ';
        $sqlNearByCnt = '
            SELECT
            COUNT(id) AS total
            FROM `bibi_my_focus`
            ';

        $sql .=" WHERE t1.user_id in ($result) ORDER BY `created_at` DESC";
        $sqlNearByCnt.=" WHERE user_id in ($result ) ORDER BY `created_at` DESC";

        $sql .= ' LIMIT '.$number.' , '.$pageSize.' ';

        $list = $this->query($sql);
        $total = $this->query($sqlNearByCnt)[0]['total'];

        $count =count($list);
        foreach($list as $key => $val){

            $list[$key]['type_info']= $this->getFocusTypeInfo($val['type'],$val['type_id']);
        }

        $res['list']=$list;
        $res['total']=$total;
        $res['has_more'] = (($number + $count) < $total) ? 1 : 2;

        return $res;

    }


    public function getFocusTypeInfo($type,$type_id){

           switch($type){
               //车辆详情
               case 1:
                   $info = $this->getCarInfo($type_id);
                   break;
               //加入话题
               case 2:
                   $info = $this->getThemeInfo($type_id);
                   break;
               //关注用户
               case 3:
                   $info = $this->getUserInfo($type_id);
                   break;
               //文章评论
               case 4:
                   $info = $this->getFeedInfo($type_id);
                   break;
           }

           return $info;

    }

    public function getCarInfo($hash){

        $sql = '
            SELECT
            car_name,price,hash,files,car_type
            FROM `bibi_car_selling_list`
            WHERE hash = "' . $hash . '"
        ';
        $car = @$this->query($sql)[0];


        if (!$car) {
            return array();
        }

        $images = unserialize($car['files']);
        if($car['car_type']==2){
            $car['file_img'] = "http://thirtimg.bibicar.cn/". $images[0]['key']."?imageMogr2/auto-orient/thumbnail/1000x/strip";
        }else{
            $car['file_img'] = IMAGE_DOMAIN.$images[0]['key']."?imageMogr2/auto-orient/thumbnail/1000x/strip";
        }
        unset($car['files']);
        return $car;

    }


    public function getThemeInfo($theme_id){

        $sql='
        SELECT 
        t1.id,t1.theme,t1.title,t1.post_file,t1.sort,t1.is_skip,t1.user_id,
        t2.avatar,t2.nickname
        FROM
        `bibi_themelist` as t1
        LEFT JOIN `bibi_user_profile` as t2 
        ON t2.user_id = t1.user_id
        WHERE
        t1.id ='.$theme_id.'
        ';
        $theme=$this->query($sql);
        if($theme){
            $info=@$theme[0];
            $info["post_file"]="http://img.bibicar.cn/".$info['post_file'];
        }else{
            $info=array();
        }
        return $info;

    }

    public function getUserInfo($user_id){

           $profileM = new ProfileModel();

           $info = $profileM->getProfile($user_id);

           return $info;

    }

    public function getFeedInfo($comment_id){

           $CommentM =  new Commentv1Model();

           $comment = $CommentM->getCommentInfo($comment_id);

           $feed_id=$comment['feed_id'];

           $FeedM = new Feedv1Model();

           $info = $FeedM->GetFeedInfo($feed_id);

           $info['comment_info']=$comment;

           return $info;
    }


}