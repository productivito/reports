<?php
class Application_Form_frmRoles extends Zend_Form {

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
		$roles = array();
		$users = array();
		$departments = array();
		
		$db = new Application_Table_dbDescribeAccess();
		$dbUsers = new Application_Table_dbUserToDepartments();
		$dbDepartments = new Application_Table_dbDepartments();
		$reportsM = $db->getAllOfType("m");
		$reportsA = $db->getAllOfType("a");
		
		foreach($reportsM as $reportM)
		{
			$roles[$reportM['name_access_level']] = $reportM['full_name'];
		}
		
		foreach($reportsA as $reportA)
		{
			$roles[$reportA['name_access_level']] = $reportA['full_name'];
		}		
		
		// echo '<pre>'.print_r($reportsM,true).'</pre>';
		//echo '<pre>'.print_r($reportsA,true).'</pre>';die();
		
		/*$temp = $dbUsers->getAllUsersFromDepartments();
		foreach($temp as $user)
		{
			$users[$user['id']] = $user['name'];
		}
		
		$temp2 = $dbDepartments->getAllDepartments();
		foreach($temp2 as $department)
		{
			$departments[$department['id']] = $department['name'];
		}*/
		
		$this->_arrFields[] = array('formField' => 'access','type' => 'text', 'elements' => array('class' => 'contactElement','label' => 'Nume','required' => true,'validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));
		
		$this->_arrFields[] = array('formField' => 'reports','type' => 'MultiCheckbox', 'elements' => array('MultiOptions' => $roles,'class' => 'contactElement','label' => 'Rapoarte'));
			
		$this->_arrFields[] = array('formField' => 'departments','type' => 'MultiCheckbox', 'elements' => array('MultiOptions' => array('r' => 'Read', 'a' => 'Add','m' => 'Modify', 'd' => 'Delete'),'class' => 'contactElement','label' => 'Departamente'));
		$this->_arrFields[] = array('formField' => 'role_management','type' => 'MultiCheckbox', 'elements' => array('MultiOptions' => array('r' => 'Read', 'a' => 'Add','m' => 'Modify', 'd' => 'Delete'),'class' => 'contactElement','label' => 'Management Roluri'));
		$this->_arrFields[] = array('formField' => 'access_accounts','type' => 'MultiCheckbox', 'elements' => array('MultiOptions' => array('r' => 'Read', 'a' => 'Add','m' => 'Modify', 'd' => 'Delete'),'class' => 'contactElement','label' => 'Conturi de Acces'));
		$this->_arrFields[] = array('formField' => 'categories','type' => 'MultiCheckbox', 'elements' => array('MultiOptions' => array('r' => 'Read', 'a' => 'Add','m' => 'Modify', 'd' => 'Delete'),'class' => 'contactElement','label' => 'Categorii'));
		$this->_arrFields[] = array('formField' => 'filters','type' => 'MultiCheckbox', 'elements' => array('MultiOptions' => array('r' => 'Read', 'a' => 'Add','m' => 'Modify', 'd' => 'Delete'),'class' => 'contactElement','label' => 'Filtre'));
		$this->_arrFields[] = array('formField' => 'emails','type' => 'MultiCheckbox', 'elements' => array('MultiOptions' => array('r' => 'Read', 'a' => 'Add','m' => 'Modify', 'd' => 'Delete'),'class' => 'contactElement','label' => 'Alerte'));
		$this->_arrFields[] = array('formField' => 'Productivito_clients','type' => 'MultiCheckbox', 'elements' => array('MultiOptions' => array('r' => 'Read', 'a' => 'Add','m' => 'Modify', 'd' => 'Delete'),'class' => 'contactElement','label' => 'Clienti Productivito'));
		$this->_arrFields[] = array('formField' => 'emailaddress','type' => 'MultiCheckbox', 'elements' => array('MultiOptions' => array('r' => 'Read'),'class' => 'contactElement','label' => 'Email'));
		//$this->_arrFields[] = array('formField' => 'users','type' => 'MultiCheckbox', 'elements' => array('MultiOptions' => $users,'class' => 'contactElement','label' => 'Utilizatori'));
		//$this->_arrFields[] = array('formField' => 'departments_monitorized','type' => 'MultiCheckbox', 'elements' => array('MultiOptions' => $departments,'class' => 'contactElement','label' => 'Departamente'));
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