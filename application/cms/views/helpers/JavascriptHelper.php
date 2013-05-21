<?php
	class Zend_View_Helper_JavascriptHelper extends Zend_View_Helper_Abstract
	{  
	    function javascriptHelper() {
	        $request = Zend_Controller_Front::getInstance()->getRequest();
	        $file_uri = 'js/default/scripts/' . $request->getControllerName() . '/' . $request->getActionName() . '.js';
	 
	        if (file_exists($file_uri)) {
	            $this->view->headScript()->appendFile($this->view->baseUrl($file_uri));
	        }
	    }
	}
?>