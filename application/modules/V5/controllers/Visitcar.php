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
 * @api {POST} /v5/Visitcar/list 浏览过的车
 * @apiName car visit list
 * @apiGroup Car
 * @apiDescription 浏览过的车
 * @apiPermission anyone
 * @apiSampleRequest http://testapi.bibicar.cn
 * @apiVersion 2.6.0
 *
 * @apiParam {string} device_identifier 设备唯一标识
 * @apiParam {string} session_id session_id
 * @apiParam {string} page session_id
 *
 * @apiParamExample {json} 请求样例
 *   POST /v5/Visitcar/list
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


        $this->required_fields = array_merge($this->required_fields,array('session_id','page'));

        $carM = new CarSellingV5Model();

        $data = $this->get_request_data();

        $data['page'] = $data['page'] ? $data['page'] : 1;
        $carM->page = $data['page'];

        $userId = $this->userAuth($data);

        $carM->currentUser = $userId;

        $list = $carM->getUserVisitCars($userId);

        $response = $list;

        $this->send($response);

    }

} 