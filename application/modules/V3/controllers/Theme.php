<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/2
 * Time: 下午6:41
 */
//话题发布，讨论
class ThemeController extends ApiYafControllerAbstract {

    /**
     * @api {POST} /v3/theme/homepage 首页
     * @apiName Theme  homepage
     * @apiGroup Theme
     * @apiDescription 首页
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     *
     * @apiParam {string} device_identifier device_identifier
     * @apiParam {string} session_id session_id
     *
     *
     * @apiParamExample {json} 请求样例
     *   POST  /v3/theme/homepage
     *   {
     *     "data": {
     *       "device_identifier":"85e8c1b3a7e2b3a64296892bf56b3b42",
     *       "session_id":"session578614120f571",
     *
     *
     *     }
     *   }
     *
     */
    //首页
    public function homepageAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id'));
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
     * @api {POST} /v3/theme/themehome 大厅
     * @apiName Theme  themehome
     * @apiGroup Theme
     * @apiDescription 大厅
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     *
     * @apiParam {string} device_identifier device_identifier
     * @apiParam {string} session_id session_id
     *
     *
     * @apiSuccess {json} theme_recommend 推荐话题
     * @apiSuccess {json} theme_join 加入的话题
     * @apiSuccess {json} feed_list  最热话题动态列表
     *
     */

    //大厅
    public  function themehomeAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id','page'));

        $data = $this->get_request_data();

        $sess = new SessionModel();

        $userId = $sess->Get($data);

        $data['page']  = $data['page'] ? ($data['page']+1) : 1;

        $themeM= new ThemelistModel();

        $theme_recommend=$themeM->getThemes(1,$userId,1,3);

        $response['theme_recommend'] = $theme_recommend['theme_list'];//推荐话题

        $theme_join=$themeM->getThemes(1,$userId,1,1);

        $response['theme_join']=$theme_join['theme_list'];//我加入的话题

        $FeedThemeM = new FeedThemeModel();

        $feeds = $FeedThemeM->getFeeds(0,8,0,$data['page']);

        $response['feed_list']=$feeds['feed_list'];//本周最热

        return $this->send($response);

    }

    /**
     * @api {POST} /v3/theme/searchtheme 话题搜索
     * @apiName Theme  search
     * @apiGroup Theme
     * @apiDescription 话题申请
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     *
     * @apiParam {string} device_identifier device_identifier
     * @apiParam {string} session_id session_id
     * @apiParam {string} page 页码
     * @apiParam {string} keyword 搜索词
     *
     *
     * @apiParamExample {json} 请求样例
     *   POST  /v3/theme/searchtheme
     *   {
     *     "data": {
     *       "device_identifier":"85e8c1b3a7e2b3a64296892bf56b3b42",
     *       "session_id":"session578614120f571",
     *       "page":"0",
     *       "keyword":"摄影",
     *
     *
     *     }
     *   }
     *
     */
    //搜索话题
    public function searchthemeAction(){


        $this->required_fields = array_merge($this->required_fields,array('session_id','page','keyword'));

        $data = $this->get_request_data();

        $sess = new SessionModel();
        $userId = $sess->Get($data);

        $data['page']  = $data['page'] ? ($data['page']+1) : 1;

        $themeM= new ThemelistModel();

        $themeM->currentUser = $userId;

        $themeM->keyword = $data['keyword'];

        $response = $themeM->getThemes(1,$userId,$data['page'],2);

        $this->send($response);

    }

    /**
     * @api {POST} /v3/theme/followtheme 加入话题
     * @apiName Theme  follow
     * @apiGroup Theme
     * @apiDescription 加入话题
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     *
     * @apiParam {string} device_identifier device_identifier
     * @apiParam {string} session_id session_id
     * @apiParam {string} theme_id  话题Id
     *
     *
     * @apiParamExample {json} 请求样例
     *   POST  /v3/theme/followtheme
     *   {
     *     "data": {
     *       "device_identifier":"85e8c1b3a7e2b3a64296892bf56b3b42",
     *       "session_id":"session578614120f571",
     *       "theme_id":"39",
     *
     *     }
     *   }
     *
     */

    //加入话题
    public function followthemeAction(){

        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id', 'theme_id')
        );

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $themelistM= new ThemelistModel();

        $themeInfo = $themelistM->gettheme($data['theme_id']);

        if(!$themeInfo){

            $this->send_error(NOT_FOUND);
        }
        $themeuserM= new ThemeUserModel();
        $theme= $themeuserM->getTheme($data['theme_id'],$userId);

        if($theme){
            $this->send_error(HAS_EXSIT);
        }else{
            $time = time();
            $themeuserM->user_id = $userId;
            $themeuserM->theme_id = $data['theme_id'];
            $themeuserM->created = $time;
            $themeuserM->saveProperties();
            $themeId = $themeuserM->CreateM();
            $this->send($themeInfo);
        }
    }

    /**
     * @api {POST} /v3/theme/gettheme 判断话题是否被创建
     * @apiName Theme  gettheme
     * @apiGroup Theme
     * @apiDescription 判断话题是否被创建
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     *
     * @apiParam {string} device_identifier device_identifier
     * @apiParam {string} session_id session_id
     * @apiParam {string} theme  话题
     *
     *
     * @apiParamExample {json} 请求样例
     *   POST  /v3/theme/gettheme
     *   {
     *     "data": {
     *       "device_identifier":"85e8c1b3a7e2b3a64296892bf56b3b42",
     *       "session_id":"session578614120f571",
     *       "theme":"#手机摄影#",
     *
     *
     *     }
     *   }
     *
     */
    //判断是否被创建
    public function getthemeAction(){

        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id', 'theme')
        );
        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $themeM= new ThemelistModel();

        //判断话题是否存在
        $theme = $themeM->getThemeByTheme($data['theme']);

        if($theme){
            $this->send($theme);
        }else{
            $this->send_error(NOT_FOUND);
        }

    }

    /**
     * @api {POST} /v3/theme/createtheme 创建话题
     * @apiName Theme  createtheme
     * @apiGroup Theme
     * @apiDescription 创建话题
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     *
     * @apiParam {string} device_identifier device_identifier
     * @apiParam {string} session_id session_id
     * @apiParam {string} theme  话题
     * @apiParam {string} post_file 图片（七牛hash）
     * @apiParam {string} title  标题
     *
     *
     * @apiParamExample {json} 请求样例
     *   POST  /v3/theme/createtheme
     *   {
     *     "data": {
     *       "device_identifier":"85e8c1b3a7e2b3a64296892bf56b3b42",
     *       "session_id":"session578614120f571",
     *       "theme":"#手机摄影#",
     *       "post_file":"videoback.png",
     *       "title":"#手机摄影#",
     *
     *
     *     }
     *   }
     *
     */
    //创建话题
    public function createthemeAction(){
       
        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id', 'theme','post_file','title')
        );
       
        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $themeM= new ThemelistModel();

        //判断权限 是否有权限创建
        $is_auth = $this->theme_create_user_auth();

        //判断话题是否存在
        $theme = $themeM->getThemeByTheme($data['theme']);

        if($theme){
            $this->send_error(HAS_EXSIT);
        }

        if($is_auth){

            $time = time();
            $themeM->user_id = $userId;
            $themeM->theme = $data['theme'];
            $themeM->created = $time;
            $themeM->post_file = $data['post_file'];
            $themeM->title= $data['title'];
            $themeM->saveProperties();

            $themeId = $themeM->CreateM();

            $themeInfo = $themeM->gettheme($themeId);

            $this->send($themeInfo);

        }else{

            $this->send_error(PACT_CAR_NOT_AUTH);
        }

    }

    /**
     * @api {POST} /v3/theme/list 话题列表
     * @apiName Theme  list
     * @apiGroup Theme
     * @apiDescription 话题列表
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     *
     * @apiParam {string} device_identifier device_identifier
     * @apiParam {string} session_id session_id
     * @apiParam {number} page 页码
     *
     *
     * @apiParamExample {json} 请求样例
     *   POST  /v3/theme/list
     *   {
     *     "data": {
     *       "device_identifier":"85e8c1b3a7e2b3a64296892bf56b3b42",
     *       "session_id":"session578614120f571",
     *       "page":"0",
     *
     *
     *     }
     *   }
     *
     */

    //话题列表
    public function listAction(){
       
        $this->required_fields = array_merge($this->required_fields,array('session_id','page'));

        $data = $this->get_request_data();

        $sess = new SessionModel();
        $userId = $sess->Get($data);

        $data['page']  = $data['page'] ? ($data['page']+1) : 1;
       
        $themeM= new ThemelistModel();

        $themeM->currentUser = $userId;
        
        $response = $themeM->getThemes(1,0,$data['page']);

        $this->send($response);

    }


    /**
     * @api {POST} /v3/theme/followthemelist 我加入的话题
     * @apiName Theme  followthemelist
     * @apiGroup Theme
     * @apiDescription 我加入的话题
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     *
     * @apiParam {string} device_identifier device_identifier
     * @apiParam {string} session_id session_id
     * @apiParam {number} page 页码
     *
     *
     * @apiParamExample {json} 请求样例
     *   POST  /v3/theme/followthemelist
     *   {
     *     "data": {
     *       "device_identifier":"85e8c1b3a7e2b3a64296892bf56b3b42",
     *       "session_id":"session578614120f571",
     *       "page":"0",
     *
     *
     *     }
     *   }
     *
     */
    //我关注的话题
    public function followthemelistAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id','page'));

        $data = $this->get_request_data();

        $sess = new SessionModel();

        $userId = $sess->Get($data);

        $data['page']  = $data['page'] ? ($data['page']+1) : 1;

        $themeM= new ThemelistModel();

        $themeM->currentUser = $userId;

        $response = $themeM->getThemes(1,$userId,$data['page']);

        $banners = array(

            array(
                'imgUrl'=>"http://img.bibicar.cn/theme-sui.jpg",
                'appUrl'=>"#话题#",
                'title' =>"岁月是一场有去无回的旅行，走过的路，错过的人，遇见的事，好的坏的都是风景",
            )

        );

        $response['banners'] = $banners;

        $this->send($response);

    }

    /**
     * @api {POST} /v3/theme/themeindex 话题详情
     * @apiName Theme  themeindex
     * @apiGroup Theme
     * @apiDescription 话题详情
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     *
     * @apiParam {string} device_identifier device_identifier
     * @apiParam {string} session_id session_id
     * @apiParam {number} theme_id 话题Id
     * @apiParam {number} page 页码
     *
     * @apiSuccess {json} theme_user 加入话题的用户
     * @apiSuccess {json} theme_info 话题详情
     * @apiSuccess {json} feed_list  话题动态列表
     * @apiSuccess {string} is_join  是否加入(1:已加入话题 0:未加入)
     *
     */

    //话题详情
    public function themeindexAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id','theme_id','page'));

        $data = $this->get_request_data();
        $sess = new SessionModel();
        $userId = $sess->Get($data);


        $data['post_type'] = 7;
        $data['page']     = $data['page'] ? ($data['page']+1) : 1;
       
        $feedM = new FeedThemeModel();
        $themeM= new ThemelistModel();
        $themeUserM = new ThemeUserModel();
        $theme= $themeM->getTheme($data['theme_id']);
        $feedM->currentUser = $userId;
        $feedM->currenttheme= $theme["theme"];
        $response = $feedM->getFeeds(0,$data['post_type'],$userId,$data['page']);
        $response['theme_user']=$themeUserM->getThemeUser($data['theme_id']);
        $response['theme_info']=$theme;
        $theme= $themeUserM->getTheme($data['theme_id'],$userId);
        if($theme){
           $response['is_join'] = 1;
        }else{
            $response['is_join'] = 0;
        }
        $this->send($response);

    }

    public function deleteAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id','Theme_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $themeM= new ThemelistModel();
        $themeM->currentUser = $userId;

        $themeM->deleteTheme($data['Theme_id']);

        $this->send();

    }
    //首页
    public  function getNewHomePage($userId){
        //轮播图
        $bannerM=new BannerModel();
        $response['banners']=$bannerM->getbanners();

        //车辆列表
        $jsonData = require APPPATH .'/configs/JsonData.php';
        $carM = new CarSellingV1Model();
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


    /**
     * @api {POST} /v3/theme/myfocus 我的关注
     * @apiName Theme  myfocus
     * @apiGroup Theme
     * @apiDescription 我的关注
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     *
     * @apiParam {string} device_identifier device_identifier
     * @apiParam {string} session_id session_id
     * @apiParam {number} page 页码
     *
     * @apiSuccess {json} type 类型 1:车辆 2:发布状态 3:加入话题
     * @apiSuccess {json} id 话题详情
     * @apiSuccess {json} feed_list  话题动态列表
     *
     */

    //我的关注

    public function MyFocusAction(){




    }


}