<?php

class Application_Object_objPages {
    /* this is pretty much a copy of user model from CMS
      let's only use this for new users and do all updates/manipulations in userSession.php ok?
      TODO: clean up and remove unused functions
     */

    protected $_objDb, $_intLanguageID, $_strCurrentController, $_strCurrentAction;

    public function __construct() {
        $this->_dbPages = new Application_Model_dbPages();
        $this->_intLanguageID = 1;
        $this->_strCurrentController = Zend_Controller_Front::getInstance()->getRequest()->getControllerName(); // Load dynamic
        $this->_strCurrentAction = Zend_Controller_Front::getInstance()->getRequest()->getActionName(); // Load dynamic
    }

    public function getPage() {

        $arrPage = $this->_dbPages->getPageByControllerAction($this->_strCurrentController,$this->_strCurrentAction,$this->_intLanguageID);
        if(empty($arrPage['page_resources_left'])){
            $arrPage['page_resources_left'] = '<h1>(Pagina niet gevonden)</h1>';
        }
        if(empty($arrPage['page_resources_right'])){
            $arrPage['page_resources_right'] = '<h1>(Pagina niet gevonden)</h1>';
        }
        return $arrPage;
    }

}
?>