<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    protected function _initView() {

        // Initialize view
        $view = new Zend_View();
        $view->doctype('XHTML1_TRANSITIONAL');
        $view->setEncoding('UTF-8');
        //$view->headMeta()->appendHttpEquiv('Content-Type', 'text/html;charset=utf-8');


        $view->headTitle('AON');
        $view->env = APPLICATION_ENV;

        // Add it to the ViewRenderer
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper(
                        'ViewRenderer'
        );


        $viewRenderer->setView($view);
	
        // Return it, so that it can be stored by the bootstrap
        return $view;
    }
    

 protected function _initDb() {

        // Get config fron applciation.ini
        $this->config = new Zend_Config_Ini(APPLICATION_PATH
                        . '/configs/application.ini',
                        APPLICATION_ENV);
       
        $zendConfig = $this->config->toArray();

        Zend_Registry::set('config',$zendConfig);
        // connect to site database
        $db = Zend_Db::factory($zendConfig['resources']['db']['adapter'], $zendConfig['resources']['db']['params']['aon']);
        Zend_Db_Table::setDefaultAdapter($db);
        Zend_Registry::set('db', $db);


		

    }
 
 public function _initCoreSession(){
 	
     $this->bootstrap('db');
     $this->bootstrap('session');
     Zend_Session::start();
 }  

}