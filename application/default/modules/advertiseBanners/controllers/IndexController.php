<?php

class AdvertiseBanners_IndexController extends AdvertiseBanners_Library_Controller_Action_Abstract {

    public function init() {
        $this->_dbAdvertise = new AdvertiseBanners_Model_dbAdvertise();
    }

    public function indexAction() {
        $arrConfig = $this->view->advertiseBanners_getConfig;
        if(!empty($arrConfig['imgHeight']) && !empty($arrConfig['imgWidth'])){
            $this->view->randomAdvertisement = $this->_dbAdvertise->getRandomAdvertisement($arrConfig['imgWidth'],$arrConfig['imgHeight']);
        }
    }

}
?>