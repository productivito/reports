<?php

class Zend_View_Helper_PageResourceHelper extends Zend_View_Helper_Abstract {

    function pageResourceHelper($strType = '',$strVal = '') {

        $objPages = new Application_Model_objPages();
        $strCurrentController = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();

        /* Settings */
        $this->view->leftBlockHeight = 302;
        $this->view->leftBlockWidth = 394;
        $this->view->leftBlockPaddingTop = 18;

        $this->view->rightBlockHeight = 302;
        $this->view->rightBlockWidth = 242;
        $this->view->rightBlockPaddingTop = 18;

        $this->view->leftBlockContent = array();
        $this->view->rightBlockContent = array();

        
        $arrPageResource = $objPages->getPage($strType,$strVal);

        if (!empty($arrPageResource['single'])) {
            /* We have a single page */
        }
        if (!empty($arrPageResource['left']) && !empty($arrPageResource['right'])) {

            if (!empty($arrPageResource['left']['widget'])) {
                $this->view->leftBlockContent[]['page_resources_text'] = $this->view->action('index', 'index', $arrPageResource['left']['widget']);
            } else {
                if (count($arrPageResource['left']['page']) > 1) {
                    $this->view->leftBlockHeight = (($this->view->leftBlockHeight + $this->view->leftBlockPaddingTop) / 2) - $this->view->leftBlockPaddingTop;
                }
                $this->view->leftBlockContent = $arrPageResource['left']['page'];
            }
            if (!empty($arrPageResource['right']['widget'])) {

                $this->view->rightBlockContent[]['page_resources_text'] = $this->view->action('index', 'index', $arrPageResource['right']['widget']);
            } else {
                if (count($arrPageResource['right']['page']) > 1) {
                    $this->view->rightBlockHeight = (($this->view->rightBlockHeight + $this->view->rightBlockPaddingTop) / 2) - $this->view->rightBlockPaddingTop;
                }
                $this->view->rightBlockContent = $arrPageResource['right']['page'];
            }

            $htmlContent = $this->view->render('helper/doublePageHelper.phtml');
        }

        return @$htmlContent;
    }
    

}
?>