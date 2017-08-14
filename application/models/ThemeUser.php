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

    public function getThemeUser($theme_id){

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
        limit 10
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
        $count=$this->query($sqlCnt)[0];

        $list['users']=$user;
        $list['total']=$count['total'];

        return $list;

    }


}