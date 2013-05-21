<?php
Class GoogleMap_ErrorController extends GoogleMap_Library_Controller_Action_Abstract
{
	public function errorAction()
    {
		$error = $this->_getParam('error_handler');
        switch ($error->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = 'Uh oh, we cant seem to find that page you wanted!';
                $this->view->stack_trace = $this->_getFullErrorMessage($error);
				$this->view->code = 404;
                break;

            default:
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = 'Looks like somethings gone wrong! Please refresh the page - if the problem persists please report the error';
                $this->view->stack_trace = $this->_getFullErrorMessage($error);
				$this->view->code = 500;
                break;
        }

        $this->view->headTitle()->prepend( $this->view->code .  ' Error' );
    }

	protected function _getFullErrorMessage($error)
    {
		if (APPLICATION_ENV != 'development')
		{
			return '';
		}

        $message = '';

        if (!empty($_SERVER['SERVER_ADDR'])) {
            $message .= "Server IP: " . $_SERVER['SERVER_ADDR'] . "n";
        }

        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $message .= "User agent: " . $_SERVER['HTTP_USER_AGENT'] . "n";
        }

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $message .= "Request type: " . $_SERVER['HTTP_X_REQUESTED_WITH'] . "n";
        }

        $message .= "Server time: " . date("Y-m-d H:i:s") . "n";
        $message .= "RequestURI: " . $error->request->getRequestUri() . "n";

        if (!empty($_SERVER['HTTP_REFERER'])) {
            $message .= "Referer: " . $_SERVER['HTTP_REFERER'] . "n";
        }

        $message .= "Message: " . $error->exception->getMessage() . "nn";
        $message .= "Trace:n" . $error->exception->getTraceAsString() . "nn";
        $message .= "Request data: " . var_export($error->request->getParams(), true) . "nn";

        $it = $_SESSION;

        $message .= "Session data:nn";
        foreach ($it as $key => $value) {
            $message .= $key . ": " . var_export($value, true) . "n";
        }
        $message .= "n";

        $message .= "Cookie data:nn";
        foreach ($_COOKIES as $key => $value) {
            $message .= $key . ": " . var_export($value, true) . "n";
        }
        $message .= "n";

        return '< pre>' . $message . '< / pre>';
    }
}
?>