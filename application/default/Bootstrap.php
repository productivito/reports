<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
    #stores a copy of the config object in the Registry for future references
    #!IMPORTANT: Must be runed before any other inits

    protected function _initConfig() {
        Zend_Registry::set('config', new Zend_Config($this->getOptions()));
        $this->bootstrap('frontController');
		
		ini_set('memory_limit', '-1');
    }

    protected function _initAutoload() {
        $autoLoaderResource = new Zend_Loader_Autoloader_Resource(
                        array(
                            'basePath' => APPLICATION_PATH,
                            'namespace' => 'Application'
                        )
        );
         Zend_Loader_Autoloader::getInstance()->registerNamespace('PHPExcel');
        // The name of the Class. Firstname is the Application_[yourname]_[filename] without capitals, the socond the directory name -> application/yourname/filename and
        // the third the same as the first but only with first letter capitalised. This is the one you call like Application_Yourname_Filename
        $autoLoaderResource->addResourceType('object', 'objects', 'Object');
        $autoLoaderResource->addResourceType('table', 'tables', 'Table');
        Zend_Loader_Autoloader::getInstance()->pushAutoloader($autoLoaderResource);
    }

    protected function _initView() {


        //$this->_appNamespace('Application_Objects_');
        // Initialize view
        $view = new Zend_View();
        $view->doctype('XHTML1_TRANSITIONAL');
        $view->setEncoding('UTF-8');
        // Set title
        $view->headTitle('Ticketshop');
        $view->env = APPLICATION_ENV;

        // Add it to the ViewRenderer
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper(
                        'ViewRenderer'
        );

        $viewRenderer->setView($view);
        return $view;
    }

    protected function _initDb() {
        // Get config fron applciation.ini
        $this->config = new Zend_Config_Ini(APPLICATION_PATH
                        . '/configs/application.ini',
                        APPLICATION_ENV);

        $zendConfig = $this->config->toArray();
        // connect to site database
        $db = Zend_Db::factory($zendConfig['resources']['db']['adapter'], $zendConfig['resources']['db']['params']);
        Zend_Db_Table::setDefaultAdapter($db);
        Zend_Registry::set('db', $db);
    }
/*
    public function _initCoreSession() {
        $this->bootstrap('db');
        $this->bootstrap('session');
        Zend_Session::start();
    }*/

    protected function _initMail() {
        $tr = new Zend_Mail_Transport_Smtp("localhost", array('port' => 2525));
        Zend_Mail::setDefaultTransport($tr);
    }

}