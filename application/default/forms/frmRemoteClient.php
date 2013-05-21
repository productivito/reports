<?php
class Application_Form_frmRemoteClient extends Zend_Form {

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
		$var = array(
                    '1' => 'Enabled',
                    '0' => 'Disabled' 
                     );
						
		$db = new Application_Table_dbMenu();
		$roles = $db->getRoles();	
        $this->_arrFields[] = array('formField' => 'rcName','type' => 'text', 'elements' => array('class' => 'contactElement','label' => 'Nume calculator','disabled='=>'disabled','validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));
        $this->_arrFields[] = array('formField' => 'rcClientIP','type' => 'text', 'elements' => array('class' => 'contactElement','label' => 'IP Client','disabled='=>'disabled','validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));
        $this->_arrFields[] = array('formField' => 'rcClientLastSeen','type' => 'text', 'elements' => array('class' => 'contactElement','label' => 'Ultima Logare','disabled='=>'disabled','validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));
        $this->_arrFields[] = array('formField' => 'rcClientVersion','type' => 'text', 'elements' => array('class' => 'contactElement','label' => 'Versiune Client','disabled='=>'disabled','validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));
		
		$this->_arrFields[] = array('formField' => 'rcEnabled','type' => 'select', 'elements' => array('MultiOptions' => $var,'class' => 'contactElement','label' => 'Status Client'));
		$this->_arrFields[] = array('formField' => 'rcTrace','type' => 'select', 'elements' => array('MultiOptions' => $var,'class' => 'contactElement','label' => 'Status Log'));
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
        $formPartB->setDescription('* Camp obligatoriu')
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