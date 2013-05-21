<?php
class Application_Form_frmUserToDepartment extends Zend_Form {

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
    
    public function formFields() {
    	$this->_arrFields = array();
		
		$departments = array();	
		$money = array(
			'RON' => 'RON',
			'EUR' => 'EUR',
			'USD' => 'USD'
		
		);			
		$db = new Application_Table_dbDepartments();
		$depts = $db->getAllDepartments();				
		
		foreach($depts as $dept)
		{
			$departments[$dept['id']] = $dept['name'];
		}
		
		
        $this->_arrFields[] = array('formField' => 'name','type' => 'text', 'elements' => array('class' => 'contactElement','label' => 'Utilizator Windows monitorizat','disabled' => 'disabled','validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));
		$this->_arrFields[] = array('formField' => 'last_name','type' => 'text', 'elements' => array('class' => 'contactElement','label' => 'Nume','validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));
		$this->_arrFields[] = array('formField' => 'first_name','type' => 'text', 'elements' => array('class' => 'contactElement','label' => 'Prenume','validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));
		
		$this->_arrFields[] = array('formField' => 'cnp','type' => 'text', 'elements' => array('class' => 'contactElement','label' => 'CNP','required' => false,'validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));
		$this->_arrFields[] = array('formField' => 'working_hours','type' => 'text', 'elements' => array('class' => 'contactElement timepicker','label' => 'Norma ore de lucru / zi ','required' => false,'validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));
		$this->_arrFields[] = array('formField' => 'break_lenght','type' => 'text', 'elements' => array('class' => 'contactElement timepicker','label' => 'Lungime Pauza (de masa)','required' => false,'validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));
		
		$this->_arrFields[] = array('formField' => 'cost_per_hour','type' => 'text', 'elements' => array('class' => 'contactElement','label' => 'Cost/ora','required' => false,'validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));
		$this->_arrFields[] = array('formField' => 'payment','type' => 'select', 'elements' => array('MultiOptions' => $money,'class' => 'contactElement','label' => 'Valuta salariu'));		
		$this->_arrFields[] = array('formField' => 'department','type' => 'select', 'elements' => array('MultiOptions' => $departments,'class' => 'contactElement','label' => 'Departament'));
		$this->_arrFields[] = array('formField' => 'company','type' => 'text', 'elements' => array('class' => 'contactElement','label' => 'Firma','required' => false,'validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));
		
		$this->_arrFields[] = array('formField' => 'day1_start','type' => 'text', 'elements' => array('class' => 'contactElement timepicker','label' => 'Luni Start','required' => false,'validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));
		$this->_arrFields[] = array('formField' => 'day1_stop','type' => 'text', 'elements' => array('class' => 'contactElement timepicker','label' => 'Luni Sfarsit','required' => false,'validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));
		
		$this->_arrFields[] = array('formField' => 'day2_start','type' => 'text', 'elements' => array('class' => 'contactElement timepicker','label' => 'Marti Start','required' => false,'validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));
		$this->_arrFields[] = array('formField' => 'day2_stop','type' => 'text', 'elements' => array('class' => 'contactElement timepicker','label' => 'Marti Sfarsit','required' => false,'validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));
		
		$this->_arrFields[] = array('formField' => 'day3_start','type' => 'text', 'elements' => array('class' => 'contactElement timepicker','label' => 'Miercuri Start','required' => false,'validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));
		$this->_arrFields[] = array('formField' => 'day3_stop','type' => 'text', 'elements' => array('class' => 'contactElement timepicker','label' => 'Miercuri Sfarsit','required' => false,'validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));
		
		$this->_arrFields[] = array('formField' => 'day4_start','type' => 'text', 'elements' => array('class' => 'contactElement timepicker','label' => 'Joi Start','required' => false,'validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));
		$this->_arrFields[] = array('formField' => 'day4_stop','type' => 'text', 'elements' => array('class' => 'contactElement timepicker','label' => 'Joi Sfarsit','required' => false,'validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));
		
		$this->_arrFields[] = array('formField' => 'day5_start','type' => 'text', 'elements' => array('class' => 'contactElement timepicker','label' => 'Viner Start','required' => false,'validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));
		$this->_arrFields[] = array('formField' => 'day5_stop','type' => 'text', 'elements' => array('class' => 'contactElement timepicker','label' => 'Vineri Sfarsit','required' => false,'validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));
		
		$this->_arrFields[] = array('formField' => 'day6_start','type' => 'text', 'elements' => array('class' => 'contactElement timepicker','label' => 'Sambata Start','required' => false,'validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));
		$this->_arrFields[] = array('formField' => 'day6_stop','type' => 'text', 'elements' => array('class' => 'contactElement timepicker','label' => 'Sambata Sfarsit','required' => false,'validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));					

		$this->_arrFields[] = array('formField' => 'day7_start','type' => 'text', 'elements' => array('class' => 'contactElement timepicker','label' => 'Duminica Start','required' => false,'validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));
		$this->_arrFields[] = array('formField' => 'day7_stop','type' => 'text', 'elements' => array('class' => 'contactElement timepicker','label' => 'Duminica Sfarsit','required' => false,'validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));		
		
		
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