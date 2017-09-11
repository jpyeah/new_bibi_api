<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/2
 * Time: 下午6:41
 */

class PostController extends ApiYafControllerAbstract {


/**
 * @api {POST} /v3/Post/index 朋友圈详情
 * @apiName feed  index
 * @apiGroup Feed
 * @apiDescription 朋友圈详情
 * @apiPermission anyone
 * @apiSampleRequest http://www.bibicar.cn:8090
 *
 * @apiParam {string} [device_identifier] 设备唯一标识
 * @apiParam {string} [session_id] session_id
 * @apiParam {number} [page] 页数
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
 *       
 *       
 *     }
 *   }
 *
 */

    public function indexAction(){


        $this->required_fields = array_merge($this->required_fields,array('session_id','feed_id','page'));

        $data = $this->get_request_data();

        $page = $data['page'] ? ($data['page']+1) : 1;

        $sess = new SessionModel();

        $userId = $sess->Get($data);

        $feedM = new FeedModel();

        $feedM->currentUser = $userId;

        $feed = $feedM->getFeeds($data['feed_id']);

        if($feed['forward_id'] > 0){

           $feed = $feedM->forwardHandler($feed);
        }

        $comments = $feed['comment_list'];

        $feed['comment_list'] = array();

        foreach($comments as $k => $comment){

            if($k < 10){

                $feed['comment_list'][] = $comment;
            }

        }
        $commentList= array();
        $num = 10;
        //$n = 0;
        $commentTotal = $feed['comment_num'];

        $getNum = $num*$page;

        if($getNum > 10){

            $start = ($page-1)*10;

            $end = $page*10-1;
            $end = $end > $commentTotal ? ($commentTotal-1) : $end;

            for($i=$start; $i<=$end; $i++){

                if(isset($comments[$i])){
                    $commentList[] = $comments[$i];

                }
            }

        }

        $count = count($feed['comment_list']) + count($commentList);

        $response = array();

        $response['feed_info'] = $feed;

        $response['has_more'] = ($commentTotal - $count > 0) && ($getNum <= $commentTotal) ?  1 : 2;

        $response['share_title'] = $feed['post_user_info']['profile']['nickname'] . '的车友圈';
        $response['share_url'] = 'http://wap.bibicar.cn/circle/'.$data['feed_id'].'?identity='.base64_encode($data['device_identifier']);
        //$response['share_url'] = 'http://wx.bibicar.cn/post/index/feed_id/'.$data['feed_id'].'';
       
        $response['share_txt'] = '更多精彩内容尽在bibi,期待您的加入!';
        $response['share_img'] = @$feed['post_files'][0]['file_url'];

        $this->send($response);

    }


}