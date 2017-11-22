<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/29
 * Time: 上午1:43
 */

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
class ElasticController extends ApiYafControllerAbstract
{     

      //批量导入elasticsearch
      public function createbybulkAction(){
          $pdoM = new PdoDb;
          $sql = "select * from `bibi_car_selling_list`";
          $lists = $pdoM->query($sql);

          foreach($lists as $k => $val){

              $params['body'][] = [
                  'index' => [
                      '_index' => 'car',
                      '_type' => 'car_selling_list',
                      '_id' => $val['hash'],
                  ]
              ];

              $params['body'][] = [
                  'car_name' => $val['car_name'],
                  'hash'=>$val['hash'],
                  'series_id'=>$val['series_id'],
                  'brand_id'=>$val['brand_id'],
                  'model_id'=>$val['model_id'],
                  'car_type'=>$val['car_type'],
                  'verify_status'=>$val['verify_status'],
              ];
          }

          $client=new Elasticsearch;
          $client=$client->instance();

          $responses = $client->bulk($params);

          print_r($responses);

      }
      
      //批量导入elasticsearch 删除一个库
      public function deleteindexAction(){

          $client=new Elasticsearch;
          $client=$client->instance();

          $params = ['index' => 'car'];
          $response = $client->indices()->delete($params);
      }
      //添加一个库和一个表
      public function createAction(){

      	      //$elastic = new Elasticsearch\Client();
              $client=new Elasticsearch;
              $client=$client->instance();
              $searchParams['index'] = 'car';
              $searchParams['type'] = 'model_detail';
              $searchParams['body'] = array(
//                  'carname' => 'abc',
//                  'hash'=>12334,
//                  'seriesid'=>1,
//                  'brandid'=>1,
//                  'modelid'=>1,
//                  'cartype'=>0,
//                  'verifystatus'=>1
              );

              $response = $client->index($searchParams);

              print_r($response);
      }

      public function searchAllAction(){
          $client=new Elasticsearch;
          $client=$client->instance();
          $searchParams['index'] = 'car'; //索引名称
          $searchParams['type']  = 'car_selling_list';
          $searchParams['body']=array();
          //$searchParams['size'] =10;
          //$searchParams['from'] = 0;
          $list = $client->search($searchParams);
          print_r($list);exit;


      }

    public function getMappingAction(){
        $client=new Elasticsearch;
        $client=$client->instance();
        $params = [
            'index' => 'car',
            'type' => 'car_selling_list'
        ];
        $response = $client->indices()->getMapping($params);
        print_r($response);exit;


    }
    //设置一个表的索引类型
    public function putmappingAction(){

          $client=new Elasticsearch;
          $client=$client->instance();
          $params = [
              'index' => 'car',
              'type' => 'car_selling_list',
              'body' => [
                  'car_selling_list' => [
                      '_source' => [
                          'enabled' => true
                      ],
                      'properties' => [
                          'car_name' => [
                              'type' => 'string',
                              "analyzer"=> "ik_max_word",
                              "search_analyzer"=> "ik_max_word"
                          ],
                      ]
                  ]
              ]
          ];
           // Update the index mapping
          $result = $client->indices()->putMapping($params);

          $this->send($result);

      }
      
      //搜索
      public function searchAction(){
          $client=new Elasticsearch;
          $client=$client->instance();
          $params = [
              'index' => 'car',
              'type' => 'car_selling_list',
              'body' => [
                  'query' => [
                      'match' => [
                          'car_name' => '宝马 7系  领先'
                      ]
                  ],
                  'highlight' =>[
                      "pre_tags" => ["<b>"],
                      "post_tags" => ["</b>"],
                        "fields" => [
                              "car_name" => new \stdClass()
                        ]
                  ]
              ]
          ];
         $params['size'] =100;
          $params['from'] = 0;
          $results = $client->search($params);
          $this->send($results);
      }
      
      //添加一列数据
      public function addAction($val){
          $client=new Elasticsearch;
          $client=$client->instance();
          $index['index'] = 'car'; //索引名称
          $index['type'] = 'car_selling_list'; //类型名称
          $index['id'] = $val['hash'];   //不指定id，系统会自动生成唯一id
          $index['body'] = array(
              'car_name' => $val['car_name'],
              'hash'=>$val['hash'],
              'series_id'=>$val['series_id'],
              'brand_id'=>$val['brand_id'],
              'model_id'=>$val['model_id'],
              'car_type'=>$val['car_type'],
              'verify_status'=>$val['verify_status'],
          );
          $res = $client->index($index);
      }


      public function indexAction(){

          $client=new Elasticsearch;
          $client=$client->instance();

             $index['index']='car';
      	     $index['type']='selling_car_list';
      	     $index['body']=array(

      	     	);
      	     $res=$client->search();
      	     print_r($res);
      }

      public function seriescreateAction(){

          $client=new Elasticsearch;
          $client=$client->instance();
          $searchParams['index'] = 'car';
          $searchParams['type'] = 'car_brand_series';
          $searchParams['body'] = array(

          );

          $response = $client->index($searchParams);

          print_r($response);

      }

      public function seriesputmappingAction(){

          $client=new Elasticsearch;
          $client=$client->instance();
          $params = [
              'index' => 'car',
              'type' => 'car_brand_series',
              'body' => [
                  'car_brand_series' => [
                      '_source' => [
                          'enabled' => true
                      ],
                      'properties' => [
                          'brand_series_name' => [
                              'type' => 'string',
                              "analyzer"=> "ik_max_word",
                              "search_analyzer"=> "ik_max_word"
                          ],
                      ]
                  ]
              ]
          ];
          // Update the index mapping
          $result = $client->indices()->putMapping($params);

          $this->send($result);
      }

      public function seriescreatebybulkAction(){

          $pdoM = new PdoDb;
          $sql = "select t1.*,t2.brand_name from `bibi_car_brand_series` as t1 LEFT JOIN `bibi_car_brand_list` as t2 ON t1.brand_id = t2.brand_id";
          $lists = $pdoM->query($sql);

          foreach($lists as $k => $val){

              $params['body'][] = [
                  'index' => [
                      '_index' => 'car',
                      '_type' => 'car_brand_series',
                      '_id' => $val['brand_series_id'],
                  ]
              ];

              $params['body'][] = [
                  'brand_series_name' => $val['brand_series_name'],
                  'brand_series_id'=>$val['brand_series_id'],
                  'makename'=>$val['makename'],
                  'brand_id'=>$val['brand_id'],
                  'brand_name'=>$val['brand_name'],
              ];
          }

          $client=new Elasticsearch;
          $client=$client->instance();

          $responses = $client->bulk($params);
      }

    //搜索
    public function seriessearchAction(){
        $client=new Elasticsearch;
        $client=$client->instance();
        $params = [
            'index' => 'car',
            'type' => 'car_brand_series',
            'body' => [
                'query' => [
                    'match' => [
                        'brand_series_name' => '宝马 7系 ',
                    ]
                ],
                'highlight' =>[
                    "pre_tags" => ["<b>"],
                    "post_tags" => ["</b>"],
                    "fields" => [
                        "brand_series_name" => new \stdClass()
                    ]
                ]
            ]
        ];
        $params['size'] =100;
        $params['from'] = 0;
        $results = $client->search($params);
        $this->send($results);
    }

}