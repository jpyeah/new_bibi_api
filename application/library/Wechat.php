<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/18
 * Time: 下午11:40
 */

use EasyWeChat\Foundation\Application;

class Wechat
{

      public function getWechat(){

          $options = [
              'debug' => true,
              'app_id' => "wx8bac6dd603d47d15",
              'secret' => "dcc4740804b9c5b19686cb4b1fd5eb8e",
              'log'=>
                  [
                      'level' => "debug",
                      'permission' => 0777,
                      'file' => "/tmp/easywechat.log",
                  ],

              'payment' => [

                  'merchant_id'        => '1424297802',
                  'key'                => 'dcc4740804b9c5b19686cb4b1fd5eb8e',
                  'cert_path'          => '/usr/local/nginx/html/cert/apiclient_cert.pem',
                  'key_path'           => '/usr/local/nginx/html/cert/apiclient_key.pem',

              ],
          ];
          $app = new Application($options);

          return $app;

      }


}