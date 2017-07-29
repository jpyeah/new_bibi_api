<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/24
 * Time: 下午11:29
 */

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
class Elasticsearch {
   /*
    public static function endpoints(){

        if(is_null(self::$instance)){
            $master = Yaf_Registry::get('config')->Elasticsearch;
            self::$config = array();
            self::$config['host']   = $master->host;
            self::$config['port']   = $master->port;
           
            self::$instance  =  Elasticsearch\ClientBuilder::create()->setHosts(self::$config)->build();
        }

        return self::$endpoints;
    }
    */
    public  function instance()
    {
       
        
           $master = Yaf_Registry::get('config')->elasticsearch;
           $config = array();
           $config['host']   = $master->host;
           $config['port']   = $master->port;
           $elasticsearch =  Elasticsearch\ClientBuilder::create()->setHosts($config)->build();
        
           return $elasticsearch;
    }


   
} 