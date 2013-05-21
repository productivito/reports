<?php

class GoogleMap_IndexController extends GoogleMap_Library_Controller_Action_Abstract {

    protected $_apiGoogle;

    public function init() {

        $this->_apiGoogle = new GoogleMap_Model_googleApi(false);
}

public function indexAction() {
//echo '<pre>' . print_r($this->_apiGoogle->getLocation(),true);die();


    /*navigate*/
        if(!empty($this->view->arrRoute)){
            $this->_apiGoogle->_arrConfig = $this->view->arrRoute;
        }
        $arrRoute = $this->_apiGoogle->getLocation();
        $this->view->apiKey = $this->_apiGoogle->getApiKey();
        $this->view->viewDist = 40;
        $this->view->orginAddr = $arrRoute['formatted_address'];
        $this->view->startLat = $arrRoute['geometry']['location']['lat'];
        $this->view->startLng = $arrRoute['geometry']['location']['lng'];

    }

}
?>