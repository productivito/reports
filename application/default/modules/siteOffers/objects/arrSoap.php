<?php

class SiteOffers_Object_arrSoap {

    protected $_arrReturn = array();
    protected $_arrProducts = array();
    protected $_intFirstProduct = 0;
    protected $_intLastProduct = 9;
    protected $_arrSoap = array();

    public function __construct() {

    }

    public function getCheckoutArray() {

        $this->_arrReturn = array(
            'Sex' => array('formField' => 'order_gender','type' => 'select', 'elements' => array('multiOptions' => array('M' =>'Man','F' =>'Vrouw'), 'label' => 'Geslacht *','required' => true)),
            'FirstName' => array('formField' => 'order_firstname','type' => 'text', 'elements' => array('value' => '','label' => 'Voornaam *','required' => true,'validators' => array(array('validator' => 'stringLength','options' => array(2, 200))))),
            'MiddleName' => array('formField' => 'order_initials','type' => 'text', 'elements' => array('value' => '', 'label' => 'Tussenvoegsel')),
            'LastName' => array('formField' => 'order_surname','type' => 'text', 'elements' => array('value' => '', 'label' => 'Achternaam *','required' => true,'validators' => array(array('validator' => 'stringLength','options' => array(2, 200))))),
            'Street' => array('formField' => 'order_address_street','type' => 'text', 'elements' => array('value' => '', 'label' => 'Straat *','required' => true,'validators' => array(array('validator' => 'stringLength','options' => array(2, 200))))),
            'AddressNr' => array('formField' => 'order_address_number','type' => 'text', 'elements' => array('value' => '', 'label' => 'Huisnummer *','required' => true,'validators' => array(array('validator' => 'stringLength','options' => array(1, 200))))),
            'AddressExtra' => array('formField' => 'order_address_extra','type' => 'text', 'elements' => array('value' => '', 'label' => 'Huisnr extra')),
            'PostalCode' => array('formField' => 'order_address_zip','type' => 'text', 'elements' => array('value' => '', 'label' => 'Postcode *','required' => true,'validators' => array(array('validator' => 'stringLength','options' => array(6, 10))))),
            'City' => array('formField' => 'order_address_city','type' => 'text', 'elements' => array('value' => '', 'label' => 'Plaats *','required' => true,'validators' => array(array('validator' => 'stringLength','options' => array(1, 200))))),
            'Country' => array('formField' => 'order_address_country','type' => 'select', 'elements' => array('multiOptions' => array('NL' =>'Nederland'), 'label' => 'Land *','required' => true,'validators' => array(array('validator' => 'stringLength','options' => array(1, 200))))),
            'Phone' => array('formField' => 'order_address_phone','type' => 'text', 'elements' => array('value' => '', 'label' => 'Telefoon *','required' => true,'validators' => array(array('validator' => 'stringLength','options' => array(7, 15))))),
            'Email' => array('formField' => 'order_address_email','type' => 'text', 'elements' => array('value' => '', 'label' => 'E-Mail *','required' => true,'validators' => array(array('validator' => 'EmailAddress')))));
        return $this;
    }

    public function getProductsArray() {

        for ($i = $this->_intFirstProduct; $i <= $this->_intLastProduct; $i++) {
            $this->_arrProducts['Product_code' . $i] = '';
            $this->_arrProducts['Quantity' . $i] = '';
            $this->_arrProducts['Dated' . $i] = '';
            $this->_arrProducts['DatedDate' . $i] = '';
            $this->_arrProducts['DatedFrom' . $i] = '';
            $this->_arrProducts['DatedLast' . $i] = '';
        }
        return $this;
    }

    public function populateCheckoutArray($arrValues = false) {
        if (!empty($arrValues)) {
            //echo '<pre>'.print_r($this->_arrReturn,true);die();
            foreach ($this->_arrReturn as $strFieldKey => $arrFields) {
                //echo '<pre>'.print_r($arrFields,true);die();
                if (!empty($arrValues[$arrFields['formField']])) {
                    $this->_arrReturn[$strFieldKey]['elements']['value'] = $arrValues[$arrFields['formField']];
                }
            }
        }
        return $this;
    }

    public function addressArrayForSoap($arrValues = false) {
        if (!empty($arrValues)) {
            $this->_arrSoap['BuyerAddress'] = array();
            foreach ($this->_arrReturn as $strFieldKey => $arrFields) {
                //echo '<pre>'.print_r($arrFields,true);die();
                if (isset($arrFields['formField']) && isset($arrValues[$arrFields['formField']])) {
                    $this->_arrSoap['BuyerAddress'][$strFieldKey] = $arrValues[$arrFields['formField']];
                }
            }
        }
        return $this;
    }

    public function productsArrayForSoap($arrValues = false) {
        if (!empty($arrValues)) {
            $this->_arrSoap['Products'] = $this->_arrProducts;
            $i = $this->_intFirstProduct;
            foreach ($arrValues as $strFieldKey => $strFieldValue) {

                $this->_arrSoap['Products']['Product_code'.$i] = $strFieldKey;
                $this->_arrSoap['Products']['Quantity' . $i] = $strFieldValue;
                $this->_arrSoap['Products']['Dated' . $i] = date('d-m-Y');
                $this->_arrSoap['Products']['DatedDate' . $i] = date('d-m-Y');
                $this->_arrSoap['Products']['DatedFrom' . $i] = date('d-m-Y');
                $this->_arrSoap['Products']['DatedLast' . $i] = date('d-m-Y');
                $i++;
            }
        }
        return $this;
    }
    public function setClientIDForSoap($clientID = false){
         $this->_arrSoap['ClientID'] = $clientID;
         return $this;
    }
       public function setShipmentForSoap($ShipMethod = ''){
         $this->_arrSoap['ShipMethod'] = $ShipMethod;
         return $this;
    }

    public function __get($name) {
        return $this->$name;
    }

}

?>