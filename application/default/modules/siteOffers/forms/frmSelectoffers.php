<?php

class SiteOffers_Form_frmSelectoffers extends Zend_Form {

    protected $_arrFields;
    protected $_arrDisplayGroup = array();
    protected $_arrTickets = array();
    protected $_arrView = array();

    public function __construct($arrTickets = false, $arrView = false,$options = null) {
        if ($arrTickets !== false) {
            /* Settingup the form */
            $this->_arrView = $arrView;
            $this->_arrTickets = $arrTickets;
            $this->_arrFields = $this->formFields();
            //echo APPLICATION_PATH;die();
            $this->addElementPrefixPath('Custom_Decorator',
                    APPLICATION_PATH.'/modules/siteOffers/forms/decorators/',
                    'decorator');
            $this->setMethod('POST');
            parent::__construct($options);
            $this->formFields();
            $this->formElements();
            $this->elementDecorators();
            $this->groupDecorators();
            /* return the form */
            return $this;
        }
    }

    private function formFields() {
        $this->_arrFields = array();
        $intCountTickets = 1;
        $strFooter = false;
        foreach ($this->_arrTickets as $arrTickets) {
            if(count($this->_arrTickets) == $intCountTickets){
                $strFooter = $this->_arrView->languageHelper('offersModule_payment_text');
            }
            $this->_arrFields[] = array('formField' => 'amount_' . $arrTickets['ticket_code'], 'type' => 'text', 'elements' => array('value' => '0','class' => 'siteOfferModule_inputAmount', 'label' => 'Geslacht','labelFields' => array($arrTickets['ticket_name'],'Normaal','Ticketshop','Aantal',$arrTickets['ticket_price_old'],$arrTickets['ticket_price'],$strFooter)));
            $intCountTickets++;
        }
        return $this;
    }

    private function formElements() {
        /* Adding all elements to the Form Stack */
        foreach ($this->_arrFields as $arrField) {
            if (!empty($arrField['elements']) && !empty($arrField['elements']['required']) && !empty($arrField['elements']['label'])) {
                $arrField['elements']['label'] = $arrField['elements']['label'] . ' *';
            }
            $this->addElement($arrField['type'], $arrField['formField'], $arrField['elements']);
            $this->_arrDisplayGroup[] = $arrField['formField'];
        }
        $this->addElement('hidden','product_ID',array('value' => $this->_arrTickets[0]['product_ID']));
        $this->addElement('submit', 'form_submit', array('label' => 'Bestellen', 'style' => 'float:right;margin-right: 19px;float:right;'));
        return $this;
    }

    private function groupDecorators() {
        /* set decorators for all fields for soap request */
        $this->addDisplayGroup($this->_arrDisplayGroup, 'formFields');
        $formPartA = $this->getDisplayGroup('formFields');
        $formPartA->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'table', 'class' => 'siteOffersModule_orderTable'))
        ));

        /* set submit decorator */
        $this->addDisplayGroup(array('form_submit'), 'formSubmit');
        $formPartB = $this->getDisplayGroup('formSubmit');
        $formPartB->setDescription('* Deze velden zijn verplicht')
                ->setDecorators(array(
                    'FormElements',
                    array('HtmlTag', array('tag' => 'div', 'style' => 'margin-top:20px;height:30px;float:right;')),
                    array('Description', array('tag' => 'div', 'style' => 'margin-top:20px;height:30px;float:left;'))
                ));
    }

    private function elementDecorators() {
        /* Decorator for every element */
        $this->setElementDecorators(array('checkout'), $this->_arrDisplayGroup);
        /* Decorator for submit */
        $this->setElementDecorators(array(
            'ViewHelper'), array('form_submit'));
        return $this;
    }

}

?>