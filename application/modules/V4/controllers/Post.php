<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/2
 * Time: 下午6:41
 */

class PostController extends ApiYafControllerAbstract {


/**
 * @api {POST} /v4/Post/index 朋友圈详情
 * @apiName feed  index
 * @apiGroup Feed
 * @apiDescription 朋友圈详情
 * @apiPermission anyone
 * @apiSampleRequest http://testapi.bibicar.cn
 * @apiVersion 2.0.0
 *
 * @apiParam {string} [device_identifier] 设备唯一标识
 * @apiParam {string} [session_id] session_id
 * @apiParam {number} [feed_id] 朋友圈id
 * 
 * @apiParam {json} data object
 * @apiUse DreamParam
 * @apiParamExample {json} 请求样例
 *   POST /v3/Post/index
 *   {
 *     "data": {
 *       "device_identifier":"",
 *       "session_id":"",
 *       "page":"",
 *       "feed_id":"",
 *     }
 *   }
 *
 */

    public function indexAction(){


        $this->required_fields = array_merge($this->required_fields,array('session_id','feed_id'));

        $data = $this->get_request_data();

        $sess = new SessionModel();

        $userId = $sess->Get($data);

        $feedM = new Feedv1Model();

        $feedM->currentUser = $userId;

        $feed = $feedM->GetFeedInfo($data['feed_id']);


        $likeM = new LikeModel();
        $likeM->currentUser = $userId;
        $likes = $likeM->getLike(0,$data['feed_id'],1);

        $response = array();
        $response['like_list'] = $likes['like_list'];

        $response['theme_info'] = $this->gettheme($feed['post_content']);

        $response['feed_info'] = $feed;

        $response['share_title'] = $feed['post_user_info']['profile']['nickname'] . '的车友圈';
        $response['share_url'] = 'http://wap.bibicar.cn/circle/'.$data['feed_id'].'?identity='.base64_encode($data['device_identifier']);
        //$response['share_url'] = 'http://wx.bibicar.cn/post/index/feed_id/'.$data['feed_id'].'';

        $response['share_txt'] = '更多精彩内容尽在bibi,期待您的加入!';
        $response['share_img'] = @$feed['post_files'][0]['file_url'];

        $this->send($response);

    }


    public function gettheme($post_content){

        $tag_pattern = "/\#([^\#|.]+)\#/";
        preg_match_all($tag_pattern, $post_content, $tagsarr);
        $tags = implode(',',$tagsarr[1]);

        $result = explode(',',$tags);

        if($result){

            $items =array();
        foreach($result as $k => $val){

            $theme = "#".$val."#";

            $themelistM = new ThemelistModel();

            $info = $themelistM->getThemeByTheme($theme);

            $items[]=$info;

        }

        return $items;

        }else{

            return array();
        }

    }


}