<?php
class Application_Form_frmContact extends Zend_Form {

    protected $_arrFields;
    protected $_arrDisplayGroup = array();
    
    public function __construct($options = null) {

        /* Settingup the form */
        $this->_arrFields = $this->formFields();
        $this->setMethod('POST');
        parent::__construct($options);
        $this->formFields();
        $this->formElements();
        $this->elementDecorators();
        $this->groupDecorators();
        /* return the form */
        return $this;
    }

    private function formFields(){

        /* The form elements */
        $this->_arrFields = array();
        $this->_arrFields[] = array('formField' => 'department','type' => 'select', 'elements' => array('MultiOptions' => array('1' => 'Department1', '2' => 'Department2'),'class' => 'contactElement','label' => 'Department'));
        /*$this->_arrFields[] = array('formField' => 'contact_firstname','type' => 'text','elements' => array('class' => 'contactElement','label' => 'Voornaam','required' => true,'validators' => array(array('validator' => 'stringLength','options' => array(1, 200)))));
        $this->_arrFields[] = array('formField' => 'contact_initials','type' => 'text','elements' => array('class' => 'contactElement','label' => 'Tussenvoegsel'));
        $this->_arrFields[] = array('formField' => 'contact_surname','type' => 'text','elements' => array('class' => 'contactElement','label' => 'Achternaam','required' => true,'validators' => array(array('validator' => 'stringLength','options' => array(1, 200)))));
        $this->_arrFields[] = array('formField' => 'contact_email','type' => 'text','elements' => array('class' => 'contactElement','label' => 'E-Mail','required' => true,'validators' => array(array('validator' => 'EmailAddress'))));
        $this->_arrFields[] = array('formField' => 'contact_company','type' => 'text','elements' => array('class' => 'contactElement','label' => 'Bedrijfsnaam','required' => false,'validators' => array(array('validator' => 'stringLength','options' => array(1, 200)))));
        $this->_arrFields[] = array('formField' => 'contact_company','type' => 'text','elements' => array('class' => 'contactElement','label' => 'Bedrijfsnaam','required' => false,'validators' => array(array('validator' => 'stringLength','options' => array(10, 15)))));
        $this->_arrFields[] = array('formField' => 'contact_message','type' => 'textarea','elements' => array('class' => 'contactElement','cols' => '30','rows' => '10','label' => 'Uw vraag of opmerking','required' => true));*/
        return $this;
    }

    private function formElements() {
        /* Adding all elements to the Form Stack */
        foreach($this->_arrFields as $arrField){
            if(!empty($arrField['elements']) && !empty($arrField['elements']['required']) && !empty($arrField['elements']['label'])){
                $arrField['elements']['label'] = $arrField['elements']['label'].' *';
            }
            $this->addElement($arrField['type'], $arrField['formField'],$arrField['elements']);
            $this->_arrDisplayGroup[] = $arrField['formField'];
        }
        $this->addElement('submit', 'form_submit' ,array('label' => 'Afiseaza','style' => 'margin-right: 19px;'));
        return $this;
    }

    private function groupDecorators(){
        /* set decorators for all fields for soap request */
        $this->addDisplayGroup($this->_arrDisplayGroup, 'formFields');
        $formPartA = $this->getDisplayGroup('formFields');
        $formPartA->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'table','class' => 'tableContactForm'))
        ));

        /* set submit decorator */
        $this->addDisplayGroup(array('form_submit'), 'formSubmit');
        $formPartB = $this->getDisplayGroup('formSubmit');
        $formPartB->setDescription('* Alege un departament')
                  ->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'div', 'style' => 'margin-top:20px;height:30px;')),
            array('Description',  array('tag' => 'div', 'style' => 'margin-top:20px;height:30px;float:left;'))
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