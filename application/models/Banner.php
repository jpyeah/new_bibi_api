<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/6
 * Time: 下午1:30
 */
class BannerModel extends PdoDb
{

    public function __construct()
    {

        parent::__construct();
        self::$table = 'bibi_banner';
    }

    public function saveProperties()
    {
        $this->properties['user_id'] = $this->user_id;
        $this->properties['theme']   = $this->theme;
        $this->properties['created'] = $this->created;
        $this->properties['post_file'] = $this->post_file;
        $this->properties['title'] = $this->title;
    }

    public function getbanners(){

        $sql ='
               SELECT 
               id,post_file,user_id,theme,title,created,sort,is_skip,feed_num,address
               FROM `bibi_banner`
               WHERE is_hot=1  
               ';
        $sql .= ' ORDER BY sort DESC';

        $bannerlist = $this->query($sql);

        $banners=array();
        foreach($bannerlist as $key =>$value){

            $banners[$key]['imgUrl']="http://img.bibicar.cn/".$value["post_file"];
            $banners[$key]['appUrl']=$value["theme"];
            $banners[$key]['title']=$value["title"];
            $banners[$key]['link']="/topic/".$value["id"];
            if($value["is_skip"]==1){
                $banners[$key]['type']="0";
            }else{
                $banners[$key]['type']=(string)$value["id"];
            }


        }

        return  $banners;
    }


    public function updatethemeNum($themeId, $action='add'){

        $condition = 'sort = sort + 1' ;

        $sql = '
            UPDATE
            `bibi_themelist`
            SET
            '.$condition.'
            WHERE
            `id` = '.$themeId.';
        ';

        $this->exec($sql);

    }

    public function updatethemefeedNum($themeId,$num){
        $sql = '
            UPDATE
            `bibi_themelist`
            SET
            feed_num ='.$num.'
            WHERE
            `id` = '.$themeId.';
        ';

        $this->exec($sql);
    }




}