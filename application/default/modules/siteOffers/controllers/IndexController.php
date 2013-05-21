<?php

class SiteOffers_IndexController extends GoogleMap_Library_Controller_Action_Abstract {

    protected $_objExampleModel;

    public function init() {
        $this->_mdlOffers = new SiteOffers_Model_mdlOffers();
        $this->_dbProducts = new SiteOffers_Table_dbProducts();
        $this->_arrSoap = new SiteOffers_Object_arrSoap();
        $this->_objSoap = new SiteOffers_Object_objSoap();
    }

    public function indexAction() {
        $this->view->moduleTopOffers = $this->_mdlOffers->getPopulairOffers();
    }

    public function populairoffersAction() {
        $this->view->modulePopulairOffers = $this->_mdlOffers->getPopulairOffers();
    }

    public function offerAction() {
        $arrParams = $this->view->toModule_Params;
        $this->view->moduleShowOffer = $this->_mdlOffers->getOffer($arrParams['id']);
        $arrOffer = $this->_mdlOffers->getOffer($arrParams['id']);
        $this->view->viewOfferForm = new SiteOffers_Form_frmSelectoffers($arrOffer['tickets'], $this->view, array('action' => $this->view->baseUrl('index/checkout')));
    }

    public function checkoutAction() {

        $arrParams = $this->view->toModule_Params;
        $arrOffers = $this->_mdlOffers->getCheckoutOffers($arrParams);
        if (!empty($arrOffers)) {
            $this->view->viewCheckoutForm = new SiteOffers_Form_frmCheckout($arrOffers, array('action' => $this->view->baseUrl('index/checkout')));
            if (!empty($_POST) && isset($_POST['order_submit']) && $this->view->viewCheckoutForm->isValid($_POST)) {

                $arrOffers = $this->_mdlOffers->getCheckoutOffers($arrParams);

                $arrCheckoutArray = $this->_arrSoap->getCheckoutArray()
                                ->getProductsArray()
                                ->setClientIDForSoap('loyaltyprofs')
                                ->addressArrayForSoap($arrParams)
                                ->productsArrayForSoap($arrOffers)
                                ->setShipmentForSoap();

                $arrResult = $this->_objSoap->soapCall($arrCheckoutArray->_arrSoap);

                if($arrResult['result'] == 1){
                    $this->redirect('index','payment','default',$arrResult);
                } else {
                    //echo '<pre>'.print_r($arrResult,true);die();
                    $this->view->formErrors = 1;
                    $this->view->formErrorMsg = 'Er ging iets fout. Probeert u het nog een keer of neem contact op met countdown.';
                }
            } else {
                $this->view->formErrors = 1;
                $this->view->viewCheckoutForm->populate($_POST);
            }
        } else {
            if (empty($arrParams['product_ID'])) {
                $this->_redirect($this->view->baseUrl());
            } else {
                $this->_redirect('index/offer/id/' . $arrParams['product_ID']);
            }
        }
    }

    public function paymentAction() {
        $arrParams = $this->_getAllParams();
        $this->view->checkoutID = $arrParams['msg'];
        
    }

    public function contactAction() {
        $arrParams = $this->view->toModule_Params;
        $this->view->moduleContactDetails = $this->_dbProducts->getProductByID($arrParams['id']);
    }

    public function categoriesoffersAction() {
        $arrParams = $this->view->toModule_Params;
        $this->view->moduleCategoriesAndOffers = $this->_mdlOffers->getAllProducts();
    }

}

?>