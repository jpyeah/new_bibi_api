<?php


class FocusController extends ApiYafControllerAbstract
{

    /**
     * @api {POST} /v1/focus/create 添加车关注
     * @apiName focus create
     * @apiGroup Focus
     * @apiDescription 添加关注
     * @apiPermission anyone
     * @apiSampleRequest http://new.bibicar.cn
     *
     * @apiParam {string} device_identifier 设备唯一标识
     * @apiParam {string} session_id session_id
     * @apiParam {string} brand_id 品牌id
     *
     * @apiParamExample {json} 请求样例
     *   POST /v1/focus/create
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "brand_id":"",
     *
     *     }
     *   }
     *
     */
    public function createAction(){

        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id', 'brand_id')
        );

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        $FocusModel = new FocusModel();

       $focus = $FocusModel->getFocu($data['brand_id'],$userId);

        if($focus){

            $this->send_error(HAS_EXSIT);
        }

        $properties = array();
        $properties['user_id'] = $userId;
        $properties['brand_id']  = $data['brand_id'];
        $created = time();
        $properties['created_at'] = $created;

        $FocusModel = new FocusModel();
        $FocusModel->properties = $properties;
        $id = $FocusModel->CreateM();

        $brandModel = new BrandModel();

        $response = array();

       // $response['focus_id'] = $id;
        //$response['brand_info']  = $brandModel->getBrandModel($data['brand_id']);
        $this->send($response);

    }

    /**
     * @api {POST} /v1/focus/delete 删除关注
     * @apiName focus delete
     * @apiGroup Focus
     * @apiDescription 删除关注
     * @apiPermission anyone
     * @apiSampleRequest http://new.bibicar.cn
     *
     * @apiParam {string} device_identifier 设备唯一标识
     * @apiParam {string} session_id session_id
     * @apiParam {string} brand_id brand_id
     *
     * @apiParamExample {json} 请求样例
     *   POST /v1/focus/delete
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "brand_id":"",
     *
     *
     *     }
     *   }
     *
     */

    public function deleteAction(){

        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id','brand_id')
        );

        $data = $this->get_request_data();

        $userId=$this->userAuth($data);

        $FocusModel = new FocusModel();

        $FocusModel->deleteFocus($data['brand_id']);

        $response = array();

        $this->send($response);

    }


    /**
     * @api {POST} /v1/focus/list 关注列表
     * @apiName focus list
     * @apiGroup Focus
     * @apiDescription 关注列表
     * @apiPermission anyone
     * @apiSampleRequest http://new.bibicar.cn
     *
     * @apiParam {string} [device_identifier] 设备唯一标识
     * @apiParam {string} [session_id] session_id
     * @apiParam {number} [user_id] 用户id any id
     *
     * @apiParamExample {json} 请求样例
     *   POST /v1/focus/list
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

        $this->required_fields = array_merge(
            $this->required_fields,
            array('session_id')
        );

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

       // $userId = $data['user_id'];

        $FocusModel = new FocusModel();

        $list = $FocusModel->getFocus($userId);
        $this->send($list);
    }




}