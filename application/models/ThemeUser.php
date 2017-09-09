<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/6
 * Time: 下午1:30
 */
class ThemeUserModel extends PdoDb
{

    public function __construct()
    {

        parent::__construct();
        self::$table = 'bibi_themelist_user';
    }

    public function saveProperties()
    {
        $this->properties['user_id'] = $this->user_id;
        $this->properties['theme_id']= $this->theme_id;
        $this->properties['created'] = $this->created;
    }


    public function getTheme($theme_id=1,$userId){

        $sql='
        SELECT 
        *
        FROM
        `bibi_themelist_user`
        WHERE
        theme_id ='.$theme_id.'
        ';

        if($userId){

            $sql .= 'AND user_id = '.$userId;
        }
        $theme=$this->query($sql);
        if($theme){
            $info=@$theme[0];
        }else{
            $info=array();
        }

        return $info;
    }

    public function getThemeUser($theme_id,$page=1){

        $pageSize = 10;

        $number = ($page-1)*$pageSize;

        $sql='
        SELECT 
        t1.*,
        t2.avatar,t2.nickname
        FROM
        `bibi_themelist_user` as t1
        LEFT JOIN `bibi_user_profile` as t2 
        ON t2.user_id = t1.user_id 
        WHERE
        t1.theme_id ='.$theme_id.'
        LIMIT ' . $number . ' , ' . $pageSize . '
        ';

        $sqlCnt='
        SELECT 
        count(*) as total
        FROM
        `bibi_themelist_user` as t1
        LEFT JOIN `bibi_user_profile` as t2 
        ON t2.user_id = t1.user_id 
        WHERE
        t1.theme_id ='.$theme_id.'
        ';

        $user=$this->query($sql);
        $total=$this->query($sqlCnt)[0]['total'];
        $count=count($user);

        foreach($user as $k =>$val){

            $friendShipM = new FriendShipModel();

            $friendShipM->currentUser = $val['user_id'];

            $user[$k]['friend_num'] = $friendShipM->friendNumCnt();

            $user[$k]['fans_num']   = $friendShipM->fansNumCnt();
            unset($user[$k]['theme_id']);

        }

        $list['users']=$user;
        $list['has_more'] = (($number + $count) < $total) ? 1 : 2;
        $list['total'] = $total;

        return $list;
    }


}