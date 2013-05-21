	<?php
define('BASE_PATH', realpath(dirname(dirname(__FILE__))));
define('APPLICATION_PATH', BASE_PATH . '/application/default');
define('DS', DIRECTORY_SEPARATOR);
date_default_timezone_set('Europe/Amsterdam');

$local = array('localhost','localhost:8888','productivo.test','productivo.corporate');
$test  = array('webio.ro');

$host = $_SERVER['HTTP_HOST'];



if(in_array($host, $local)) {
	define('APPLICATION_ENV', 'local');

}
elseif(in_array($host, $live)) {
	define('APPLICATION_ENV', 'live');

}
elseif(in_array($host, $test)){
	define('APPLICATION_ENV', 'test');

}
else{
	die('Application is not within configured servers.');
 }

// Include path
set_include_path(
        BASE_PATH . '/library'
        . PATH_SEPARATOR . get_include_path()
);

require_once 'Zend/Session.php';
require_once 'Zend/Application.php';
require_once 'Zend/Session.php';
Zend_Session::start();
Zend_Session::start();
$application = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
$application->bootstrap();
$application->run();
