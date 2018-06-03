<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/19
 * Time: 上午1:00
 */


use Qiniu\Auth;
//use Qiniu\Storage;


class AppController extends ApiYafControllerAbstract {

    /*
     * @device_id
     * @device_resolution
     * @device_sys_version
     * @device_type
     * @device_identifier
     */
    /**
     * @api {POST} /app/register 注册App(获取device_identifier)
     * @apiName APP getversion
     * @apiGroup APP
     * @apiDescription 注册App
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     *
     * @apiParam {string} device_id 版本号
     * @apiParam {string} device_resolution 版本号
     * @apiParam {string} device_sys_version 版本号
     * @apiParam {number} device_type   1:ios 2:android
     *
     * @apiParamExample {json} 请求样例
     *   POST /app/register
     *   {
     *     "data": {
     *       "device_id":"",
     *       "device_resolution":"",
     *      "device_sys_version":"",
     *       "device_type":"",
     *
     *
     *     }
     *   }
     *
     */
    public function registerAction(){


        $this->required_fields = array('device_id','device_resolution','device_sys_version','device_type');

        $data = $this->get_request_data();

        $data['device_identifier'] = $this->generateIdentifier($data);

        //查找是否有该DEVICE_IDENTIFIER
        $appModel = new \AppModel;

        $result = $appModel->getDevice($data['device_identifier']);

        if(!$result){

            $data['created'] = time();
            $data['updated'] = time();

            //$id = $this->db->insert('bibi_device_info' , $data);
            $id = $appModel->registerDevice($data);

            if(!$id){

                $this->send_error(APP_REGISTER_FAIL , STATUS_FAIL);
            }

        }

        $this->send($data);

    }
    /**
     * @api {POST} /app/getversion 获取最新版本号
     * @apiName APP getversion
     * @apiGroup APP
     * @apiDescription 获取最新版本号
     * @apiPermission anyone
     * @apiSampleRequest http://new.bibicar.cn
     *
     * @apiParam {string} version_code 版本号
     * @apiParam {number} type   1:ios 2:android
     *
     * @apiParamExample {json} 请求样例
     *   POST /app/getversion
     *   {
     *     "data": {
     *       "version_code":"",
     *       "type":"",
     *
     *
     *     }
     *   }
     *
     */
    public function getVersionAction(){

        $this->required_fields = array('type');

        $data = $this->get_request_data();

        $App = new AppModel();

        $res = $App->getAppVersion($data['type']);

        $this->send($res);
    }



    /**
     * @api {POST} /app/getstartimg 获取启动页图片
     * @apiName APP getstartimg
     * @apiGroup APP
     * @apiDescription 获取启动页图片
     * @apiPermission anyone
     * @apiSampleRequest http://new.bibicar.cn
     **
     * @apiParamExample {json} 请求样例
     *   POST /app/getstartimg
     *
     */
    public function getStartImgAction(){

        $App = new AppModel();

        $response['url'] = $App->getStartImg();

        $this->send($response);

    }


    public function generateIdentifier($data){

        $key = $data['device_id'] . $data['device_resolution'] . $data['device_sys_version'] . $data['device_type'];

        $identifier = md5(md5($key));

        return $identifier;

    }
    /**
     * @api {POST} /app/uploadtoken 获取七牛token
     * @apiName APP uploadtoken
     * @apiGroup APP
     * @apiDescription 获取token
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     *
     * @apiParam {string} session_id session标识
     * @apiParam {string} device_identifier   device设备标识
     *
     *
     */
    public function uploadTokenAction(){


        $this->required_fields = array('session_id','device_identifier');

        $data = $this->get_request_data();

        $userId = $this->userAuth($data);

        // $token = 'b2uNBag0oxn1Kh1-3ZaX2I8PUl_o2r19RWerT3yI:7ybP6eSg1UWghOKsdYLFpUfdBWE=:eyJzY29wZSI6ImJpYmkiLCJkZWFkbGluZSI6MTQ0Njc0NzM2OH0=';
        $accessKey = QI_NIU_AK;
        $secretKey = QI_NIU_SK;

        // 构建鉴权对象
        $auth = new Auth($accessKey, $secretKey);

        // 要上传的空间
        $bucket = 'bibi';

        // 生成上传 Token
        //$token = $auth->uploadToken($bucket);

        $expire = 3600;

        $key = 'uploadToken_' . $userId;

        $policy = array(
            'callbackUrl' => 'http://120.25.62.110/index/callback',
            'callbackBody' => '{"fname":"$(fname)", "hash":"$(key)",  "user_id":' . $userId . '}',
           // 'mimeLimit'    => 'image/*'
        );

        $uploadToken = $auth->uploadToken($bucket, null, $expire, $policy);


        $response = array();
        $response['upload_token'] = $uploadToken;

        $this->send($response);


    }



    public function uploadAction(){

        $this->required_fields = array('session_id','device_identifier');

        $data = $this->get_request_data();

        $user_id = $this->userAuth($data);

        // $token = 'b2uNBag0oxn1Kh1-3ZaX2I8PUl_o2r19RWerT3yI:7ybP6eSg1UWghOKsdYLFpUfdBWE=:eyJzY29wZSI6ImJpYmkiLCJkZWFkbGluZSI6MTQ0Njc0NzM2OH0=';
        $accessKey = QI_NIU_AK;
        $secretKey = QI_NIU_SK;

        // 构建鉴权对象
        $auth = new Auth($accessKey, $secretKey);

        // 要上传的空间
        $bucket = 'bibi';

        // 生成上传 Token
        //$token = $auth->uploadToken($bucket);
        $expire = 3600;

        $key = 'uploadToken_' . $user_id;

        $policy = array(
            'callbackUrl' => 'http://120.25.62.110/index/callback',
            'callbackBody' => '{"fname":"$(fname)", "hash":"$(key)",  "user_id":' . $user_id . '}'
        );

        $uploadToken = $auth->uploadToken($bucket, null, $expire, $policy);


        $items = array();

        if($_FILES){

            foreach($_FILES as $k => $file){

                $filePath = $file['tmp_name'];

                // 上传到七牛后保存的文件名
                $key = base64_encode(uniqid('bibi-file'));

                // 初始化 UploadManager 对象并进行文件的上传。
                $uploadMgr = new \Qiniu\Storage\UploadManager();

                list($ret, $err) = $uploadMgr->putFile($uploadToken, $key, $filePath);


                $hash = $ret['data']['hash'];

                if($err != null){

                    $this->send_error(QINIU_UPLOAD_ERROR);
                }

                $items[] = $hash;
            }

            echo implode(',', $items);


        }
        else{

            $this->send_error(QINIU_UPLOAD_ERROR);

        }

    }

    public function ruleAction(){


        header("Location: http://120.25.62.110/protocol.html");

    }
    /**
     * @api {POST} /app/suggest 意见反馈
     * @apiName APP suggest
     * @apiGroup APP
     * @apiDescription 意见反馈
     * @apiPermission anyone
     * @apiSampleRequest http://testapi.bibicar.cn
     *
     * @apiParam {string} session_id  用户session
     * @apiParam {string} device_identifier 设备device
     * @apiParam {string} description  反馈意见
     *
     * @apiParamExample {json} 请求样例
     *   POST /app/suggest
     *   {
     *     "data": {
     *       "description":"",
     *     }
     *   }
     *
     */
    public function SuggestAction(){

        $this->required_fields = array('session_id','device_identifier','description');

        $data = $this->get_request_data();

        $user_id = $this->userAuth($data);

        $Suggest = new SuggestModel();

        $time = time();

        $insert['created_at']=$time;

        $insert['updated_at']=$time;

        $insert['description']=$data['description'];

        $insert['user_id']=$user_id;

        $id = $Suggest->insert('bibi_suggest',$insert);

        $response['id'] = $id;

        $this->send($response);

    }


    /**
     * @api {POST} /app/sendCode 发送验证码
     * @apiName App send_mobile
     * @apiGroup App
     * @apiDescription 发送验证码
     * @apiPermission anyone
     * @apiSampleRequest http://new.bibicar.cn
     * @apiVersion 1.0.0
     *
     * @apiParam {string} [device_identifier] 设备唯一标识
     * @apiParam {string} [mobile] 手机号码
     *
     * @apiParamExample {json} 请求样例
     *   POST /app/sendCode
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "mobile":"",
     *
     *
     *     }
     *   }
     *
     */
    public function sendCodeAction()
    {

        $this->required_fields = array_merge($this->required_fields, array('mobile'));

        $code = rand(1000,9999);

        $data = $this->get_request_data();

        $key = 'code_' . $data['mobile'] . '';

        RedisDb::setValue($key, $code);

        RedisDb::getInstance()->expire($key, 60);

        $response = array(
            'code' => $code
        );

        Common::sendSMS($data['mobile'],array($code),"180149");

        $this->send($response);

    }

    /**
     * @api {POST} /app/pushlist 推送消息列表
     * @apiName App pushlist
     * @apiGroup App
     * @apiDescription 推送消息列表
     * @apiPermission anyone
     * @apiSampleRequest http://new.bibicar.cn
     * @apiVersion 1.0.0
     *
     * @apiParam {string} [device_identifier] 设备唯一标识
     * @apiParam {string} [page] 页码
     *
     * @apiParamExample {json} 请求样例
     *   POST /app/pushlist
     *   {
     *     "data": {
     *       "device_identifier":"",
     *       "page":"",
     *
     *
     *     }
     *   }
     *
     */

    public function pushlistAction(){

        $this->required_fields = array_merge($this->required_fields, array('page'));

        $data = $this->get_request_data();

        $data['page']     = $data['page'] ? ($data['page']+1) : 1;

        $push= new PushModel();

        $list =$push->getPushs($data['page']);

        return $list;
    }


}
