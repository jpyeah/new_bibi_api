<?php

/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/12/12
 * Time: 下午12:30
 */
class CarReportController extends ApiYafControllerAbstract
{

    public $info_fields = array(
        'session_id', 'files_id', 'files_type','car_color','brand_id','series_id','model_id',
        'contact_phone','contact_name','guide_price', 'board_fee','insurance_fee',
        'other_fee','other_fee_intro','extra_info','bank_no','bank_name','bank_account','promise','purch_fee','total_price','report_time','car_intro','status');

    public function publishProgress($data,$userId){

        $properties['car_id']=$data['car_id'];
        $properties['hash']=$data['hash'];
        $properties['user_id'] = $userId;

        $bm = new BrandModel();
        $brandM = $bm->getBrandModel($data['brand_id']);
        $seriesM = $bm->getSeriesModel($data['brand_id'], $data['series_id']);
        $modelM = $bm->getModelModel($data['series_id'], $data['model_id']);
        if (!is_array($brandM)) {
            $this->send_error(CAR_BRAND_ERROR);
        }
        if (!is_array($seriesM)) {
            $this->send_error(CAR_SERIES_ERROR);
        }
        if (!is_array($modelM)) {
            $this->send_error(CAR_MODEL_ERROR);
        }
        $properties['car_name'] = $brandM['brand_name'] . ' ' . $seriesM['series_name'] . ' ' . $modelM['model_name'];
        $properties['car_name'] = trim($properties['car_name']);

        $properties['brand_id']      =$data['brand_id'];
        $properties['series_id']     =$data['series_id'];
        $properties['model_id']      =$data['model_id'];
        $properties['car_color']      =$data['car_color'];
        $properties['model_name']    =$modelM['model_name'];
        $properties['series_name']   =$seriesM['series_name'];
        $properties['brand_name']    =$brandM['brand_name'];
        $properties['guide_price']   = $data['guide_price'];
        $properties['board_fee']     = $data['board_fee'];
        $properties['insurance_fee'] = $data['insurance_fee'];
        $properties['other_fee'] = $data['other_fee'];
        $properties['other_fee_intro'] = $data['other_fee_intro'];
        $properties['extra_info'] = $data['extra_info'];
        $properties['bank_no'] = $data['bank_no'];
        $properties['bank_name'] = $data['bank_name'];
        $properties['bank_account'] = $data['bank_account'];
        $properties['contact_phone'] = $data['contact_phone'];
        $properties['contact_name'] = $data['contact_name'];
        $properties['contact_address'] = @$data['contact_address'];
        $properties['purch_fee'] = $data['purch_fee'];
        $properties['promise'] = $data['promise'];
        $properties['total_price'] = $data['total_price'];
        $properties['report_time'] = $data['report_time'];
        $properties['car_intro'] = $data['car_intro'];
        $properties['status'] = $data['status'];

        $properties['last_price'] = $data['last_price'];
        $properties['transfer_fee'] = $data['transfer_fee'];
        $properties['car_no'] = $data['car_no'];
        $properties['exchange_time'] = $data['exchange_time'];
        $properties['mileage'] = $data['mileage'];
        $properties['board_time'] = $data['board_time'];
        $properties['board_address'] = $data['board_address'];
        $properties['ins_type'] = $data['ins_type'];
        $properties['tci_time'] = $data['tci_time'];
        $properties['vci_time'] = $data['vci_time'];
        $properties['envirstandard'] = $data['envirstandard'];

        $time = time();
        $properties['created'] = $time;
        $properties['updated'] = $time;

        $filesInfo = $this->dealFilesWithString($data['files_id'], $data['files_type']);

        $properties['files'] = $filesInfo ? serialize($filesInfo) : '';

        if (!$properties['files']) {
            $this->send_error(CAR_CREATE_FILES_ERROR);
        }

        return $properties;
    }
    /**
     * @api {POST} /v5/carreport/create  生成报价单
     * @apiName carreport create
     * @apiGroup Carreport
     * @apiDescription 生成报价单
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.5.3
     *
     * @apiParam (request) {string} [device_identifier] 设备唯一标识
     * @apiParam (request) {string} [session_id] session_id
     * @apiParam (request) {Object} [file_type] 图片类型说明 默认填写 1
     * @apiParam (request) {Object} [files_id] 七牛图片hash
     * @apiParam (request) {number} car_id 车辆id (详情有返回)
     * @apiParam (request) {number} hash   车辆car_id(详情有返回)
     * @apiParam (request) {number} car_color 车辆颜色
     * @apiParam (request) {number} brand_id 车品牌id
     * @apiParam (request) {number} series_id 车系列id
     * @apiParam (request) {number} model_id 车型id
     * @apiParam (request) {string} contact_phone 联系电话
     * @apiParam (request) {string} contact_name 联系人姓名
     * @apiParam (request) {string} [contact_address] 联系地址
     * @apiParam (request) {string} guide_price 指导价
     * @apiParam (request) {string} [board_fee] 上牌费用
     * @apiParam (request) {string} [insurance_fee] 保险费用
     * @apiParam (request) {string} [other_fee] 其他费用
     * @apiParam (request) {string} [other_fee_intro] 其他费用说明
     * @apiParam (request) {string} [bank_no] 银行卡号
     * @apiParam (request) {string} [bank_name] 银行名称
     * @apiParam (request) {string} [bank_account] 开户人名称
     * @apiParam (request) {string} [extra_info] 基本配置选项(id与逗号拼接字符串 2,3,4,5)
     * @apiParam (request) {string} promise 承诺
     * @apiParam (request) {string} total_price 合计总价
     * @apiParam (request) {string} purch_fee 购置税
     * @apiParam (request) {string} report_time 报价时间 时间戳
     * @apiParam (request) {string} car_intro 车辆描述
     * @apiParam (request) {number} status 是否保存 1:保存 2：不保存
     *
     * @apiParam (request) {string} last_price 新车4s店最低价
     * @apiParam (request) {string} transfer_fee 过户费
     * @apiParam (request) {string} car_no  车牌号
     * @apiParam (request) {string} exchange_time 过户次数
     * @apiParam (request) {string} mileage  公里数
     * @apiParam (request) {string} board_time 上牌时间
     * @apiParam (request) {string} board_address  上牌地址
     * @apiParam (request) {string} ins_type  种类
     * @apiParam (request) {string} tci_time  交强险
     * @apiParam (request) {string} vci_time  商业险
     * @apiParam (request) {string} envirstandard 环保标准 国1 国2 国3 国4 国5
     *
     */
    public function createAction()
    {
        $this->required_fields = array_merge(
            $this->required_fields,
            $this->info_fields
        );

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $properties = $this->publishProgress($data, $userId);

        $csReport  = new CarSellingReportModel();

        $csReport->properties = $properties;

        $ReportId = $csReport->CreateM();

        if ($ReportId) {
            $response['info']=$csReport->getReportV1($ReportId);
            $title = "【吡吡汽车】- 报价单";
            $response['share_title'] = $title;
            $response['share_url'] ='http://share.bibicar.cn/views/detail/offer.html?session='.$data['session_id'].'&id='.$ReportId.'&ident='.$data['device_identifier'];
            $response['share_txt'] = '更多精选二手车在bibi car,欢迎您来选购!';
            $response['share_img'] = isset($response['info']['files']["type1"]) ? $response['info']['files']["type1"][0]['file_url'] : '';
            $this->send($response);
        } else {
            $this->send_error(CAR_ADDED_ERROR);
        }

    }

    /**
     * @api {POST} /v5/carreport/view  报价单详情
     * @apiName carreport view
     * @apiGroup Carreport
     * @apiDescription 报价单详情
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.5.3
     *
     * @apiParam {string} [device_identifier] 设备唯一标识
     * @apiParam {string} session_id session_id
     * @apiParam {string} report_id 报价单id
     *
     * @apiParam (response) {string} files 图片
     *
     *
     */
    public function viewAction(){

        $this->required_fields = array_merge($this->required_fields, array( 'session_id','report_id'));

        $data = $this->get_request_data();

        $sess = new SessionModel();
        $userId = $sess->Get($data);

        $CarReport = new CarSellingReportModel();

        $report = $CarReport->getReportV1($data['report_id']);
        $response['info']=$report;
        $title = "【吡吡汽车】- 报价单";
        $response['share_title'] = $title;
        $response['share_url'] ='http://share.bibicar.cn/views/detail/offer.html?session='.$data['session_id'].'&id='.$data['report_id'].'&ident='.$data['device_identifier'];
        $response['share_txt'] = '更多精选二手车在bibi car,欢迎您来选购!';
        $response['share_img'] = isset($report['files']["type1"]) ? $report['files']["type1"][0]['file_url'] : '';
        $this->send($response);
    }

    /**
     * @api {POST} /v5/carreport/list 历史报价
     * @apiName carreport list
     * @apiGroup Carreport
     * @apiDescription 历史报价
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.5.3
     *
     * @apiParam {string} device_identifier 设备唯一标识
     * @apiParam {string} session_id session_id
     * @apiParam {string} car_id car_id 车辆id
     * @apiParam {number} page 页数
     *
     * @apiParamExample {json} 请求样例
     *   POST /v5/carreport/list
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "car_id",
     *       "page":"",
     *
     *     }
     *   }
     *
     */
    public function listAction(){

        $this->required_fields = array_merge($this->required_fields, array('session_id','page','car_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $CarReport = new CarSellingReportModel();

        $CarReport->page =  $data['page'] ? ($data['page']+1) : 1;

        $CarReport->user_id = $userId;

        $CarReport->car_id  = $data['car_id'];

        $reports = $CarReport->getReports();

        $this->send($reports);

    }


    /**
     * @api {POST} /v5/carreport/delete 删除报价单
     * @apiName carreport delete
     * @apiGroup Carreport
     * @apiDescription 删除报价单
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     * @apiVersion 2.5.3
     *
     * @apiParam {string} device_identifier 设备唯一标识
     * @apiParam {string} session_id session_id
     * @apiParam {string} report_id 报价单id
     *
     * @apiParamExample {json} 请求样例
     *   POST /v5/carreport/delete
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "report_id",
     *
     *     }
     *   }
     *
     */

    public function deleteAction(){

        $this->required_fields = array_merge($this->required_fields, array( 'session_id','report_id'));

        $data = $this->get_request_data();

        $sess = new SessionModel();

        $userId = $sess->Get($data);

        $CarReport = new CarSellingReportModel();

        $report = $CarReport->getReportV1($data['report_id']);

        if($report && $report['user_id'] == $userId ){

            $CarReport->deleteReport($data['report_id']);

            $response['message']="删除成功";

            $this->send($response);
        }else{
            $this->send_error(NOT_FOUND);


        }

    }













}