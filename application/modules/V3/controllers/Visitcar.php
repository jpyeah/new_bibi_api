<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/12/19
 * Time: 下午10:39
 */

class VisitcarController extends ApiYafControllerAbstract {

/**
 * @apiDefine Data
 *
 * @apiParam (data) {string} [device_identifier]  设备唯一标示.
 * @apiParam (data) {string} [session_id]     用户session_id.
 * 
 * 
 */

/**
 * @api {POST} /v3/Visitcar/list 浏览过的车
 * @apiName car visit list
 * @apiGroup Car
 * @apiDescription 浏览过的车
 * @apiPermission anyone
 * @apiSampleRequest http://www.bibicar.cn:8090
 *
 * @apiParam {string} [device_identifier] 设备唯一标识
 * @apiParam {string} [session_id] session_id
 * 
 * @apiParam {json} data object
 * @apiUse DreamParam
 * @apiParamExample {json} 请求样例
 *   POST /v3/Visitcar/list
 *   {
 *     "data": {
 *       "device_identifier":"",
 *       "session_id":"",
 *       
 *       
 *     }
 *   }
 *
 */
    public function listAction(){


        $this->required_fields = array_merge($this->required_fields,array('session_id'));

        $carM = new CarSellingModel();

        $data = $this->get_request_data();

        $data['page'] = $data['page'] ? $data['page'] : 1;
        $carM->page = $data['page'];

        $userId = $this->userAuth($data);

        $carM->currentUser = $userId;

        $list = $carM->getUserVisitCar($userId);

        $response = $list;

        $this->send($response);

    }

} 