<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 16/1/2
 * Time: 下午6:41
 */

class UsercarpactController extends ApiYafControllerAbstract {


    //     session_id=session5650660854db1&device_identifier=de762bd50f3e985476cb1fcfdd8886ab
    //买家 session_id=session58cb4211e4b2b&device_identifier=df4871c207120a5d73407318477b97b2  user_id = 544;
    //卖家 device_identifier=1d7c030c120f467e58e832cde18a4f4a&session_id=session58df1710ca231  user_id = 389;
    /**
     * @api {POST} /v3/UserCarPact/checkstatus 查看是否有预约
     * @apiName UserCarPact checkstatus
     * @apiGroup UserCarPact
     * @apiDescription
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     *
     * @apiParam {string} device_identifier device_identifier
     * @apiParam {string} session_id session_id
     * @apiParam {string} car_id  车辆id
     *
     * @apiParamExample {json} 请求样例
     *   POST /v3/UserCarPact/pactcreate
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "session_id":"",
     *       "car_id":"",
     *
     *     }
     *   }
     *
     * @apiSuccess {json} pact_info 预约详情.
     * @apiSuccess {json} user_info 当前用户信息
     * @apiSuccess {json} seller_info 当前车主信息
     * @apiSuccess {json} car_info 车辆信息
     * @apiSuccess {string} description 当有只返回car_info时,说明当前用户还没有预约当前车辆。
     * @apiSuccess {string} pact_info.id pact_id 预约Id
     * @apiSuccess {string} pact_info.buyer_id buyer_id 预约用户Id
     * @apiSuccess {string} pact_info.seller_id seller_id 预约车主Id
     * @apiSuccess {string} pact_info.status 状态  0:买家点击预约 1:买家付款失败 2 买家付款成功 3:卖家已确认 4:双方履约(订单完成) 5:双方履约失败,6:客服(介入)(订单完成)
     *
     *
     */


    public function RechargeAction(){

        $this->required_fields = array_merge($this->required_fields,array('session_id'));

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        if($result){

            $response=$UserCarPact->getPactInfo($result['id']);

            $this->send($response);
        }else{

            $CS= new CarSellingModel();

            $CarInfo = $CS->GetCarInfoById($data['car_id']);

            $response['car_info'] = $CarInfo;

            $this->send($response);
        }



    }


}