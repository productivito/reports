<?php

class SiteOffers_Form_frmCheckout extends Zend_Form {


    protected $_arrFields;
    protected $_arrHiddenFields;
    protected $_arrDisplayGroup = array();
    public function __construct($arrHiddenFields = false,$options = null) {

        $this->_arrFields = new SiteOffers_Object_arrSoap();
        $this->_arrFields ->getCheckoutArray();
        $this->_arrHiddenFields = $arrHiddenFields;
        $this->setMethod('POST');
        parent::__construct($options);
        $this->formElements();
        $this->elementDecorators();
        $this->groupDecorators();
        return $this;
    }

    public function formElements() {

        /* Create all formfields needed for the Soap request. Fields are defined in objects/arrSoap.php */
        foreach($this->_arrFields->_arrReturn as $arrField){
            $this->addElement($arrField['type'], $arrField['formField'],$arrField['elements']);
            $this->_arrDisplayGroup[] = $arrField['formField'];
        }
        if(!empty($this->_arrHiddenFields)){
            foreach($this->_arrHiddenFields as $strKey => $strHiddenValue){
                $this->addElement('hidden', 'amount_'.$strKey ,array('value' => $strHiddenValue));
                $this->_arrDisplayGroup[] = 'amount_'.$strKey;
            }
        }

        $this->addElement('submit', 'order_submit' ,array('label' => 'Betalen'));
        /* Create Submit Buttom */

        return $this;
    }

    private function groupDecorators(){
        /* set decorators for all fields for soap request */
        $this->addDisplayGroup($this->_arrDisplayGroup, 'formFields');
        $formPartA = $this->getDisplayGroup('formFields');
        $formPartA->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'table', 'class' => 'tableCheckoutForm')),
        ));

        /* set submit decorator */
        $this->addDisplayGroup(array('order_submit'), 'formSubmit');
        $formPartB = $this->getDisplayGroup('formSubmit');
        $formPartB->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'div', 'class' => 'tableCheckoutSubmit')),
        ));

    }

    private function elementDecorators() {
        /* Decorator for every element */
        $this->setElementDecorators(array(
            'ViewHelper',
            'Errors',
            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
            array('Label', array('tag' => 'td')),
            array(array('row' => 'HtmlTag'), array('tag' => 'tr'))),$this->_arrDisplayGroup);
        /* Decorator for submit */
        $this->setElementDecorators(array(
            'ViewHelper'),array('form_submit'));
        return $this;
    }
    

}
?>