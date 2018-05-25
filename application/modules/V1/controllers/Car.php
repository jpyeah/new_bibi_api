<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/13
 * Time: 下午6:09
 */
class CarController extends ApiYafControllerAbstract
{

    /**
     * @api {POST} /v1/car/series 车辆系列表(首页)
     * @apiName car series
     * @apiGroup Car
     * @apiDescription 车辆系列表(首页)
     * @apiPermission anyone
     * @apiSampleRequest http://new.bibicar.cn
     * @apiVersion 1.0.0
     *
     * @apiParam {string} page 页码 从0开始
     *
     * @apiParamExample {json} 请求样例
     *    POST /v1/car/seires
     *   {
     *     "data": {
     *       "page":"",
     *
     *     }
     *   }
     *
     */
    public function seriesAction(){

        $this->required_fields = array_merge(array('page'));

        $data = $this->get_request_data();

        $data['page']     = $data['page'] ? ($data['page']+1) : 1;

        $BrandM = new BrandModel();

        $list = $BrandM->getSeries($data['page']);

        $this->send($list);
    }


    public function SearchAction(){

        $this->required_fields = array_merge($this->required_fields, array('keyword','page'));

        $data = $this->get_request_data();

        $data['page']     = $data['page'] ? ($data['page']+1) : 1;

        $number = ($data['page']-1)*10;

        $carM = new CarSellingV1Model();

        $results = $this->searchcar($data['keyword'], $number);

        if($results['hits']['hits']){

            $inStr = $this->implodeArrayByKey('_id',$results['hits']['hits']);

            $where = '';

            $where .= ' where t1.id in (' . $inStr . ') ORDER By field(t1.id,'.$inStr.')'; //ORDER BY t3.comment_id DESC

            $carM = new CarSellingV1Model();

            $carM->where = $where;

            $list = $carM->getCarlistByIds();

        }else{

            $list=array();
        }
        $total=$results['hits']['total'];

        $count = count($results['hits']['hits']);

        $lists['car_list']=$list;
        $lists['has_more'] = (($number+$count) < $total) ? 1 : 2;
        $lists['total'] = $total;
        $lists['number'] = $number;

        $lists['custom_url'] = "http://custom.bibicar.cn/customize";


        return $this->send($lists);
    }

    /**
     * @api {POST} /v1/car/index 车型车辆详情
     * @apiName car index
     * @apiGroup Car
     * @apiDescription 车型车辆详情
     * @apiPermission anyone
     * @apiSampleRequest http://new.bibicar.cn
     * @apiVersion 1.0.0
     *
     * @apiParam {string} session_id session
     * @apiParam {string} model_id 车型Id
     *
     * @apiParamExample {json} 请求样例
     *    POST /v1/car/index
     *   {
     *     "data": {
     *       "session_id":"",
     *       "model_id":"",
     *
     *     }
     *   }
     *
     */
    public function indexAction()
    {

        $this->required_fields = array_merge($this->required_fields, array('session_id', 'model_id'));

        $data = $this->get_request_data();

        //$userId = $this->userAuth($data);
        if(@$data['session_id']){
            $sess = new SessionModel();
            $userId = $sess->Get($data);
        }else{
            $userId = 0;
        }

        $carModel = new CarSellingModel();

        $carT = $carModel::$table;

        $ModleId = $data['model_id'];

        $carInfo = $carModel->GetCarInfoById($ModleId,$userId);

        $response['car_info'] = $carInfo;

//        $brandId = isset($carInfo['brand_info']['brand_id']) ? $carInfo['brand_info']['brand_id'] : 0;
//
//        $title = is_array($carInfo['user_info']) ?
//                    $carInfo['user_info']['profile']['nickname'] . '的' . $carInfo['car_name']
//                    : $carInfo['car_name'];
//
//        $response['share_title'] = $title;
//        //http://m.bibicar.cn/post/index?device_identifier='.$data['device_identifier'].'&fcar_id='.$carId.'
//        $response['share_url'] = 'http://wx.bibicar.cn/car/index/car_id/'.$carId.'';
//        $response['share_txt'] = '更多精选二手车在bibi car,欢迎您来选购!';
//        $response['share_img'] = isset($carInfo['files'][0]) ? $carInfo['files'][0]['file_url'] : '';

        $this->send($response);


    }










}
