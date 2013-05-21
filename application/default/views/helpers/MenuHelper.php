<?php

class Zend_View_Helper_MenuHelper extends Zend_View_Helper_Abstract {

    function menuHelper() {

        $dbMenu = new Application_Table_dbMenu();
        $strCurrentController = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
        $strCurrentAction = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
        $dbMenu->getMenu();
        $arrMenu = $dbMenu->getMenu();


        //echo '<pre>'.print_r($arrMenu,true);
        $intAmountItems = count($arrMenu);
        $intCountLoop = 0;
        foreach($arrMenu as $key => $arrItem){
            $intCountLoop++;
            if($arrItem['controller'] == $strCurrentController && $arrItem['action'] = $strCurrentAction){
                //echo $arrItem['controller'].'<->'.$strCurrentController.'<br />';
               // echo $strCurrentAction.'<->'.$arrItem['action'].'<br /><br />';
                $arrMenu[$key]['class'] = 'menuItemActive ajaxButton';
            } else{
                $arrMenu[$key]['class'] = 'menuItem ajaxButton';
            }
            if($intAmountItems == $intCountLoop){
                $arrMenu[$key]['class'] .= ' menuItemLast';
            }
        }
        //die();
        $this->view->menuItems = $arrMenu;
        return $this->view->render('helper/menuHelper.phtml');
    }

}
?>