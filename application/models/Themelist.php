<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/6
 * Time: 下午1:30
 */
class ThemelistModel extends PdoDb
{

    public function __construct()
    {

        parent::__construct();
        self::$table = 'bibi_themelist';
    }

    public function saveProperties()
    {
        $this->properties['user_id'] = $this->user_id;
        $this->properties['theme']   = $this->theme;
        $this->properties['created'] = $this->created;
        $this->properties['post_file'] = $this->post_file;
        $this->properties['title'] = $this->title;
    }

    public function getTheme($theme_id=1){
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

            $info['user_info']=array();
            $info['user_info']['avatar']=$info['avatar'];
            $info['user_info']['user_id']=$info['user_id'];
            $info['user_info']['nickname']=$info['nickname'];

            $friendShipM = new FriendShipModel();

            $friendShipM->currentUser = $info['user_id'];

            $info['user_info']['friend_num'] = $friendShipM->friendNumCnt();

            $info['user_info']['fans_num']   = $friendShipM->fansNumCnt();
            unset($info['avatar']);
            unset($info['user_id']);
            unset($info['nickname']);
        }else{
          $info=array();
        }
        return $info;
    }



    public function getThemeByTheme($theme){
        $sql="
        SELECT 
        id,theme,title,post_file,sort,is_skip
        FROM
        `bibi_themelist`
        WHERE
        theme  = '".$theme."'"
        ;

        $theme=$this->query($sql);
        if($theme){
            $info=@$theme[0];
            $info["post_file"]="http://img.bibicar.cn/".$info['post_file'];
        }else{
            $info=array();
        }

        return $info;
    }



    public function getThemes($type=1,$userId=0,$page=1,$tag=1){
      
       $pageSize = 10;
       $sql ='
       SELECT 
       id,post_file,user_id,theme,title,created,sort,is_skip,feed_num,address
       FROM `bibi_themelist`
       WHERE is_hot=1 AND type='.$type.'
          ';
        $sqlNearByCnt = '
            SELECT
            COUNT(id) AS total
            FROM
            `bibi_themelist` 
            WHERE is_hot=1 AND type='.$type.'
            ';


        if($type == 2){
            
           $sql .= "  AND tag = ".$this->tag;
           $sqlNearByCnt .= " AND tag = ".$this->tag;

        }

        $number = ($page-1)*$pageSize;

        switch($tag){

            case 1 :

                if($userId){
                    //用户关注
                    $sqlHot = '
                        SELECT
                        t1.theme_id
                        FROM
                        `bibi_themelist_user` AS t1
                        WHERE t1.user_id='.$userId.'
                        ORDER BY
                        created DESC
                        LIMIT ' . $number . ' , ' . $pageSize . '
                    ';

                    $sqlHotCnt = '
                        SELECT
                        COUNT(t1.theme_id) AS total
                        FROM
                        `bibi_themelist_user` AS t1
                         WHERE t1.user_id='.$userId.'
                        ORDER BY
                        created DESC
                    ';
                    $total = $this->query($sqlHotCnt)[0]['total'];
                    $result = @$this->query($sqlHot);
                    $result = $this->implodeArrayByKey('theme_id', $result);
                    $sql .= ' AND id in (' . $result . ') ORDER BY `created` DESC'; //ORDER BY t3.comment_id DESC
                }else{

                    $sql .= ' ORDER BY sort DESC';
                    $sql .= ' LIMIT '.$number.' , '.$pageSize.' ';
                    $total = $this->query($sqlNearByCnt)[0]['total'];
                }
                break;
            case 2 :
                //AND t1.post_content LIKE "%'.$this->keyword.'%"
                $sql .= 'AND theme LIKE "%'.$this->keyword.'%"';
                $sqlNearByCnt .= 'AND theme LIKE "%'.$this->keyword.'%"';
                break;
            case 3 :
                //推荐话题
                $sqlHot = '
                        SELECT
                        theme_id,
                        count(*)
                        FROM
                        `bibi_themelist_user`
                        GROUP BY theme_id
                        LIMIT 10
                    ';

                $result = @$this->query($sqlHot);
                $result = $this->implodeArrayByKey('theme_id', $result);
                $sql .= ' AND id in (' . $result . ') ORDER BY `created` DESC'; //ORDER BY t3.comment_id DESC
                break;
            case 4 :

                break;

        }

       $theme = $this->query($sql);
       $total = $this->query($sqlNearByCnt)[0]['total'];
       foreach($theme as $key =>$value){
               $theme[$key]["post_file"]="http://img.bibicar.cn/".$value['post_file'];
       }


       $count = count($theme);
       $list['theme_list']=array();
       $list['theme_list'] = $theme;
       $list['has_more'] = (($number + $count) < $total) ? 1 : 2;
       $list['total'] = $total;


       return $list;
    }


    public function deleteTheme($ThemeId){

        $sql = ' DELETE FROM `bibi_themelist` WHERE `id` = '.$ThemeId.' AND `user_id` = '.$this->currentUser.' ';

        $this->execute($sql);

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


    public function CountThemeUserNum($themeId){

        $sql = 'SELECT count(*) total FROM `bibi_themelist_user`';

        $sql .= ' WHERE theme_id ='.$themeId;

        $result = $this->query($sql);

        return $result ? $result[0]["total"] : 0;

    }


    public function getAlltheme(){


        $sql = 'SELECT id,theme FROM bibi_themelist WHERE type = 1 AND is_hot =1';

        $result = $this->query($sql);

        return $result;
    }




}