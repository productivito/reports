<?php

class SiteOffers_Model_mdlOffers {

    protected $_arrConfig;

    public function __construct() {

        $this->_objDb = Zend_Registry::get('db');
        $this->_objSoap = new SiteOffers_Object_objSoap();

        $this->_dbCategories = new SiteOffers_Table_dbCategories();
        $this->_dbProducts = new SiteOffers_Table_dbProducts();
        $this->_dbTickets = new SiteOffers_Table_dbTickets();


        $this->_mdlProducts = new SiteOffers_Model_mdlProducts();
    }

    private function updateAllOffers() {
        $arrXml = $this->_objSoap->getXmlInArray('getProducts');
        if (!empty($arrXml)) {
            $this->_mdlProducts->processProductsFromXml($arrXml);
        }
    }

    public function getAllProducts() {
        $this->updateAllOffers();
        $arrResult = array();
        $arrCategories = $this->_dbCategories->getAllCategories();
        //echo '<pre>'.print_r($arrResult,true);die();
        foreach ($arrCategories as $intKeyCat => $arrCategory) {
            $arrResult[$intKeyCat] = $arrCategory;
            //echo '<pre>'.print_r($arrResult,true);die();
            $arrResult[$intKeyCat]['products'] = $this->_dbProducts->getProductsByCategory($arrCategory['category_ID']);
            foreach ($arrResult[$intKeyCat]['products'] as $intKeyProduct => $arrProduct) {
                $arrResult[$intKeyCat]['products'][$intKeyProduct]['tickets'] = $this->_dbTickets->getTicketsByProduct($arrProduct['product_ID']);
            }
        }
        return $arrResult;
    }

    public function getPopulairOffers() {

        return $this->_dbTickets->getPopulairOffers();
    }

    public function getOffer($intId = false) {
        $arrResult = array();
        if (!empty($intId)) {
            $arrResult = $this->_dbProducts->getProductByID($intId);
            if (!empty($arrResult)) {
                $arrResult['tickets'] = $this->_dbTickets->getTicketsByProduct($arrResult['product_ID']);
            }
        }
        return $arrResult;
    }

    public function getCheckoutOffers($arrParams = false) {
        $arrOrders = array();
        $intCountOders = 0;
        if (!empty($arrParams)) {
            foreach ($arrParams as $strKeyParam => $strParamValue) {
                if (stristr($strKeyParam, 'amount_') != FALSE) {
                    $arrOrders[str_replace('amount_','',$strKeyParam)] = $strParamValue;
                    $intCountOders += $strParamValue;
                }
            }
            if(!empty($arrOrders) && $intCountOders !=0){
                return $arrOrders;
            }
        }
        return false;
        
    }

}

?>
