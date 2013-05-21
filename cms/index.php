<?php
$local = array('localhost','localhost:8888');
$development     = array('moonglow');
$test  = array('cms.aon.loyaltyprofs.nl');
$live  = 'aon.nl';
$host = $_SERVER['HTTP_HOST'];
$host_path = $_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];

if(in_array($host, $development)) {
	define('APPLICATION_ENV', 'development');
	define('UPLOAD_PATH', realpath('').'/upload/');
}
elseif(in_array($host, $local)) {
	define('APPLICATION_ENV', 'local');
	define('UPLOAD_PATH','upload/');
}
elseif(strstr($host_path, $test[0])||strstr($host_path, $test[1])){
	define('APPLICATION_ENV', 'test');
	define('UPLOAD_PATH',  realpath(dirname(__FILE__)).'/upload/');
}
elseif(strstr($host_path, $live)) {
	define('APPLICATION_ENV', 'live');
	define('UPLOAD_PATH',  realpath(dirname(__FILE__)).'/upload/');
}
else{
	die('Application is not within configured servers.');
}

define('BASE_PATH', realpath(dirname(__FILE__) . '/../'));
define('APPLICATION_PATH', BASE_PATH . '/application/cms');
define('DS', DIRECTORY_SEPARATOR);
date_default_timezone_set('Europe/Amsterdam');

// Include path
set_include_path(
        BASE_PATH . '/library'
        . PATH_SEPARATOR . get_include_path()
);
// Zend_Application
require_once 'Zend/Application.php';

$application = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
$application->bootstrap();
$application->run();

?>