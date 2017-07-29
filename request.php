<?php
define('APPLICATION_PATH', dirname(__FILE__));

define('APPPATH', APPLICATION_PATH . "/application");
define('EXTPATH' , APPPATH . "/library/");

$application = new Yaf_Application( APPLICATION_PATH . "/conf/application.ini");

$application->getDispatcher()->dispatch(new Yaf_Request_Simple("CLI", "", "Test", "rest", array("para" => 2)));

?>