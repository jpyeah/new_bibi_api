<?php
/**
 * Created by sublime.
 * User: jpjy
 * Date: 15/10/19
 * Time: 上午11:50
 * note: 文章管理
 */
class ArticleController extends ApiYafControllerAbstract
{



      //首页
      public function homepageAction(){

          $this->required_fields = array_merge($this->required_fields,array('session_id','page'));
          $data = $this->get_request_data();

          if(@$data['session_id']){

              $sess = new SessionModel();
              $userId = $sess->Get($data);
          }
          else{

              $userId = 0;
          }
         $response =  $this->getNewHomePage($userId);

          //1车辆  2 视频 3 文章 4 车行
          //吡吡头条
          $array=array(
              ['img_url' =>'http://ojygvz0ql.bkt.clouddn.com/23.jpg' , 'content'  => '路虎揽胜','type'=> 1, 'type_id'=>'576bb220c300c'],
              ['img_url' =>'http://ojygvz0ql.bkt.clouddn.com/24.jpg' , 'content'  => '全民星探－尚恒竟然在车上干出这种事','type'=> 2, 'type_id'=>5024 ],
              ['img_url' =>'http://ojygvz0ql.bkt.clouddn.com/20.jpg' , 'content'  => '震惊！5000块就可以开走的车','type'=> 3, 'type_id'=>5014 ],
              ['img_url' =>'http://ojygvz0ql.bkt.clouddn.com/1.jpg' , 'content'  => '捷豹车行入驻成功','type'=> 4, 'type_id'=>389 ],
          );
          $response['news'] = $array;

         $this->send($response);

      }



/**
 * @api {POST} /v3/Article/themeslist 首页标签－车辆－大咖－话题－轮播图列表
 * @apiName Article  themeslist
 * @apiGroup Article
 * @apiDescription 首页标签－车辆－大咖－话题－轮播图列表
 * @apiPermission anyone
 * @apiSampleRequest http://testapi.bibicar.cn
 * @apiVersion 1.0.0
 *
 * @apiParam {string} device_identifier device_identifier
 * @apiParam {string} session_id session_id
 * @apiParam {string} page 页码
 * @apiParam {string} tag 1:推荐 2:车行 3:视频
 *
 * @apiParamExample {json} 请求样例
 *   POST /v3/Article/themeslist
 *   {
 *     "data": {
 *       "device_identifier":"",
 *       "session_id":"",
 *       "page":"",
 *       
 *       
 *     }
 *   }
 * @apiSuccess {json} list 话题列表
 * @apiSuccess {json} users 最牛大咖.
 * @apiSuccess {json} company_list  最佳车行
 * @apiSuccess {json} car_list  最新车辆
 * @apiSuccess {json} article  最新文章
 * @apiSuccess {json} company  车行列表
 * @apiSuccess {json} videos  最新视频
 * @apiSuccess {json} banners  轮播图
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "status" :1,
 *       "code" : 0,
 *       "data" :{
 *        "list" :"",
 *        "users" :"",
 *        "company_list" :"",
 *        "car_list" :"",
 *        "article" :"",
 *        "banners" :"",
 *        },
 *
 *     }
 * @apiVersion 1.6.2
 *
 */
    //标签列表
    public function themeslistAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id','page'));

        $data = $this->get_request_data();
        

        if(@$data['session_id']){

            $sess = new SessionModel();
            $userId = $sess->Get($data);
        }
        else{

            $userId = 0;
        }
        $data['page']     = $data['page'] ? ($data['page']+1) : 1;

        if(@$data['tag']){
        switch ($data['tag']){
            case 1:
                 $response=$this->GetRecommonn($userId, $data );
                 break;

            case 2:
                $response=$this->GetCompany($userId, $data );
                break;

            case 3:
                $response=$this->GetVideo($userId, $data );
                break;
        }

        }else{

            $response=$this->GetRecommonn($userId, $data);
        }


        //吡吡头条
        $array=array(
            ['img_url' =>'http://img.bibicar.cn/bibilogo.png' , 'content'  => '豪车0元租抽奖活动即将上线！敬请期待！！' ],
        );


//        $array=array(
//            ['img_url' =>'http://ojygvz0ql.bkt.clouddn.com/23.jpg' , 'content'  => '胡静在bibicar以¥66.5万购入一台奥迪 Q7。' ],
//            ['img_url' =>'http://ojygvz0ql.bkt.clouddn.com/24.jpg' , 'content'  => '张焕明在bibicar以¥66万购入一台日产 GTR。' ],
//            ['img_url' =>'http://ojygvz0ql.bkt.clouddn.com/20.jpg' , 'content'  => '方瑞新在bibicar以¥178万购入一台路虎揽胜。' ],
//            ['img_url' =>'http://ojygvz0ql.bkt.clouddn.com/21.jpg' , 'content'  => '黄志鹏在bibicar以¥66.5万购入一台奔驰 V260。' ],
//            ['img_url' =>'http://ojygvz0ql.bkt.clouddn.com/18.jpg' , 'content'  => '庄业军在bibicar以¥152万购入一台保时捷 911。' ],
//            ['img_url' =>'http://ojygvz0ql.bkt.clouddn.com/17.jpg' , 'content'  => '李楠在bibicar以¥40万购入一台雷克萨斯 ES300h。' ],
//            ['img_url' =>'http://ojygvz0ql.bkt.clouddn.com/16.jpg' , 'content'  => '黄艳梅在bibicar以¥51.5万购入一台宝马 M235i。' ],
//            ['img_url' =>'http://ojygvz0ql.bkt.clouddn.com/15.jpg' , 'content'  => '叶利军在bibicar以¥47.28万购入一台奔驰 C200 coupe。' ],
//            ['img_url' =>'http://ojygvz0ql.bkt.clouddn.com/14.jpg' , 'content'  => '吴瑶在bibicar以¥153.1万购入一台保时捷 911 targa4。' ],
//            ['img_url' =>'http://ojygvz0ql.bkt.clouddn.com/13.jpg' , 'content'  => '李坤在bibicar以¥90.29万购入一台宝马 740li。' ],
//            ['img_url' =>'http://ojygvz0ql.bkt.clouddn.com/12.jpg' , 'content'  => '洪方奎在bibicar以¥470万购入一台劳斯莱斯魅影。' ],
//            ['img_url' =>'http://ojygvz0ql.bkt.clouddn.com/11.jpg' , 'content'  => '汤尔胜在bibicar以¥27.67万购入一台凯迪拉克ATS-L。' ],
//            ['img_url' =>'http://ojygvz0ql.bkt.clouddn.com/10.jpg' , 'content'  => '贾方融在bibicar以¥40.99万购入一台沃尔沃S60L。' ],
//            ['img_url' =>'http://ojygvz0ql.bkt.clouddn.com/9.jpg' , 'content'  => '李志端在bibicar以¥89.4万购入一台奔驰S320。' ],
//            ['img_url' =>'http://ojygvz0ql.bkt.clouddn.com/8.jpg' , 'content'  => '万宏在bibicar以¥111.7万购入一台丰田埃尔法。' ],
//            ['img_url' =>'http://ojygvz0ql.bkt.clouddn.com/7.jpg' , 'content'  => '赵卫明在bibicar以¥60万购入一台二手车保时捷 帕拉梅拉。' ],
//            ['img_url' =>'http://ojygvz0ql.bkt.clouddn.com/6.jpg' , 'content'  => '吴明达在bibicar以¥87.27万购入一台玛莎拉蒂Ghibli。' ],
//            ['img_url' =>'http://ojygvz0ql.bkt.clouddn.com/5.jpg' , 'content'  => '文燕在bibicar以¥30.08万购入一台奔驰C200。' ],
//            ['img_url' =>'http://ojygvz0ql.bkt.clouddn.com/4.jpg' , 'content'  => '余海涛在bibicar以¥15.9万购入一台二手车奥迪A3。' ],
//            ['img_url' =>'http://ojygvz0ql.bkt.clouddn.com/3.jpg' , 'content'  => '黄泽涛在bibicar以¥100.3万购入一台奔驰GLE43。' ],
//            ['img_url' =>'http://ojygvz0ql.bkt.clouddn.com/2.jpg' , 'content'  => '胡山青在bibicar以¥28.5万购入一台二手车宝马320li。' ],
//            ['img_url' =>'http://ojygvz0ql.bkt.clouddn.com/1.jpg' , 'content'  => '王逸风在bibicar以¥50.2万购入一台捷豹P-pace。' ],
//        );

        $response['news'] = $array;

        $this->send($response);
    }



/**
 * @api {POST} /v3/Article/getAct 活动列表
 * @apiName Article  getAct
 * @apiGroup Article
 * @apiDescription 活动列表
 * @apiPermission anyone
 * @apiSampleRequest http://testapi.bibicar.cn
 * @apiVersion 1.0.0
 * @apiParam {string} [device_identifier] device_identifier
 * @apiParam {string} [session_id] session_id
 * @apiParam {string} [page] 页码
 *
 * @apiParamExample {json} 请求样例
 *   POST /v3/Post/asycreate
 *   {
 *     "data": {
 *       "device_identifier":"",
 *       "session_id":"",
 *       "page":"",
 *       
 *       
 *     }
 *   }
 *
 */

    public function getActAction(){
        
        $this->required_fields = array_merge($this->required_fields,array('page'));

        $data = $this->get_request_data();
        
        if(@$data['session_id']){

            $sess = new SessionModel();
            $userId = $sess->Get($data);
        }
        else{

            $userId = 0;
        }
        $data['page']     = $data['page'] ? ($data['page']+1) : 1;
        $type=3;
        $themeM=new ThemelistModel();
        $theme=$themeM->getThemes($type,$userId,$data['page']);
       $banners=array();
       if($theme["theme_list"]){
          foreach($theme["theme_list"] as $key =>$value){
               $banners[$key]['imgUrl']=$value["post_file"];
               
               $banners[$key]['appUrl']=$value["theme"];
               $banners[$key]['title']=$value["title"];
               $banners[$key]['created']=$value["created"];
               $banners[$key]['address']=$value["address"];
               $banners[$key]['link']="/topic/".$value["id"];
               if($value["is_skip"]==1){
                     $banners[$key]['type']="0";
                 if($value["id"]== 20){
                     $banners[$key]['appUrl']=$value["theme"]."?se=".base64_encode($data['session_id'])."&identity=".base64_encode($data['device_identifier']);
                 }elseif($value["id"]== 32){
                     $banners[$key]['appUrl']=$value["theme"]."?session=".base64_encode($data['session_id'])."&ident=".base64_encode($data['device_identifier']);
                 }
                 
               }else{
                $banners[$key]['type']=(string)$value["id"];
               }
               

          }
       }

       $response['list']=$banners;
       $this->send($response);

    }

    public function testAction(){
      /*  //最牛大咖
        $Profile=new ProfileModel();
        $tag=2;
        $page=1;
        $user=$Profile->gettypeofuser($tag,$page);
        print_r($user);
      */
        $jsonData = require APPPATH .'/configs/JsonData.php';
        $carM = new CarSellingModel();
        $where = 'WHERE t1.files <> "" AND t1.brand_id <> 0 AND t1.series_id <> 0 AND t1.car_type <> 3 AND (t1.verify_status = 2 OR t1.verify_status = 11 OR t1.verify_status = 4) ';
        $carM->where = $where;
        $carM->order = $jsonData['order_info'][0];
        $carM->page = 1;
        $userId=0;
        $carM->currentUser =$userId;
        $lists = $carM->getCarList($userId);
        print_r( $lists);

    }
   



     //编辑文章
    public function createAction(){

            $this->required_fields = array_merge(
            $this->required_fields,
            array(
                'sort_id',
                'title',
                'full_title',
                'keyword',
                'content',
            ));

	        $data = $this->get_request_data();

	        $userId = $this->userAuth($data);
	        
	        if (!$data['sort_id'] && !$data['title'] && !$data['full_title'] && !$data['keyword'] && $data['content']) {

             $this->send_error(CAR_DRIVE_INFO_ERROR);
            }        
            
            $ArticleM= new ArticleModel;
            $ArticleContentM=new ArticleCotentModel();
            $time=time();
	        $ArticleM->sort_id       = $data['sort_id'];
	        $ArticleM->title         = $data['title'];
	        $ArticleM->full_title    = $data['full_title'];
	        $ArticleM->author_id     = $userId;;
	        $ArticleM->copyfrom      = $data['copyfrom'];
	        $ArticleM->http_url      = $data['http_url'];
	        $ArticleM->keyword       = $data['keyword'];
	        $ArticleM->created       = $time;
	        $ArticleM->update_time   = $time;
            $ArticleM->saveProperties();
            $ArticleId = $ArticleM->CreateM();

           

            $ArticleContentM->article_id =$ArticleId;
            $ArticleContentM->content    =$data['content'];
            $ArticleContentM->saveProperties();
            $ArticleId = $ArticleContentM->CreateM();
     }


    /**
     * @api {POST} /v3/Article/list 文章列表
     * @apiName Article list
     * @apiGroup Article
     * @apiDescription 文章列表
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 1.0.0
     *
     * @apiParam {string} device_identifier device_identifier
     * @apiParam {string} session_id session_id
     * @apiParam {string} page 页码
     * @apiParam {number} [grade] 1:车主故事
     * @apiParam {number} [type]  type=4 兼容富文本文章列表
     *
     *
     *
     * @apiParamExample {json} 请求样例
     *   POST  /v3/Article/list
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "page":"",
     *
     *
     *     }
     *   }
     *
     */


     //文章列表
    public function listAction(){
            
            $this->required_fields = array_merge($this->required_fields,array('page'));

            $data = $this->get_request_data();
             if(@$data['session_id']){

                    $sess = new SessionModel();
                    $userId = $sess->Get($data);
                }
                else{

                    $userId = 0;
                }
             
            $feedM = new Feedv1Model();
            
            if(@$data['grade']){
                $type=2;
                $feedM->grade_id=$data['grade'];
            }else if(@$data['tags_id']){
                $type=3;
                $feedM->tags_id =$data['tags_id'];
            }else{
                $type=1;
            }

            if(@$data['type'] == 4){

                $type = 4;
            }

            if(@$data['type'] && @$data['grade']){
                $type = 2;
            }

            /*
            $data['post_type'] = $data['post_type'] ? $data['post_type'] : 1;
             */
            $data['page']     = $data['page'] ? ($data['page']+1) : 1;
           
            $feedM->currentUser = $userId;

     
            $response =$feedM->getFeeds($type,$data['page']);

            $this->send($response);

     }

    /**
     * @api {POST} /v3/Article/searcharticlelist 搜索文章
     * @apiName Article  searcharticlelist
     * @apiGroup Article
     * @apiDescription 搜索文章
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 1.0.0
     *
     * @apiParam {string} device_identifier device_identifier
     * @apiParam {string} session_id session_id
     * @apiParam {number} page 页码
     * @apiParam {string} keyword 搜索词
     *
     * @apiParamExample {json} 请求样例
     *   POST  /v3/Article/list
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "page":"",
     *       "keyword":"",
     *
     *
     *     }
     *   }
     *
     */

    //搜索文章
     public function SearchArticleListAction(){

         $this->required_fields = array_merge($this->required_fields,array('page','keyword'));

         $data = $this->get_request_data();

         if(@$data['session_id']){

             $sess = new SessionModel();
             $userId = $sess->Get($data);
         }
         else{

             $userId = 0;
         }

         $feedM = new Feedv1Model();

         $data['page']     = $data['page'] ? ($data['page']+1) : 1;

         $feedM->keyword = $data['keyword'];

         $response =$feedM->getFeeds(5,$data['page']);

         $this->send($response);
     }


    //车主故事
    public function StorylistAction(){

        $this->required_fields = array_merge($this->required_fields,array('page'));

        $data = $this->get_request_data();
        if(@$data['session_id']){

            $sess = new SessionModel();
            $userId = $sess->Get($data);
        }
        else{

            $userId = 0;
        }

        $feedM = new Feedv1Model();

        if(@$data['grade_id']){
            $type=2;

            $feedM->grade_id=$data['grade_id'];

        }else if(@$data['tags_id']){

            $type=3;

            $feedM->tags_id =$data['tags_id'];

        }else{
            $type=1;
        }

        /*
        $data['post_type'] = $data['post_type'] ? $data['post_type'] : 1;
         */
        $data['page']     = $data['page'] ? ($data['page']+1) : 1;

        $feedM->currentUser = $userId;


        $response =$feedM->getFeeds($type,$data['page']);

        $this->send($response);

    }

     //文章详情
    public function indexAction(){
                
                $this->required_fields = array_merge($this->required_fields, array('session_id','feed_id'));

                $data = $this->get_request_data();
                //$userId = $this->userAuth($data);
                if(@$data['session_id']){

                    $sess = new SessionModel();
                    $userId = $sess->Get($data);
                }
                else{

                    $userId = 0;
                }
            
                $FeedId=$data['feed_id'];
            

                $FeedModel = new Feedv1Model();

                $FeedMT = $FeedModel::$table;

                $FeedModel->currentUser = $userId;

                $FeedInfo = $FeedModel->GetFeedInfoById($FeedId,$userId);

                $response['feed_info'] = $FeedInfo;
                
               
                $visitFeedM = new FeedVisitModel();
                 
                $visitFeedM->feed_id = $FeedId;
                $visitFeedM->user_id = $userId;

                $id = $visitFeedM->get();
              
                if(!$id){
                    $properties = array();
                    $properties['created'] = time();
                    $properties['user_id'] = $userId;
                    $properties['feed_id']  = $FeedId;
                    $num=$FeedInfo['visit_num']+1;

                    $result=$FeedModel->updateByPrimaryKey(
                        $FeedMT,
                        array('feed_id'=>$FeedId),
                        array('visit_num'=>$num)
                    );

                    $visitFeedM->insert($visitFeedM->tableName, $properties);
                }
                
                
                $title = $FeedInfo['title'];
                $response['share_title'] = $title;
                $response['share_url'] = 'http://wap.bibicar.cn/'.$FeedId.'?identity='.base64_encode($data[
                    "device_identifier"]);
                $response['share_txt'] = '更多精选二手车在bibi car,欢迎您来选购!';
                $response['share_img'] = isset($FeedInfo['image_url'][0]) ? $FeedInfo['image_url'][0] : '';
                $this->send($response);

    }

    //添加评论
    public function commentcreateAction(){

                $time = time();

                $this->required_fields = array_merge(
                    $this->required_fields,
                    array('session_id', 'feed_id','content')
                );

                $data = $this->get_request_data();

                $userId = $this->userAuth($data);

                $commentM = new CommentModel();
                $commentM->currentUser = $userId;
                $feedM = new FeedModel();
                $feedM->currentUser = $userId;

                $replyId = @$data['reply_id'] ? $data['reply_id'] : 0;
                $fatherId = @$data['father_id'] ? $data['father_id'] :0;

                //相关的人
                $feedrelatedM = new FeedrelatedModel();
                $data['feed_id']=$data['feed_id'];
                $data['user_id']=$userId;
                $data['comment'] ='1';
                $data['create_time']=time();
                $feedrelatedM->savefeed($data);


                if($replyId){

                    $replyComment = $commentM->getComment($replyId, $data['feed_id']);

                    $toId = isset($replyComment['from_user_info']['user_id']) ? $replyComment['from_user_info']['user_id'] : 0;

                    
                }
                else{

                    $feed = $feedM->getFeeds($data['feed_id']);
                    $toId = $feed['post_user_info']['user_id'];
                }

                $commentM = new CommentModel();
                $commentM->user_id = $userId;
                $commentM->feed_id = $data['feed_id'];
                $commentM->content = $data['content'];
                $commentM->from_id = $userId;
                $commentM->to_id   = $toId;
                $commentM->reply_id = $replyId;
                $commentM->father_id = $fatherId;
                $commentM->created = $time;

                $commentM->saveProperties();

                $commentId = $commentM->CreateM();

                if($commentId){
                    //sort 热度加分
                    $userpro=new UserSortModel();
                    $active="articlecomment";
                    $type_id=$commentId;
                    $fromId=$userId;
                    $toId=$toId;
                    $result=$userpro->updateSortByKey($active,$type_id,$fromId,$toId);
                }

                $feedM->updateCommentNum($data['feed_id']);

                $comment = $commentM->getComment($commentId , $data['feed_id']);

                if($userId != $toId){

                    $mh = new MessageHelper;

                    $nickname = $comment['from_user_info']['profile']['nickname'];
                    $content = ''.$nickname.'评论了你';
                    $mh->commentNotify($toId, $content);
                }

                $this->send($comment);
    }

    //评论列表
    public function commentlistAction(){

            
            $this->required_fields = array_merge($this->required_fields,array('session_id','feed_id','page'));

            $data = $this->get_request_data();
            $data['page']     = $data['page'] ? ($data['page']+1) : 1;
           
          
            $sess = new SessionModel();
            $userId = $sess->Get($data);
           
            $commentM = new Commentv1Model();
            $comments = $commentM->getComment($data['feed_id'],$data['page'],$userId);
           
            $this->send($comments);

    }

    //获取一级评论下的二级评论
     public function getcommentAction(){
          
        $this->required_fields = array_merge($this->required_fields,array('session_id','feed_id','comment_id','page'));

        $data = $this->get_request_data();
        $data['page']     = $data['page'] ? ($data['page']+1) : 1;
       
      
        $sess = new SessionModel();
        $userId = $sess->Get($data);

         $commentM = new Commentv1Model();
         $Comment = $commentM->getCommentdetail($data['comment_id'],$data['feed_id'],$data['page'],$userId);
       
         $this->send($Comment);
         
     }

    
    public function commenttomeAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id', 'page'));

        $data = $this->get_request_data();
        $data['page']     = $data['page'] ? ($data['page']+1) : 1;

        $userId = $this->userAuth($data);

        $commentM = new CommentModel();
        $comments = $commentM->getCommenttome(0,0,$data['page'],$userId);

        $this->send($comments);

    }

    //删除评论
    public function commentdeleteAction(){

            $this->required_fields = array_merge($this->required_fields,array('session_id','feed_id','comment_id'));


            $data = $this->get_request_data();

            $userId = $this->userAuth($data);

            $feed_id = @$data['feed_id'];
            $comment_id = @$data['comment_id'];

            if($comment_id){

                $commentM = new CommentModel();

                $commentM->currentUser = $userId;
                $commentM->feed_id = $feed_id;
                $commentM->comment_id = $comment_id;

                $commentM->from_id = $userId;

                $commentM->deleteComment();

                $feedM = new FeedModel();
                $feedM->updateCommentNum($feed_id,"miuns");
            }
            else{

                $feedM = new FeedModel();
                $feedM->currentUser = $userId;

                $feedM->deleteFeed($feed_id);
            }
            $this->send();

    }
    //评论点赞
    public function commentlikecreateAction(){
                
                $this->required_fields = array_merge(
                    $this->required_fields,
                    array('session_id', 'feed_id','comment_id')
                );

                $data = $this->get_request_data();

                $userId = $this->userAuth($data);
                
                $time = time();

                $CommentLikeM = new CommentLikeModel();

                $like = $CommentLikeM ->getLike($data['feed_id'],$data['comment_id'],$userId);
                
                if(!$like){

                    $CommentLikeM = new CommentLikeModel();
                    $CommentLikeM ->user_id     = $userId;
                    $CommentLikeM ->feed_id  = $data['feed_id'];
                    $CommentLikeM ->comment_id  = $data['comment_id'];
                    $CommentLikeM ->created     = $time;

                    $CommentLikeM ->saveProperties();
                    $id =  $CommentLikeM ->CreateM();


                    if($id){
                        
                        //sort 热度加分
                        $userpro=new UserSortModel();
                        $active="articlecomment";
                        $type_id=$data['comment_id'];
                        $fromId=$userId;
                        $toId=0;
                        $result=$userpro->updateSortByKey($active,$type_id,$fromId,$toId);
                        
                        $CommentLikeM = new CommentLikeModel();
                        $CommentLikeM->updateLikeNum($data['feed_id'],$data['comment_id']);

                        $key = 'commentlike_'.$data['feed_id'].'_'.$userId.'_'.$data['comment_id'].'';

                        RedisDb::setValue($key,1);
                   
                        $like =$CommentLikeM ->getLike($data['feed_id'],$data['comment_id'] );
                        
                        $this->send($like);
                    }


                }
                else{
                    
                    $this->send_error(FEED_HAS_LIKED);
                }


    }

    //取消评论点赞
    public function commentlikedeleteAction(){
                
                
                $this->required_fields = array_merge(
                    $this->required_fields,
                    array('session_id', 'feed_id','comment_id')
                );

                $data = $this->get_request_data();

                $userId = $this->userAuth($data);
                
              
                $CommentLikeM = new CommentLikeModel();

                $like = $CommentLikeM->getLike($data['feed_id'],$data['comment_id'],$userId);

                $rs = $CommentLikeM->deleteLike($data['feed_id'],$userId,$data['comment_id']);

                if($rs){

                    $CommentLikeM = new CommentLikeModel();;

                    $CommentLikeM->updateLikeNum($data['feed_id'],$data['comment_id'],'minus');

                    $like = $CommentLikeM->getLike($data['feed_id'],$data['comment_id']);

                    $this->send($like);
                }
                else{

                    $this->send_error(FEED_LIKE_HAS_CANCLED);
                }


    }

    //收藏添加
    public function collectcreateAction(){
        //添加收藏的文章
        
        $this->required_fields = array_merge($this->required_fields,array('session_id','feed_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $FeedCollectM              = new FeedCollectModel();
        $FeedCollectM->user_id     = $userId;
        $FeedCollectM->feed_id     = $data['feed_id'];
        $FeedCollect = $FeedCollectM->get();
      
        if($FeedCollect){

            $this->send_error(FAVORITE_CAR_ALREADY);
        }

        $FeedM = new Feedv1Model();
        $feedMTable = $FeedM::$table;
        $FeedM->currentUser = $userId;
        $feed = $FeedM->GetFeedInfoById($data['feed_id']);
        $feedNum = $feed['collect_num'] + 1;

        if(!$feed){

            $this->send_error(CAR_NOT_EXIST);
        }

        $FeedCollectM              = new FeedCollectModel();
        $FeedCollectM  ->user_id     = $userId;
        $FeedCollectM  ->feed_id  = $data['feed_id'];
        $created=time();
        $FeedCollectM  ->created     = $created;

        $FeedCollectM ->saveProperties();
        
        $id = $FeedCollectM->CreateM();

        if($id){
            
            //sort 热度加分
            $userpro=new UserSortModel();
            $active="articlecomment";
            $type_id=$id;
            $fromId=$userId;
            $toId=0;
            $result=$userpro->updateSortByKey($active,$type_id,$fromId,$toId);
        }
        
        if(!$id){
            $this->send_error(FAVORITE_FAIL);
        }
        else{

            $FeedM->updateByPrimaryKey($feedMTable , array('feed_id'=>$data['feed_id']),array('collect_num'=>$feedNum));

            $response = array();
            $response['collect_id'] = $id;

            $feed["is_collect"] =1;
            $response['feed_info'] = $feed;

            $key = 'collect_'.$userId.'_'.$data['feed_id'].'';

            RedisDb::setValue($key,$id);

            $this->send($response);
        }

    }

    //收藏删除
    public function collectdeleteAction(){

            //删除收藏文章
            $this->required_fields = array_merge($this->required_fields,array('session_id', 'feed_id'));

            $data = $this->get_request_data();

            $userId = $this->userAuth($data);
           
            $FeedCollectM     = new FeedCollectModel();

            $FeedCollectM->feed_id      = $data['feed_id'];
            $FeedCollectM->user_id         = $userId;

            $key = 'collect_'.$FeedCollectM->user_id.'_'.$FeedCollectM->feed_id.'';

            $CollectId = RedisDb::getValue($key);
           
            $FeedCollectM->collect_id = $CollectId;
            $FeedCollectM->delete();

            RedisDb::delValue($key);

            $response = array();

            $this->send($response);

    }

    //收藏列表
     public function collectlistAction(){
            
            $this->required_fields = array_merge($this->required_fields,array('session_id','page'));

             $feedM = new Feedv1Model();

            $data = $this->get_request_data();

            $data['page']   = $data['page'] ? $data['page'] : 1;
            $feedM->page = $data['page'];

            $userId = $this->userAuth($data);
             
            $feedM->currentUser = $userId;

            $list = $feedM->getUserCollectFeed($userId);

            $response = $list;

            $this->send($response);

    }

    /**
     * @api {POST} /v3/Article/visitlist  浏览过的文章
     * @apiName Article  visitlist
     * @apiGroup Article
     * @apiDescription 浏览过的文章
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.0.0
     *
     * @apiParam {string} device_identifier device_identifier
     * @apiParam {string} session_id session_id
     * @apiParam {number} page 页码
     *
     *
     * @apiParamExample {json} 请求样例
     *   POST   /v3/Article/visitlist
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "page":"",
     *
     *     }
     *   }
     *
     */
    public function visitlistAction(){

            //浏览过的文章
            $this->required_fields = array_merge($this->required_fields,array('session_id','page'));

            $feedM = new Feedv1Model();

            $data = $this->get_request_data();

            $data['page'] = $data['page'] ? $data['page'] : 1;
            $feedM->page = $data['page'];

            $userId = $this->userAuth($data);
            
            $feedM->currentUser = $userId;

            $list = $feedM->getUserVisitFeed($userId);

            $response = $list;

            $this->send($response);
    }


    public function sharecreateAction(){

            //浏览过的文章
            $this->required_fields = array_merge($this->required_fields,array('session_id','feed_id'));

            $feedM = new Feedv1Model();

            $data = $this->get_request_data();
            $userId = $this->userAuth($data);

             $FeedInfo = $FeedModel->GetFeedInfoById($FeedId,$userId);
                $response['feed_info'] = $FeedInfo;
       
            $shareFeedM = new FeedShareModel();
                 
                $shareFeedM->feed_id = $FeedId;
                $shareFeedM->user_id = $userId;

                $id = $shareFeedM->get();
                 
                if(!$id){
                    $properties = array();
                    $properties['created'] = time();
                    $properties['user_id'] = $userId;
                    $properties['feed_id']  = $FeedId;

                    $FeedModel->updateByPrimaryKey(
                        $FeedMT,
                        array('feed_id'=>$feedId),
                        array('share_num'=>($FeedInfo['share_num']+1))
                    );
                    $shareFeedM->insert($shareFeedM->tableName, $properties);
                }

            $this->send($response);
    }



         public function GetRandStr($len)   
   {  
    
    $chars = array(   
        "A", "B", "C", "D", "E", "F", "G",    
        "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",    
        "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",    
        "3", "4", "5", "6", "7", "8", "9"   
    );   
     
    $charsLen = count($chars) - 1;   
    shuffle($chars);     
    $output = "";   
    for ($i=0; $i<$len; $i++)   
    {   
        $output .= $chars[mt_rand(0, $charsLen)];   
    }    
    
    return $output; 

    }

    /*
     * 推荐 tab tab =1
     */
    public function GetRecommonn($userId=0,$data){

        $themeM=new ThemelistModel();

        $themeM->tag = 1;
        $type=1;
        $themes=$themeM->getThemes($type,0,1);
        if($themes["theme_list"]){
            $items=array();
            $response['list']=array();
            $response['list']=$themes["theme_list"];
            $response['has_more']=$themes['has_more'];
            $response['total']=$themes['total'];
        }
//        $theme=$themeM->getThemes(2,0);
//        $tags=array();
//        //print_r($theme);exit;
//        $banners=array();
//        if($theme["theme_list"]){
//            foreach($theme["theme_list"] as $key =>$value){
//
//                $banners[$key]['imgUrl']=$value["post_file"];
//                $banners[$key]['appUrl']=$value["theme"];
//                $banners[$key]['title']=$value["title"];
//                $banners[$key]['link']="/topic/".$value["id"];
//                if($value["is_skip"]==1){
//                    $banners[$key]['type']="0";
//                    if($value["id"]== 20){
//                        $banners[$key]['appUrl']=$value["theme"]."?se=".base64_encode($data['session_id'])."&identity=".base64_encode($data['device_identifier']);
//                    }elseif($value["id"]== 32){
//                        $banners[$key]['appUrl']=$value["theme"]."?session=".base64_encode($data['session_id'])."&ident=".base64_encode($data['device_identifier']);
//                    }elseif($value['id']== 33){
//                        $code='';
//                        if($data['session_id']){
//                            $couponM=new CouponModel;
//                            $res=$couponM->getcode(2,$userId);
//                            if($res){
//                                $code=$res[0]['code'];
//                            }
//                        }
//                        $banners[$key]['appUrl']=$value["theme"]."?se=".base64_encode($data['session_id'])."&ident=".base64_encode($data['device_identifier'])."&code=".$code;
//                    }
//
//                }else{
//                    $banners[$key]['type']=(string)$value["id"];
//                }
//
//
//            }
//        }
        //最牛大咖
        $Profile=new ProfileModel();
        $tag=2;
        $page=1;
        $user=$Profile->gettypeofuser($tag,$page);
        $response['users']=$user;

        //最佳车行
        $Profile=new ProfileModel();
        $Profile->pageSize=5;
        $page     = 1;
        $user=$Profile->getCompanylist($page);
        $response['company_list']=$user;

        //车辆列表
        $jsonData = require APPPATH .'/configs/JsonData.php';
        $carM = new CarSellingModel();
        $where = 'WHERE t1.files <> "" AND t1.brand_id <> 0 AND t1.series_id <> 0 AND t1.car_type <> 3 AND (t1.verify_status = 2 OR t1.verify_status = 11 OR t1.verify_status = 4) ';
        $carM->where = $where;
        $carM->order = $jsonData['order_info'][0];
        $carM->page = 1;
        $userId=0;
        $carM->currentUser =$userId;
        $lists = $carM->getCarList($userId);
        $response['car_list']=$lists;

        //文章列表
        $feedM = new Feedv1Model();
        $type=1;
        $feedM->currentUser = $userId;
        $response['article']=$feedM->getFeeds($type,$data['page']);

        //话题和轮播图
        // print_r($banners);exit;
        $bannerM=new BannerModel();
        $AppM= new AppModel();
        $res = $AppM->getIdentifierInfo($data['device_identifier']);
        $bannerM->device_size  = $res ;
        $response['banners']=$bannerM->getbanners();

        return $response;

    }
    /*
     * 车行 tab  tab =2
     */
    public function GetCompany($userId=0,$data){

        $themeM=new ThemelistModel();

        $themeM->tag=2;
        $theme=$themeM->getThemes(2,0);
        $tags=array();
        //print_r($theme);exit;
//        $banners=array();
//        if($theme["theme_list"]){
//            foreach($theme["theme_list"] as $key =>$value){
//
//                $banners[$key]['imgUrl']=$value["post_file"];
//                $banners[$key]['appUrl']=$value["theme"];
//                $banners[$key]['title']=$value["title"];
//                $banners[$key]['link']="/topic/".$value["id"];
//                if($value["is_skip"]==1){
//                    $banners[$key]['type']="0";
//                    if($value["id"]== 20){
//                        $banners[$key]['appUrl']=$value["theme"]."?se=".base64_encode($data['session_id'])."&identity=".base64_encode($data['device_identifier']);
//                    }elseif($value["id"]== 32){
//                        $banners[$key]['appUrl']=$value["theme"]."?session=".base64_encode($data['session_id'])."&ident=".base64_encode($data['device_identifier']);
//                    }elseif($value['id']== 33){
//                        $code='';
//                        if($data['session_id']){
//                            $couponM=new CouponModel;
//                            $res=$couponM->getcode(2,$userId);
//                            if($res){
//                                $code=$res[0]['code'];
//                            }
//                        }
//                        $banners[$key]['appUrl']=$value["theme"]."?se=".base64_encode($data['session_id'])."&ident=".base64_encode($data['device_identifier'])."&code=".$code;
//                    }
//
//                }else{
//                    $banners[$key]['type']=(string)$value["id"];
//                }
//
//
//            }
//        }

        //车行列表
        $Profile=new ProfileModel();
        $Profile->pageSize=5;
        $user=$Profile->getCompanylist($data['page']);
        $response['company']=$user;

        //话题和轮播图
        // print_r($banners);exit;
        $bannerM=new BannerModel();
        $AppM= new AppModel();
        $res = $AppM->getIdentifierInfo($data['device_identifier']);
        $bannerM->device_size  = $res;
        $response['banners']=$bannerM->getbanners();

        return $response;


    }

    /*
     * 咨询 视频 tab
     * tab =3
     */
    public function GetVideo($userId=0,$data){

        $themeM=new ThemelistModel();
        $themeM->tag=3;
        $theme=$themeM->getThemes(2,0);
        $tags=array();
        //print_r($theme);exit;
//        $banners=array();
//        if($theme["theme_list"]){
//            foreach($theme["theme_list"] as $key =>$value){
//
//                $banners[$key]['imgUrl']=$value["post_file"];
//                $banners[$key]['appUrl']=$value["theme"];
//                $banners[$key]['title']=$value["title"];
//                $banners[$key]['link']="/topic/".$value["id"];
//                if($value["is_skip"]==1){
//                    $banners[$key]['type']="0";
//                    if($value["id"]== 20){
//                        $banners[$key]['appUrl']=$value["theme"]."?se=".base64_encode($data['session_id'])."&identity=".base64_encode($data['device_identifier']);
//                    }elseif($value["id"]== 32){
//                        $banners[$key]['appUrl']=$value["theme"]."?session=".base64_encode($data['session_id'])."&ident=".base64_encode($data['device_identifier']);
//                    }elseif($value['id']== 33){
//                        $code='';
//                        if($data['session_id']){
//                            $couponM=new CouponModel;
//                            $res=$couponM->getcode(2,$userId);
//                            if($res){
//                                $code=$res[0]['code'];
//                            }
//                        }
//                        $banners[$key]['appUrl']=$value["theme"]."?se=".base64_encode($data['session_id'])."&ident=".base64_encode($data['device_identifier'])."&code=".$code;
//                    }
//
//                }else{
//                    $banners[$key]['type']=(string)$value["id"];
//                }
//
//
//            }
//        }

        $feedM = new FeedvideoModel();

        $type=1;

        $feedM->currentUser = $userId;
        $response['videos'] =$feedM->getFeeds($type,$data['page']);
        //话题和轮播图
        // print_r($banners);exit;
        $bannerM=new BannerModel();
        $AppM= new AppModel();
        $res = $AppM->getIdentifierInfo($data['device_identifier']);
        $bannerM->device_size  = $res ;
        $response['banners']=$bannerM->getbanners();

        return $response;

    }

    //首页
    public  function getNewHomePage($userId){
        //轮播图
        $bannerM=new BannerModel();
        $response['banners']=$bannerM->getbanners();

        //车辆列表
        $jsonData = require APPPATH .'/configs/JsonData.php';
        $carM = new CarSellingModel();
        $where = 'WHERE t1.files <> "" AND t1.brand_id <> 0 AND t1.series_id <> 0 AND t1.car_type <> 3 AND (t1.verify_status = 2 OR t1.verify_status = 11 OR t1.verify_status = 4) ';
        $carM->where = $where;
        $carM->order = $jsonData['order_info'][0];
        $carM->page = 1;
        $userId=0;
        $carM->currentUser =$userId;
        $lists = $carM->getCarList($userId);
        $response['car_list']=$lists;

        //视频
        $feedM = new FeedvideoModel();
        $type=1;
        $feedM->currentUser = $userId;
        $response['videos'] =$feedM->getFeeds($type,1);

        //文章列表
        $feedM = new Feedv1Model();
        $type=1;
        $feedM->currentUser = $userId;
        $response['article']=$feedM->getFeeds($type,1);

        //最佳车行
        $Profile=new ProfileModel();
        $Profile->pageSize=5;
        $page     = 1;
        $user=$Profile->getCompanylist($page);
        $response['company_list']=$user;

        return $response;

    }


    public function testidAction(){

        $identifier = "c95871090dd825a0da6a2ce9c967e3b3";
        $AppM= new AppModel();
        $res = $AppM->getIdentifierInfo($identifier);

        print_r($res);exit;
    }






}

