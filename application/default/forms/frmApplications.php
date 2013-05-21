<?php
class Application_Form_frmApplications extends Zend_Form {

    protected $_arrFields;
    protected $_arrDisplayGroup = array();
    
    public function __construct($level = 'all',$options = null) {

        /* Settingup the form */
        $this->_arrFields = $this->formFields($level);
        $this->setMethod('POST');
        parent::__construct($options);
        $this->formFields($level);
        $this->formElements();
        $this->elementDecorators();
        $this->groupDecorators();
        /* return the form */
        return $this;
    }
    
    public function formFields($level) {
    	$this->_arrFields = array();
		if($level == "department")
        $this->_arrFields[] = array('formField' => 'department','type' => 'select', 'elements' => array('MultiOptions' => array('1' => 'Department1', '2' => 'Department2'),'class' => 'contactElement','label' => 'Department'));
		if($level == "employee")
			$this->_arrFields[] = array('formField' => 'angajari','type' => 'select', 'elements' => array('MultiOptions' => array('1' => 'Angajat1', '2' => 'Angajat2'),'class' => 'contactElement','label' => 'Angajat'));
		$this->_arrFields[] = array('formField' => 'start_date','type' => 'text', 'elements' => array('class' => 'startElement','label' => 'Start date'));
		$this->_arrFields[] = array('formField' => 'end_date','type' => 'text', 'elements' => array('class' => 'endElement','label' => 'End date'));
        /*
        $this->_arrFields[] = array('formField' => 'username','type' => 'text', 'elements' => array('class' => 'contactElement','label' => 'Username','required' => true,'validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));
        $this->_arrFields[] = array('formField' => 'name','type' => 'text', 'elements' => array('class' => 'contactElement','label' => 'Full Name','required' => true,'validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));
        $this->_arrFields[] = array('formField' => 'email','type' => 'text', 'elements' => array('class' => 'contactElement','label' => 'Email','required' => true,'validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));
        $this->_arrFields[] = array('formField' => 'password','type' => 'password', 'elements' => array('class' => 'contactElement','label' => 'Password','required' => true,'validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));
	    */
		
        
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
        $this->addElement('submit', 'form_submit' ,array('label' => 'Salveaza','style' => 'margin-right: 19px;'));
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
        $formPartB->setDescription('* Editeaza date')
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