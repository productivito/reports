<?php
class ErrorController extends Zend_Controller_Action{
	public function errorAction()
	{
		// retrieve the handle to access the error
		$error		= $this->_getparam('error_handler');
		$exception	= $error->exception;
		
		// decide what to do depending on what kind of error occured
		switch( $error->type ) {
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER :
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION :

				$this->_response->setHttpResponseCode(404);
				$this->_response->setRawHeader('HTTP/1.1 404 Not Found');
				break;
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_OTHER :
			default :
				$this->_response->setHttpResponseCode(500);
				$this->_response->setRawHeader('HTTP/1.1 500 Internal Server Error');
				break;
		}
		
		// hand off the information to the view
		$this->view->message	= $exception->getMessage();
		$this->view->trace		= $exception->getTraceAsString();
	}
}