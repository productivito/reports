<?php
/**
 * Error controller for PROJECT_NAME
 *
 * Gives you an error message, incase you're working from localhost you also will get
 * the error trace information
 *
 * Made by:     Thomas Bredenbeek
 * Modified by: -
 * Copyright:   MaxWebresults 2011
 *
 */

class ErrorController extends Zend_Controller_Action {

    public function init() {

    }

    public function errorAction() {

        $error = $this->_getparam('error_handler');
        $exception = $error->exception;

        $string = $exception->getMessage() . '' . $exception->getTraceAsString();
        
        switch ($error->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER :
                $this->_response->setHttpResponseCode(404);
                $this->_response->setRawHeader('HTTP/1.1 404 Not Found');
                break;
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

        if ((APPLICATION_ENV) && APPLICATION_ENV == 'local') {
            $this->view->message = $exception->getMessage();
            $this->view->trace = $exception->getTraceAsString();
        }
    }

}