<?php
class Application_Form_frmUser extends Zend_Form {

    protected $_arrFields;
    protected $_arrDisplayGroup = array();
    
    public function __construct($options = null,$edit) {

        /* Settingup the form */
        $this->_arrFields = $this->formFields($edit);
        $this->setMethod('POST');
        parent::__construct($options,$edit);
        $this->formFields($edit);
        $this->formElements();
        $this->elementDecorators();
        $this->groupDecorators();
        /* return the form */
        return $this;
    }
    
    public function formFields($edit) {
    	$this->_arrFields = array();
		$users = array();
		$departments = array();
		
		$dbUsers = new Application_Table_dbUserToDepartments();
		$dbDepartments = new Application_Table_dbDepartments();	
				
		$temp = $dbUsers->getAllUsersFromDepartments();	
		foreach($temp as $user)
		{
			$dept = $dbDepartments->getDepartmentbyId($user['department']);
			$dept_name = 'Departament sters';
			if(!empty($dept))
				$dept_name = $dept['name'];
			
			$users[$user['id']] = $user['name'];
			$first_name = '';
			$last_name = '';
			if( !empty($user['last_name']) )
			{
				$last_name = $user['last_name'];
			}
			if( !empty($user['first_name']) )
			{
				$first_name = $user['first_name'];
			}
			
			if( $first_name != '' || $last_name != '' )
				$users[$user['id']] = $users[$user['id']].' ( '.$last_name.' '.$first_name. ' / ' . $dept_name .' )';
				
		}
		
		$temp2 = $dbDepartments->getAllDepartments();
		foreach($temp2 as $department)
		{
			$departments[$department['id']] = $department['name'];
		}	
							
		$db = new Application_Table_dbMenu();
		
		$roles = $db->getRoles();	
		
        $this->_arrFields[] = array('formField' => 'username','type' => 'text', 'elements' => array('class' => 'contactElement','label' => 'Username','required' => true,'validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));
        $this->_arrFields[] = array('formField' => 'name','type' => 'text', 'elements' => array('class' => 'contactElement','label' => 'Nume Utilizator','required' => true,'validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));
        $this->_arrFields[] = array('formField' => 'email','type' => 'text', 'elements' => array('class' => 'contactElement','label' => 'Email','required' => true,'validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));
		
		if ($edit == '1')
		{
        	$this->_arrFields[] = array('formField' => 'password','type' => 'password', 'elements' => array('class' => 'contactElement','label' => 'Parola','validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));
			$this->_arrFields[] = array('formField' => 'password_check','type' => 'Checkbox', 'elements' => array('class' => 'contactElement','label' => 'Modifica Parola'));
		}
		else
		{
			$this->_arrFields[] = array('formField' => 'password','type' => 'password', 'elements' => array('class' => 'contactElement','label' => 'Parola','required' => true,'validators' => array(array('validator' => 'stringLength','options' => array(1, 128)))));
		}

		$this->_arrFields[] = array('formField' => 'role','type' => 'select', 'elements' => array('MultiOptions' => $roles,'class' => 'contactElement','label' => 'Rol'));
		
		$this->_arrFields[] = array('formField' => 'users',
									'type'      => 'MultiCheckbox',
									'elements'  => array('MultiOptions' => $users,
														 'class'        => 'contactElement',
														 'label'        => 'Utilizatori'));
														 
		$this->_arrFields[] = array('formField' => 'qwertyuiop',
									'type'      => 'hidden',
									'elements'  => array());
									
		
		$this->_arrFields[] = array('formField' => 'departments_monitorized',
								 	'type'      => 'MultiCheckbox',
								 	'elements'  => array('MultiOptions' => $departments,
								 						'class' 		=> 'departments',
								 						'label' 		=> 'Departamente'));
				
        return $this;
    }

    private function formElements()
    {
        /* Adding all elements to the Form Stack */
        foreach($this->_arrFields as $arrField)
		{
            if (!empty($arrField['elements']) && !empty($arrField['elements']['required']) && !empty($arrField['elements']['label']))
            {
                $arrField['elements']['label'] = $arrField['elements']['label'].' *';
            }
			
	        $this->addElement($arrField['type'], $arrField['formField'], $arrField['elements']);
						
            $this->_arrDisplayGroup[] = $arrField['formField'];
        }

        $this->addElement('submit', 'form_submit' , array('label' => 'Salveaza','style' => 'margin-right: 19px;'));
		
        return $this;
    }

    private function groupDecorators()
    {
        /* set decorators for all fields for soap request */
        $this->addDisplayGroup($this->_arrDisplayGroup, 'formFields');
		
        $formPartA = $this->getDisplayGroup('formFields');
		
        $formPartA->setDecorators(array('FormElements',
            							array('HtmlTag',
            								  array('tag' => 'table',
            								  		'class' => 'tableContactForm'))
        						 ));

        /* set submit decorator */
        $this->addDisplayGroup(array('form_submit'), 'formSubmit');
		
        $formPartB = $this->getDisplayGroup('formSubmit');
		
        $formPartB->setDescription('* Camp Obligatoriu')
                  ->setDecorators(array('FormElements',
            							array('HtmlTag',
            								  array('tag' => 'div',
            								  		'style' => 'margin-top:20px;height:30px;',
            								  		'class' => 'submit')),
            					  array('Description',
            					  		array('tag' => 'div',
            					  			  'style' => 'margin-top:20px;height:30px;float:left;'))
        						 ));
    }

    private function elementDecorators()
    {
        /* Decorator for every element */
       //print_r($this->_arrDisplayGroup);die();
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