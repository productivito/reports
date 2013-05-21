<?php

abstract class Login_Library_Controller_Action_Abstract extends Custom_Controller_Action_Abstract {

    public function init() {
        
        $this->_initView();
    }

    /**
     * Before dispatching the requested controller/action
     * check to see if teh request is an AJAX request (via XMLHTTPREQUEST or $_GET['ajax']
     *
     * If it is an ajax request, remove the layout
     *
     * If it is not, setup the FlashMessenger
     */
    public function preDispatch() {
        
        //echo '<pre>'.print_r($_POST,true);die();
        //if  its an AJAX request stop here - can be simulated via ?ajax GET parameter sent in the request
        if ($this->_request->isXmlHttpRequest() || isset($_POST['ajax']) || isset($_GET['ajax'])) {
            //echo '<pre>'.print_r($_POST,true);
            Zend_Controller_Action_HelperBroker::removeHelper('Layout');
        }
        //echo 'OK';die();
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $messages = array();
            $messages['error'] = $this->_helper->FlashMessenger->setNamespace('error')->getMessages();
            $messages['success'] = $this->_helper->FlashMessenger->setNamespace('success')->getMessages();
            $this->view->messages = $messages;
        }

        //Sets the base url to the javascripts of the application
        $script = '
			var base_url = "' . $this->view->baseUrl() . '";
		';
        $this->view->headScript()->prependScript($script, $type = 'text/javascript', $attrs = array());
    }

    protected function _initView() {
        $view = new Custom_Controller_Action_Helper_View($this->view);
        $this->view = $view->init();
    }

}
?>