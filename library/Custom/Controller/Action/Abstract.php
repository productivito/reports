<?php

abstract class Custom_Controller_Action_Abstract extends Zend_Controller_Action {

    /**
     * Helper method to redirect to a specific action or controller from a
     * specific module, via a specified route(or not) with specified parameters
     *
     * @param string $controller / $url which contains http in its composition
     * @param string $action
     * @param string $module
     * @param array  $params
     * @param string $route
     * @param boolean $reset
     */
    public function redirect($controller = 'index', $action = 'index', $module = 'frontoffice', $params = array(), $route = null, $reset = true) {
        $this->_redirect = $this->_helper->getHelper('Redirector');
      
        $current_controller = $this->_getParam('controller');
        $current_action = $this->_getParam('action');
        $current_module = $this->_getParam('module');

        if ($current_controller == $controller &&
                $current_action == $action &&
                $current_module == $module) {
            return TRUE;
        }

        if (strstr($controller, 'http')) {
            return $this->_redirect($controller, array('code' => 301));
        }

        if ($route !== null) {
            $params = array_merge(array('action' => $action,
                        'controller' => $controller,
                        'module' => null), $params);

            return $this->_redirect->setCode(301)
                    ->setExit(true)
                    ->gotoRoute($params, $route, $reset);
        }

        return $this->_redirect->setCode(301)
                ->setExit(true)
                ->gotoSimpleAndExit($action,
                        $controller,
                        $module,
                        $params);
    }

}
?>