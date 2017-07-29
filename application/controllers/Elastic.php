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
     
      public function createAction(){

      	      //$elastic = new Elasticsearch\Client();
      	      $hosts = array('127.0.0.1:9200');     
              $client =  Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();
              $searchParams['index'] = 'test';
              $search=$client->search($searchParams);
              print_r($search);
      }
       
      public function testAction(){

      	     $client=new Elasticsearch;
      	     $client=$client->instance();

      	     $search['index']='test';
      	     $list=$client->search($search);
             
      }

      public function indexAction(){
              
      	     $hosts = array('127.0.0.1:9200');     
             $client =  Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();

            // $client = new Elasticsearch\ClientBuilder();
      	     $index['index']='test';
      	     $index['type']='bibi_car';
      	     $index['body']=array(
                    'title' =>'elasticsearch之手哟功能',
                    'content' =>'有关于elasticsearch在php下的扩展使用方法',
                    'create_time' =>date('Y-m-d',time()),

      	     	);
      	     $res=$client->search();
      	     //print_r($res);
      }

}