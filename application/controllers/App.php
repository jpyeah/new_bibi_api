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
    public function registerAction(){


        $this->required_fields = array('device_id','device_resolution','device_sys_version','device_type');

        $data = $this->get_request_data();

        unset($data['app/register']);

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
     * @apiSampleRequest http://testapi.bibicar.cn
     *
     * @apiParam {string} version_code 版本号
     * @apiParam {number} type   1:ios 2:android
     *
     * @apiParamExample {json} 请求样例
     *   POST /v3/Video/list
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


        $this->required_fields = array('type','version_code');

        $data = $this->get_request_data();

        $App = new AppModel();
        $res = $App->getAppVersion($data['version_code'],$data['type']);

        $this->send($res);
    }

    public function getimgAction(){

        //$response['url']=['http://img.bibicar.cn/bling.png','http://img.bibicar.cn/qiuyue.png','http://img.bibicar.cn/yub.png'];http://img.bibicar.cn/chezhuzhaoweijia.jpeg
       // $response['url']=['http://img.bibicar.cn/bibichepaidang.jpg','http://img.bibicar.cn/chezhuzhaoweijia.jpeg','http://img.bibicar.cn/chezhustory002.jpeg','http://img.bibicar.cn/chezhustory003.jpeg'];
        $response['url']=['http://img.bibicar.cn/bibichepaidang.jpg','http://img.bibicar.cn/bibichepaidang1.jpg','http://img.bibicar.cn/bibichepaidang2.jpg'];
        $this->send($response);
         
    }

    public function generateIdentifier($data){

        $key = $data['device_id'] . $data['device_resolution'] . $data['device_sys_version'] . $data['device_type'];

        $identifier = md5(md5($key));

        return $identifier;

    }

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
            'callbackBody' => '{"fname":"$(fname)", "hash":"$(key)",  "user_id":' . $userId . '}'
        );

        $uploadToken = $auth->uploadToken($bucket, null, $expire, $policy);

//        $uploadToken = RedisDb::getValue($key);
//
//        if(!$uploadToken){
//
//            $policy = array(
//                'callbackUrl' => 'http://120.25.62.110/index/callback',
//                'callbackBody' => '{"fname":"$(fname)", "hash":"$(key)",  "user_id":' . $userId . '}'
//            );
//
//            $uploadToken = $auth->uploadToken($bucket, null, $expire, $policy);
//            RedisDb::setValue($key , $uploadToken);
//            RedisDb::getInstance()->expire($key,$expire);
//
//        }


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


}
