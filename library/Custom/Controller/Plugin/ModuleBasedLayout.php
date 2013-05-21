<?php

class Custom_Controller_Plugin_ModuleBasedLayout extends Zend_Layout_Controller_Plugin_Layout {

    public function preDispatch(Zend_Controller_Request_Abstract $request) {
        //echo 'koweek';die();
        if($request->getModuleName() != 'default'){
            $this->getLayout()->setLayoutPath(Zend_Registry::get('config')->resources->frontController->moduleDirectory. DS . $request->getModuleName() . DS . 'layouts');
        } else {
            $this->getLayout()->setLayoutPath(APPLICATION_PATH . DS . 'layouts' . DS . 'scripts');
        }
    }

}
?>