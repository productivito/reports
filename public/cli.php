<?php

define('BASE_PATH', realpath(dirname(dirname(__FILE__))));
define('APPLICATION_PATH', BASE_PATH . '/application/default');
define('DS', DIRECTORY_SEPARATOR);
date_default_timezone_set('Europe/Amsterdam');

$local = array('localhost','localhost:8888','Productivito.test','Productivito.corporate');
$test  = array('webio.ro');

define('APPLICATION_ENV', 'test');

// Include path
set_include_path(
        BASE_PATH . '/library'
        . PATH_SEPARATOR . get_include_path()
);

require_once 'Zend/Session.php';
require_once 'Zend/Application.php';
require_once 'Zend/Session.php';
Zend_Session::start();
$application = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
$application->bootstrap();

$report = new Application_Table_dbCategory();

while (true) {
	
	//print_r($report->getAllCategories());
	// 
    print_r($report->getAllCategories());
	sleep(1);
	
	// Zend_Db_Profile
	// La generarea raportului, rezultate intermediare sunt aduse impreuna
	// 
}
