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


}