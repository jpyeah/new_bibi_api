<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/10
 * Time: 下午4:05
 */
class CarController extends ApiYafControllerAbstract
{

/**
 * @apiDefine Data
 *
 * @apiParam (data) {string}  [device_identifier=ce32eaab37220890a063845bf6b6dc1a]  设备唯一标示.
 * @apiParam (data) {string}  [session_id=session5845346a59a31]     用户session_id.
 * @apiParam (data) {json}    [mobile_list=18]     车型id默认值是18.
 * 
 * 
 */

/**
 * @api {POST} /Car/brand 获取品牌
 * @apiName car brand 
 * @apiGroup Publish
 * @apiDescription 获取品牌
 * @apiPermission anyone
 * @apiSampleRequest http://www.bibicar.cn:8090
 *
 * @apiParam {string} [device_identifier] 设备唯一标识
 * 
 * @apiParam {json} data object
 * @apiUse Data
 * @apiParamExample {json} 请求样例
 *   POST /Car/brand
 *   {
 *     "data": {
 *       "device_identifier":"",
 *       
 *       
 *     }
 *   }
 *
 */
    public function brandAction()
    {

        $sql = 'SELECT `brand_id`, `brand_name`, `abbre`, `brand_url` FROM `bibi_car_brand_list` WHERE is_hot = 1 ORDER BY `abbre` ASC';

        $pdo = new PdoDb;
        $list = $pdo->query($sql);

        $brandList = array();

        foreach ($list as $key => $item) {

            $alpha = $item['abbre'];
            unset($item['abbre']);
            $brandList[$alpha][] = $item;

        }

        $response = array();
        $response['brand_list'] = $brandList;


        $this->send($response);

    }
/**
 * @api {POST} /Car/series 获取系列
 * @apiName car series 
 * @apiGroup Publish
 * @apiDescription 获取系列
 * @apiPermission anyone
 * @apiSampleRequest http://www.bibicar.cn:8090
 *
 * @apiParam {string} [device_identifier] 设备唯一标识
 * @apiParam {number} [brand_id] 品牌id
 * 
 * @apiParam {json} data object
 * @apiUse Data
 * @apiParamExample {json} 请求样例
 *   POST /Car/series
 *   {
 *     "data": {
 *       "device_identifier":"",
 *       "brand_id":"",
 *       
 *       
 *     }
 *   }
 *
 */
    public function seriesAction($brand_id)
    {

        if (!$brand_id) {

            $this->send_error(NOT_ENOUGH_ARGS);
        }

        $pdo = new PdoDb;

        $sql = 'SELECT * FROM `bibi_car_brand_list` WHERE `brand_id` = ' . $brand_id . '';
        $brand = @$pdo->query($sql)[0];

        $brandInfo = array();


        $brandInfo['series'] = array();

        $sql = 'SELECT `brand_series_id` AS `series_id`, `brand_series_name` AS `series_name` , `makename`  FROM `bibi_car_brand_series` WHERE `brand_id` = ' . $brand_id . ' AND `saleStatus` =1 ORDER BY `makename` , `series_name` ASC';

        $series = $pdo->query($sql);

        $info = array();

        foreach($series as $serie){

            $serie['brand_id'] = $brand['brand_id'];
            $serie['series_name'] .= ' '.$serie["makename"].'';
            $info[] = $serie;
        }

        $response = array();
        $response['series_list'] = $info;

        $this->send($response);


    }
/**
 * @api {POST} /Car/series 获取车型
 * @apiName car series
 * @apiGroup Publish
 * @apiDescription 获取系列
 * @apiPermission anyone
 * @apiSampleRequest http://www.bibicar.cn:8090
 *
 * @apiParam {string} [device_identifier] 设备唯一标识
 * @apiParam {number} [series_id] 系列id
 * 
 * @apiParam {json} data object
 * @apiUse Data
 * @apiParamExample {json} 请求样例
 *   POST /Car/series
 *   {
 *     "data": {
 *       "device_identifier":"",
 *       "series_id":"",
 *       
 *       
 *     }
 *   }
 *
 */
    public  function  modelAction($series_id){

        $sql = 'SELECT `model_id` , `model_name` FROM `bibi_car_series_model` WHERE  `series_id` = '.$series_id.' ORDER BY `model_name` DESC';

        $pdo = new PdoDb;

        $info = array();

        $models = $pdo->query($sql);

        foreach($models as $k => $model){

            $model['series_id'] = $series_id;
            $info[]  = $model;

        }

        $response = array();
        $response['model_list'] = $info;

        $this->send($response);
    }

    /**
     * @api {POST} /Car/extrainfo 获取基本配置
     * @apiName car extrainfo
     * @apiGroup Publish
     * @apiDescription 获取基本配置
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     *
     * @apiParam {string} [device_identifier] 设备唯一标识
     *
     * @apiParamExample {json} 请求样例
     *   POST /Car/extrainfo
     *   {
     *     "data": {
     *       "device_identifier":"",
     *     }
     *   }
     *
     */
    public  function  ExtrainfoAction(){

        $ExtraModel = new CarSellingExtraInfoModel();

        $response = $ExtraModel->getExtrainfolist();

        $this->send($response);
    }

 /**
 * @api {POST} /car/province 获取省份
 * @apiName car  province
 * @apiGroup Publish
 * @apiDescription 获取省份
 * @apiPermission anyone
 * @apiSampleRequest http://testapi.bibicar.cn
 *
 * @apiParam {string} [device_identifier] 设备唯一标识
 * @apiParamExample {json} 请求样例
 *   POST /car/province
 *   {
 *     "data": {
 *       "device_identifier":"",
 *       
 *       
 *     }
 *   }
 *
 */
     public  function  provinceAction(){

        $sql = 'SELECT `province_id` , `province` FROM `bibi_zode_province`  ORDER BY `province_id` ASC';

        $pdo = new PdoDb;
        $list = $pdo->query($sql);

        $provinceList = array();

        foreach ($list as $key => $item) {
            $provinceList[] = $item;
        }

        $response = array();
        $response['province_list'] = $provinceList;
        $this->send($response);

    }

/**
 * @api {POST} /car/city 获取城市
 * @apiName car city
 * @apiGroup Publish
 * @apiDescription 获取城市
 * @apiPermission anyone
 * @apiSampleRequest http://testapi.bibicar.cn
 *
 * @apiParam {string} [device_identifier] 设备唯一标识
 * @apiParam {string} [province_id]  省份id
 *
 * @apiParamExample {json} 请求样例
 *   POST /car/city
 *   {
 *     "data": {
 *       "device_identifier":"",
 *       "province_id":"",
 *       
 *       
 *     }
 *   }
 *
 */
    public  function cityAction($province_id){

        if (!$province_id) {
            $this->send_error(NOT_ENOUGH_ARGS);
        }

        $sql = 'SELECT `city_name` , `city_code`,`abbr`,`engineno`,`classno` FROM `bibi_zode_citys` WHERE  `province_id` = '.$province_id.' ORDER BY `id` ASC';

        $pdo = new PdoDb;

        $info = array();

        $citys = $pdo->query($sql);

        foreach($citys as $k => $city){
            $info[]  = $city;

        }

        $response = array();
        $response['city_list'] = $info;

        $this->send($response);
    }

    /**
     * @api {POST} /car/gaodeprovince 获取高德省份
     * @apiName car  gaodeprovince
     * @apiGroup Publish
     * @apiDescription 获取省份
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     *
     * @apiParam {string} [device_identifier] 设备唯一标识
     * @apiParamExample {json} 请求样例
     *   POST /car/province
     *   {
     *     "data": {
     *       "device_identifier":"",
     *
     *
     *     }
     *   }
     *
     */
    public  function  gaodeprovinceAction(){

        $pdo = new PdoDb;

        $sql = 'SELECT id as province_id,cate_name as province  FROM `bibi_car_cate_area` WHERE pid = 0 AND level = 1';

        $list = $pdo->query($sql);

        $provinceList = array();

        foreach ($list as $key => $item) {
            $provinceList[] = $item;
        }
        $response = array();
        $response['province_list'] = $provinceList;
        $this->send($response);

    }

    /**
     * @api {POST} /car/gaodecity 获取高德城市
     * @apiName car gaodecity
     * @apiGroup Publish
     * @apiDescription 获取城市
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     *
     * @apiParam {string} [device_identifier] 设备唯一标识
     * @apiParam {string} [province_id]  省份id
     * @apiParam {string} [city_name]    城市名称
     * @apiParam {string} [city_code]    城市编码
     * @apiParam {string} [adcode]       地区编码
     * @apiParam {string} [latitude]     纬度
     * @apiParam {string} [longitude]    经度
     *
     * @apiParamExample {json} 请求样例
     *   POST /car/city
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "province_id":"",
     *
     *
     *     }
     *   }
     *
     */
    public  function gaodecityAction(){

        $this->required_fields = array_merge($this->required_fields, array( 'province_id'));

        $data = $this->get_request_data();

        if (!$data['province_id']) {
            $this->send_error(NOT_ENOUGH_ARGS);
        }
        $pdo = new PdoDb;

        $sql = 'SELECT id as city_id,cate_name as city_name , citycode as city_code ,adcode,latitude,longitude FROM `bibi_car_cate_area` WHERE  `pid` = '.$data['province_id'].' AND level = 2';

        $info = array();

        $citys = $pdo->query($sql);

        $response = array();
        $response['city_list'] = $citys;
        $this->send($response);
    }


    public function getGradeTooneAction(){


            $sql = 'SELECT `grade`, `content`, `id`, `avatar`,`father_id` FROM `bibi_grade` WHERE `grade` = 1 ';
            $pdo = new PdoDb;
            
            $gradeM = $pdo->query($sql);
           
            if(isset($gradeM[0])){
                 $response = array();
                 $response['grade_list'] = $gradeM;

                 $this->send($response);
            }
            else{
                return new stdClass;
            }



    }

    public function getGradeTotwoAction(){
         
            $fatherId=414;
            $sql = 'SELECT `grade`, `content`, `id`, `avatar`,`father_id` FROM `bibi_grade` WHERE `grade` =2  AND `father_id`="'.$fatherId.'"';
             $pdo = new PdoDb;
            $gradeM = $pdo->query($sql);

            if(isset($gradeM[0])){
                 
                 $response = array();
                 $response['grade_list'] = $gradeM;

                 $this->send($response);
            }
            else{
                return new stdClass;
            }


    }

    public function  getGradeTothreeAction()
    {

            $fatherId=438;
            $sql = 'SELECT `grade`, `content`, `id`, `avatar`,`father_id` FROM `bibi_grade` WHERE `grade` =3  AND `father_id`="'.$fatherId.'"';
             $pdo = new PdoDb;
            $gradeM = $pdo->query($sql);

            if(isset($gradeM[0])){

                $response = array();
                 $response['grade_list'] = $gradeM;

                 $this->send($response);
            }
            else{
                return new stdClass;
            }



    }


}