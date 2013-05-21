<?php

class Zend_View_Helper_MenuHelper extends Zend_View_Helper_Abstract {

    function menuHelper() {

        $currentController = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
        $sMenu = '';
        $this->permissions = new Application_Model_permissions();

        $auth = Zend_Auth::getInstance();

        if ($auth->hasIdentity()) {
            $menuItems = array();

            $menuItems[] = array('controller' => 'index',
                'view' => 'index',
                'name' => 'Home');

            $menuItems[] = array('controller' => 'offers',
                'view' => 'offers',
                'name' => 'Aanbiedingen');

            $menuItems[] = array('controller' => 'pages',
                'view' => 'pages',
                'name' => 'Pages');


            foreach ($menuItems as $key => $item) {
                if ($item['controller'] == $currentController) {
                    $menuItems[$key]['class'] = 'active';
                } else {
                    $menuItems[$key]['class'] = 'inactive';
                }
            }
            $sMenu = '<ul class="MainMenu">';
            foreach ($menuItems as $menuItem) {
                $sMenu .='<li class="' . $menuItem['class'] . '">
					<a href="' . $this->view->baseUrl($menuItem['controller']) . '">
						' . $menuItem['name'] . '
					</a>
				</li>';
            }
            $sMenu .='</ul>';
        }
        return $sMenu;
    }

}
