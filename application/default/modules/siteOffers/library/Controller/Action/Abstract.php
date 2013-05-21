<?php

abstract class SiteOffers_Library_Controller_Action_Abstract extends Custom_Controller_Action_Abstract {

    public function init() {
        
        $this->_initView();
    }

    public function preDispatch() {
        
        //if  its an AJAX request stop here - can be simulated via ?ajax GET parameter sent in the request
        if ($this->_request->isXmlHttpRequest() || isset($_GET['ajax'])) {
            Zend_Controller_Action_HelperBroker::removeHelper('Layout');
        }

        if (!$this->getRequest()->isXmlHttpRequest()) {
            $messages = array();
            $messages['error'] = $this->_helper->FlashMessenger->setNamespace('error')->getMessages();
            $messages['success'] = $this->_helper->FlashMessenger->setNamespace('success')->getMessages();
            $this->view->messages = $messages;
        }

        //Sets the base url to the javascripts of the application
        $script = 'var base_url = "' . $this->view->baseUrl() . '";';
        $this->view->headScript()->prependScript($script, $type = 'text/javascript', $attrs = array());
    }

    protected function _initView() {
        $view = new Custom_Controller_Action_Helper_View($this->view);
        $this->view = $view->init();
    }

}
?>