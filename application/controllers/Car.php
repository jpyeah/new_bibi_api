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
 * @api {POST} /Car/brand 获取品牌
 * @apiName Car brand
 * @apiGroup Car
 * @apiDescription 获取品牌
 * @apiPermission anyone
 * @apiSampleRequest http://new.bibicar.cn
 *
 *
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
    public function BrandAction()
    {
        $data = $this->get_request_data();

        if(@$data['session_id']){
            $sess = new SessionModel();
            $userId = $sess->Get($data);
        }else{
            $userId = 0;
        }

        $sql = 'SELECT `brand_id`, `brand_name`, `abbre`, `brand_url` FROM `new_bibi_car_brand_list`';

        $pdo = new PdoDb;

        $list = $pdo->query($sql);


        foreach($list as $k =>$val){

            if($userId){

            $focus = 'SELECT `brand_id` FROM `new_bibi_car_focus` WHERE user_id='.$userId.' AND brand_id='.$val['brand_id'];

            $pdo = new PdoDb;

            $res = $pdo->query($focus);

                if($res){

                    $list[$k]['is_focus']=1;

                }else{

                    $list[$k]['is_focus']=2;

                }

            }else{

                $list[$k]['is_focus']=2;

            }
        }

        $response['brand_list'] = $list;

        $this->send($response);

    }
    /**
     * @api {POST} /Car/series 获取系列
     * @apiName Car series
     * @apiGroup Car
     * @apiDescription 获取系列
     * @apiPermission anyone
     * @apiSampleRequest http://new.bibicar.cn
     *
     * @apiParam {number} [brand_id] 品牌id
     *
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
    public function seriesAction()
    {
        $this->required_fields = array_merge( array('brand_id'));

        $data = $this->get_request_data();

        $brand_id = $data['brand_id'];

        if (!$brand_id) {

            $this->send_error(NOT_ENOUGH_ARGS);
        }

        $pdo = new PdoDb;

        $sql = 'SELECT * FROM `new_bibi_car_brand_list` WHERE `brand_id` = ' . $brand_id ;

        $brand = @$pdo->query($sql)[0];

        $brandInfo = array();

        $brandInfo['series'] = array();

        $sql = 'SELECT `brand_series_id` AS `series_id`, `brand_series_name` AS `series_name` ,`brand_series_url` AS `series_url` , `makename`  FROM `new_bibi_car_brand_series` WHERE `brand_id` = ' . $brand_id;

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
     * @api {POST} /Car/model 获取车型
     * @apiName Car mode
     * @apiGroup Car
     * @apiDescription 获取系列
     * @apiPermission anyone
     * @apiSampleRequest http://new.bibicar.cn
     *
     * @apiParam {number} series_id 系列id
     * @apiSuccess {string} interior 内饰
     * @apiSuccess {string} exterior 外饰
     * @apiSuccess {string} model_url 图片链接
     * @apiSuccess {string} version 版本
     *
     * @apiParamExample {json} 请求样例
     *   GET /Car/model
     *   {
     *     "data": {
     *       "series_id":"",
     *
     *
     *     }
     *   }
     *
     */
    public  function  modelAction(){

        $this->required_fields = array_merge( array('series_id'));

        $data = $this->get_request_data();

        $series_id = $data['series_id'];

        $sql = 'SELECT `model_id` , `model_name`, `model_url`,`interior`,`exterior`,`version`,`price`FROM `new_bibi_car_series_model` WHERE  `series_id` = '.$series_id.' ORDER BY `model_name` DESC';

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



}