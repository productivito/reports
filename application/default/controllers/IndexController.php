<?php

/**
 * Index controller for PROJECT_NAME
 *
 * Made by:     Thomas Bredenbeek
 * Modified by: -
 * Copyright:   MaxWebresults 2011
 *
 */
class IndexController extends Zend_Controller_Action {


    public function init() {
    }
	
    function indexAction() {
       
        $this->_helper->layout->setLayout('login_layout');
        $mailError = $this->getRequest()->getParam('mailerror');
        $email = $this->getRequest()->getParam('email');
        if ($mailError == '1' || $mailError == 'dob') {
            $this->view->loginModule = $this->view->action('index', 'index', 'login', array('error' => $mailError, 'email' => $email));
        } else {
            $this->view->loginModule = $this->view->action('index', 'index', 'login', array());
        }
    }

   function browserAction(){
        //$this->_helper->layout->setLayout('browser');
    }

    public function loginAction() {
        $this->_helper->layout->setLayout('login_layout');
        $arrCredentials = $this->_request->getPost();
		$db = new Application_Model_Database();
		
        if (!empty($arrCredentials)) {
            
            $result = $db->userLoginAttempt($arrCredentials);
            
            if(!empty($result)) {
                    $this->view->loginModule = $this->view->action('index', 'index', 'login', array('credentials' => $arrCredentials));
            } else {
                $this->view->loginModule = $this->view->action('index', 'index', 'login', array('credentials' => array()));
            }
        }
    }

    public function forgotpswAction() {
        $this->_helper->layout->setLayout('login_layout');
        $email = $this->_request->getPost('email');
        $month = $this->_request->getPost('month');
        $year = $this->_request->getPost('year');
        $day = $this->_request->getPost('day');

        if (!empty($email)) {
            if (!empty($month) && !empty($year) && !empty($day))
                $this->view->loginModule = $this->view->action('forgotpsw', 'index', 'login', array('email' => $email, 'month' => $month, 'year' => $year, 'day' => $day));
            else
                $this->view->loginModule = $this->view->action('forgotpsw', 'index', 'login', array('email' => $email));
        }
    }

    public function logoutAction() {
        $this->view->loginModule = $this->view->action('logout', 'index', 'login');
    }

    function homeAction() {
    	
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();

		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];
		
		//echo "<pre>";var_dump($userInfo);echo "</pre>";die();
		 $description = $db->getMenu($role['access'],"u");
		 $this->view->menu = $description;
 		
		$i = 1;
		/*$this->_objAuth = Zend_Auth::getInstance();
		//var_dump($this->_objAuth);die();	
        if ($this->_objAuth->hasIdentity()) {
            $storage = $this->_objAuth->getStorage();
            $objStorage = $storage->read();
			
            if(isset($objStorage->user_first_login) && $objStorage->user_first_login == 1){
            	
                $this->_redirect('driver/index');
            }
        }*/

		
		$rolesSidebar = $db->getAccessLevel();
		
		$this->view->rolesSidebar = $rolesSidebar;
		
		$rows = $db->getAllUsers();
		$roles[] = array();	
		
		//echo "<pre>";
		//print_r( $description);die();
		//echo "</pre>";
		
		foreach($rows as $row)
		{
			$role = $db->getRole($row['access_level']);
			$roles[$i] = $role['access'];
			$i++;
		}
		
		$this->view->rows = $rows;	
		$this->view->roles = $roles;
		
		$rights = $db->getRights("Admin",'access_accounts');
		
		$this->view->rights = $rights;

		 /*for($i = 0;$i<count($description);$i++){
		 echo "<pre>";
		 	print_r($description[$i]);			
		echo "</pre>";
		}die();*/
		 //$this->view->menu = $description;
		 	
    }
	
	/*function logoutAction()
	{
	    $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
        
		session_destroy();
		$this->_redirect('index/index');
	}*/
	
	//functie care preia informatii din index/rapoarte
	function rapoarteAction()
	{
	   $db = new Application_Model_Database();
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);	 
		$this->view->role = $role['access'];
		
	   $descriptionMonitor = $db->getMenu($role['access'],"m");
	   $description1Activity = $db->getMenu($role['access'],"a");		
	   
	   $this->view->reportMonitor = $descriptionMonitor;
	   $this->view->reportActivity = $description1Activity;
	   			
	   //$departments = $db->getAllDepartments();
	  // $usersToDepartments = $db->getAllUsersFromDepartments();
	   
	   /*  Part for the organigram filters from db*/
		$status = array();
		$um = array();
		$dm = array();
		$temp_um = array();
		$temp_dm = array();
		$deptbanned = array();

		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;	
						
		$departments = $db->getAllDepartments();
	    $usersToDepartments = $db->getAllUsersFromDepartments();
		$currentUser = $db->getUserDetails($userInfo->id);
		
		$temp_dm = explode("/",$currentUser['departments_monitorized']);
		$temp_um = explode("/",$currentUser['users']);
			
		for($i=0;$i<count($temp_um);$i++)
		{
			$um[$temp_um[$i]] = $temp_um[$i];
		}
		
		for($i=0;$i<count($temp_dm);$i++)
		{
			$dm[$temp_dm[$i]] = $temp_dm[$i];
		}

		foreach($departments as $department)
		{
			if (!in_array($department['id'], $dm))
			{
				$status[$department['id']] = $department['id'];
			}
		}

		foreach($status as $item)
		{
			$deptbanned[$item] = $item;

			$deptbanned = $this->getAllSubDepartments($deptbanned, $item);
		}			

		$this->view->departmentsMonitorized = $dm;
		$this->view->usersMonitorized = $um;
		$this->view->deptBanned = $deptbanned;	   
	    /* End Organigram */
	   
	 // echo "<pre>"; print_r($departments);die();echo "</pre>";
	    $this->view->departments = $departments;
		$this->view->usersToDepartments = $usersToDepartments;
		$this->_helper->layout()->setLayout('rapoarte');
		
	    $form = new Application_Form_frmContact(array('id' => 'contactForm'));
        
        if (!empty($_POST) && $form->isValid($_POST)) {
           $this->view->formSucces = 1;
           $this->view->department = $this->_request->getPost("department");
           
        } elseif ($_POST) {
            $form->populate($_POST);
        }
        $this->view->form = $form;
		
	}
	
	
	//=======================================================Raport de Monitorizare============================
	//functie care preia informatii din index/raportfisiere
	function reportfilesAction()
	{
		$db = new Application_Model_Database();

        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];
		
	   $descriptionMonitor = $db->getMenu($role['access'],"m");
	   $description1Activity = $db->getMenu($role['access'],"a");		

	   $this->view->reportMonitor = $descriptionMonitor;
	   $this->view->reportActivity = $description1Activity;
	   		
	    //$departments = $db->getAllDepartments();
	    //$usersToDepartments = $db->getAllUsersFromDepartments();
	    // echo "<pre>"; print_r($departments);die();echo "</pre>";
	    
	    /*  Part for the organigram filters from db*/
		$status = array();
		$um = array();
		$dm = array();
		$temp_um = array();
		$temp_dm = array();
		$deptbanned = array();

		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;	
						
		$departments = $db->getAllDepartments();
	    $usersToDepartments = $db->getAllUsersFromDepartments();
		$currentUser = $db->getUserDetails($userInfo->id);
		
		$temp_dm = explode("/",$currentUser['departments_monitorized']);
		$temp_um = explode("/",$currentUser['users']);
			
		for($i=0;$i<count($temp_um);$i++)
		{
			$um[$temp_um[$i]] = $temp_um[$i];
		}
		
		for($i=0;$i<count($temp_dm);$i++)
		{
			$dm[$temp_dm[$i]] = $temp_dm[$i];
		}

		foreach($departments as $department)
		{
			if (!in_array($department['id'], $dm))
			{
				$status[$department['id']] = $department['id'];
			}
		}

		foreach($status as $item)
		{
			$deptbanned[$item] = $item;

			$deptbanned = $this->getAllSubDepartments($deptbanned, $item);
		}			

		$this->view->departmentsMonitorized = $dm;
		$this->view->usersMonitorized = $um;
		$this->view->deptBanned = $deptbanned;	    
	     /* End Organigram */
	    
	    $this->view->departments = $departments;
		$this->view->usersToDepartments = $usersToDepartments;
		
		$this->_helper->layout()->setLayout('rapoarte');
	    $db = new Application_Model_Database();
	}
	
	//functie care preia informatii din index/raportdocumente
	function reportdocumentsAction()
	{
		
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];
		
	   $descriptionMonitor = $db->getMenu($role['access'],"m");
	   $description1Activity = $db->getMenu($role['access'],"a");			

	   $this->view->reportMonitor = $descriptionMonitor;
	   $this->view->reportActivity = $description1Activity;		
		
	    //$departments = $db->getAllDepartments();
	    //$usersToDepartments = $db->getAllUsersFromDepartments();
	    // echo "<pre>"; print_r($departments);die();echo "</pre>";
	    
	    /*  Part for the organigram filters from db*/
		$status = array();
		$um = array();
		$dm = array();
		$temp_um = array();
		$temp_dm = array();
		$deptbanned = array();

		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;	
						
		$departments = $db->getAllDepartments();
	    $usersToDepartments = $db->getAllUsersFromDepartments();
		$currentUser = $db->getUserDetails($userInfo->id);
		
		$temp_dm = explode("/",$currentUser['departments_monitorized']);
		$temp_um = explode("/",$currentUser['users']);
			
		for($i=0;$i<count($temp_um);$i++)
		{
			$um[$temp_um[$i]] = $temp_um[$i];
		}
		
		for($i=0;$i<count($temp_dm);$i++)
		{
			$dm[$temp_dm[$i]] = $temp_dm[$i];
		}

		foreach($departments as $department)
		{
			if (!in_array($department['id'], $dm))
			{
				$status[$department['id']] = $department['id'];
			}
		}

		foreach($status as $item)
		{
			$deptbanned[$item] = $item;

			$deptbanned = $this->getAllSubDepartments($deptbanned, $item);
		}			

		$this->view->departmentsMonitorized = $dm;
		$this->view->usersMonitorized = $um;
		$this->view->deptBanned = $deptbanned;	    
	     /* End Organigram */
	    
	    $this->view->departments = $departments;
		$this->view->usersToDepartments = $usersToDepartments;
				
		$this->_helper->layout()->setLayout('rapoarte');
		
		$level = $this->getRequest()->getParam('level');
		
		switch($level) {
			default:
			case 'all':
				$arrApplications = array('','Mozilla', 'Chrome', 'MS Excel', '');
			break;
			case 'department':
                $arrApplications = array('','Mozilla', 'Chrome', 'MS Excel', '');
			break;
			case 'employee':
                $arrApplications = array('','Mozilla', 'Chrome', 'MS Excel', '');
			break;
		}
		
		$this->view->form = new Application_Form_frmApplications($level,array('id' => 'applicationsForm'));
		
		if (!empty($_POST) && $this->view->form->isValid($_POST)) {
           $this->view->formSucces = 1;
           $this->view->department = $this->_request->getPost("department");
           
        } elseif ($_POST) {
            $form->populate($_POST);
        }
	}
	
	//functie care preia informatii din index/raportcalculator
	function reportcomputerAction()
	{
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];
		
	   $descriptionMonitor = $db->getMenu($role['access'],"m");
	   $description1Activity = $db->getMenu($role['access'],"a");		

	   $this->view->reportMonitor = $descriptionMonitor;
	   $this->view->reportActivity = $description1Activity;		
		
	   // $departments = $db->getAllDepartments();
	   // $usersToDepartments = $db->getAllUsersFromDepartments();
	    // echo "<pre>"; print_r($departments);die();echo "</pre>";
	    
	    /*  Part for the organigram filters from db*/
		$status = array();
		$um = array();
		$dm = array();
		$temp_um = array();
		$temp_dm = array();
		$deptbanned = array();

		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;	
						
		$departments = $db->getAllDepartments();
	    $usersToDepartments = $db->getAllUsersFromDepartments();
		$currentUser = $db->getUserDetails($userInfo->id);
		
		$temp_dm = explode("/",$currentUser['departments_monitorized']);
		$temp_um = explode("/",$currentUser['users']);
			
		for($i=0;$i<count($temp_um);$i++)
		{
			$um[$temp_um[$i]] = $temp_um[$i];
		}
		
		for($i=0;$i<count($temp_dm);$i++)
		{
			$dm[$temp_dm[$i]] = $temp_dm[$i];
		}

		foreach($departments as $department)
		{
			if (!in_array($department['id'], $dm))
			{
				$status[$department['id']] = $department['id'];
			}
		}

		foreach($status as $item)
		{
			$deptbanned[$item] = $item;

			$deptbanned = $this->getAllSubDepartments($deptbanned, $item);
		}			

		$this->view->departmentsMonitorized = $dm;
		$this->view->usersMonitorized = $um;
		$this->view->deptBanned = $deptbanned;	    
	     /* End Organigram */
		 
	    $this->view->departments = $departments;
		$this->view->usersToDepartments = $usersToDepartments;
				
		$this->_helper->layout()->setLayout('rapoarte');
		
		$form = new Application_Form_frmContact(array('id' => 'contactForm'));
        
        if (!empty($_POST) && $form->isValid($_POST)) {
           $this->view->formSucces = 1;
           $this->view->department = $this->_request->getPost("department");
           
        } elseif ($_POST) {
            $form->populate($_POST);
        }
        $this->view->form = $form;
	}
	
	//functie care preia informatii din index/raportaplicatii
	function reportapplicationsAction()
	{
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];
		
	   $descriptionMonitor = $db->getMenu($role['access'],"m");
	   $description1Activity = $db->getMenu($role['access'],"a");		

	   $this->view->reportMonitor = $descriptionMonitor;
	   $this->view->reportActivity = $description1Activity;		
		
	    //$departments = $db->getAllDepartments();
	    //$usersToDepartments = $db->getAllUsersFromDepartments();
	    // echo "<pre>"; print_r($departments);die();echo "</pre>";
	    
	    /*  Part for the organigram filters from db*/
		$status = array();
		$um = array();
		$dm = array();
		$temp_um = array();
		$temp_dm = array();
		$deptbanned = array();

		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;	
						
		$departments = $db->getAllDepartments();
	    $usersToDepartments = $db->getAllUsersFromDepartments();
		$currentUser = $db->getUserDetails($userInfo->id);
		
		$temp_dm = explode("/",$currentUser['departments_monitorized']);
		$temp_um = explode("/",$currentUser['users']);
			
		for($i=0;$i<count($temp_um);$i++)
		{
			$um[$temp_um[$i]] = $temp_um[$i];
		}
		
		for($i=0;$i<count($temp_dm);$i++)
		{
			$dm[$temp_dm[$i]] = $temp_dm[$i];
		}

		foreach($departments as $department)
		{
			if (!in_array($department['id'], $dm))
			{
				$status[$department['id']] = $department['id'];
			}
		}

		foreach($status as $item)
		{
			$deptbanned[$item] = $item;

			$deptbanned = $this->getAllSubDepartments($deptbanned, $item);
		}			

		$this->view->departmentsMonitorized = $dm;
		$this->view->usersMonitorized = $um;
		$this->view->deptBanned = $deptbanned;	    
	     /* End Organigram */
	    
	    $this->view->departments = $departments;
		$this->view->usersToDepartments = $usersToDepartments;
				
		$this->_helper->layout()->setLayout('rapoarte');
		
		$level = $this->getRequest()->getParam('level');
		
		switch($level) {
			default:
			case 'all':
				$arrApplications = array('','Mozilla', 'Chrome', 'MS Excel', '');
			break;
			case 'department':
                $arrApplications = array('','Mozilla', 'Chrome', 'MS Excel', '');
			break;
			case 'employee':
                $arrApplications = array('','Mozilla', 'Chrome', 'MS Excel', '');
			break;
		}
		
		$this->view->form = new Application_Form_frmApplications($level,array('id' => 'applicationsForm'));
		
		if (!empty($_POST) && $this->view->form->isValid($_POST)) {
           $this->view->formSucces = 1;
           $this->view->department = $this->_request->getPost("department");
           
        } elseif ($_POST) {
            $form->populate($_POST);
        }
	}
	
	//functie care preia informatii din index/raportinternet
	function reportinternetAction()
	{
		
		
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];
		
	   $descriptionMonitor = $db->getMenu($role['access'],"m");
	   $description1Activity = $db->getMenu($role['access'],"a");			

	   $this->view->reportMonitor = $descriptionMonitor;
	   $this->view->reportActivity = $description1Activity;		
		
		/*  Part for the organigram filters from db*/
		$status = array();
		$um = array();
		$dm = array();
		$temp_um = array();
		$temp_dm = array();
		$deptbanned = array();

		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;	
						
		$departments = $db->getAllDepartments();
	    $usersToDepartments = $db->getAllUsersFromDepartments();
		$currentUser = $db->getUserDetails($userInfo->id);
		
		$temp_dm = explode("/",$currentUser['departments_monitorized']);
		$temp_um = explode("/",$currentUser['users']);
			
		for($i=0;$i<count($temp_um);$i++)
		{
			$um[$temp_um[$i]] = $temp_um[$i];
		}
		
		for($i=0;$i<count($temp_dm);$i++)
		{
			$dm[$temp_dm[$i]] = $temp_dm[$i];
		}

		foreach($departments as $department)
		{
			if (!in_array($department['id'], $dm))
			{
				$status[$department['id']] = $department['id'];
			}
		}

		foreach($status as $item)
		{
			$deptbanned[$item] = $item;

			$deptbanned = $this->getAllSubDepartments($deptbanned, $item);
		}			

		$this->view->departmentsMonitorized = $dm;
		$this->view->usersMonitorized = $um;
		$this->view->deptBanned = $deptbanned;
		 /* End Organigram */
		
	    $this->view->departments = $departments;
		$this->view->usersToDepartments = $usersToDepartments;
				
		$this->_helper->layout()->setLayout('rapoarte');
	}
	
	//functie care preia informatii din index/raportchat
	function reportchatAction()
	{
		
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];
		
	   $descriptionMonitor = $db->getMenu($role['access'],"m");
	   $description1Activity = $db->getMenu($role['access'],"a");			

	   $this->view->reportMonitor = $descriptionMonitor;
	   $this->view->reportActivity = $description1Activity;		
		
		/*  Part for the organigram filters from db*/
		$status = array();
		$um = array();
		$dm = array();
		$temp_um = array();
		$temp_dm = array();
		$deptbanned = array();

		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;	
						
		$departments = $db->getAllDepartments();
	    $usersToDepartments = $db->getAllUsersFromDepartments();
		$currentUser = $db->getUserDetails($userInfo->id);
		
		$temp_dm = explode("/",$currentUser['departments_monitorized']);
		$temp_um = explode("/",$currentUser['users']);
			
		for($i=0;$i<count($temp_um);$i++)
		{
			$um[$temp_um[$i]] = $temp_um[$i];
		}
		
		for($i=0;$i<count($temp_dm);$i++)
		{
			$dm[$temp_dm[$i]] = $temp_dm[$i];
		}

		foreach($departments as $department)
		{
			if (!in_array($department['id'], $dm))
			{
				$status[$department['id']] = $department['id'];
			}
		}

		foreach($status as $item)
		{
			$deptbanned[$item] = $item;

			$deptbanned = $this->getAllSubDepartments($deptbanned, $item);
		}			

		$this->view->departmentsMonitorized = $dm;
		$this->view->usersMonitorized = $um;
		$this->view->deptBanned = $deptbanned;
		 /* End Organigram */
		
	    // echo "<pre>"; print_r($departments);die();echo "</pre>";
	    $this->view->departments = $departments;
		$this->view->usersToDepartments = $usersToDepartments;
				
		$this->_helper->layout()->setLayout('rapoarte');
	}
	
		//=======================================================Raport de Prezenta si Activitate==============
	//functie care preia informatii din index/raportroi
	function reportroiAction()
	{
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];
		
	   $descriptionMonitor = $db->getMenu($role['access'],"m");
	   $description1Activity = $db->getMenu($role['access'],"a");			

	   $this->view->reportMonitor = $descriptionMonitor;
	   $this->view->reportActivity = $description1Activity;		
		
		/*  Part for the organigram filters from db*/
		$status = array();
		$um = array();
		$dm = array();
		$temp_um = array();
		$temp_dm = array();
		$deptbanned = array();

		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;	
						
		$departments = $db->getAllDepartments();
	    $usersToDepartments = $db->getAllUsersFromDepartments();
		$currentUser = $db->getUserDetails($userInfo->id);
		
		$temp_dm = explode("/",$currentUser['departments_monitorized']);
		$temp_um = explode("/",$currentUser['users']);
			
		for($i=0;$i<count($temp_um);$i++)
		{
			$um[$temp_um[$i]] = $temp_um[$i];
		}
		
		for($i=0;$i<count($temp_dm);$i++)
		{
			$dm[$temp_dm[$i]] = $temp_dm[$i];
		}

		foreach($departments as $department)
		{
			if (!in_array($department['id'], $dm))
			{
				$status[$department['id']] = $department['id'];
			}
		}

		foreach($status as $item)
		{
			$deptbanned[$item] = $item;

			$deptbanned = $this->getAllSubDepartments($deptbanned, $item);
		}			

		$this->view->departmentsMonitorized = $dm;
		$this->view->usersMonitorized = $um;
		$this->view->deptBanned = $deptbanned;
		 /* End Organigram */
		
	    $this->view->departments = $departments;
		$this->view->usersToDepartments = $usersToDepartments;
				
		$this->_helper->layout()->setLayout('rapoarte');
		
		
	}
	
	
	//functie care preia informatii din index/raportalerte
	function reportalertsAction()
	{
		$this->_helper->layout()->setLayout('emailalertslayout');
				
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];
		
	   $descriptionMonitor = $db->getMenu($role['access'],"m");
	   $description1Activity = $db->getMenu($role['access'],"a");			

	   $this->view->reportMonitor = $descriptionMonitor;
	   $this->view->reportActivity = $description1Activity;		
		
		/*  Part for the organigram filters from db*/
		$status = array();
		$um = array();
		$dm = array();
		$temp_um = array();
		$temp_dm = array();
		$deptbanned = array();

		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;	
						
		$departments = $db->getAllDepartments();
	    $usersToDepartments = $db->getAllUsersFromDepartments();
		$currentUser = $db->getUserDetails($userInfo->id);
		
		$temp_dm = explode("/",$currentUser['departments_monitorized']);
		$temp_um = explode("/",$currentUser['users']);
			
		for($i=0;$i<count($temp_um);$i++)
		{
			$um[$temp_um[$i]] = $temp_um[$i];
		}
		
		for($i=0;$i<count($temp_dm);$i++)
		{
			$dm[$temp_dm[$i]] = $temp_dm[$i];
		}

		foreach($departments as $department)
		{
			if (!in_array($department['id'], $dm))
			{
				$status[$department['id']] = $department['id'];
			}
		}

		foreach($status as $item)
		{
			$deptbanned[$item] = $item;

			$deptbanned = $this->getAllSubDepartments($deptbanned, $item);
		}			

		$this->view->departmentsMonitorized = $dm;
		$this->view->usersMonitorized = $um;
		$this->view->deptBanned = $deptbanned;
		 /* End Organigram */
		
	    $this->view->departments = $departments;
		$this->view->usersToDepartments = $usersToDepartments;	
		
	}

	function editreportalertsAction()
	{
		$this->_helper->layout()->setLayout('editemailalertslayout');
				
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
		 
		$id = $this->getRequest()->getParam('id');
		if(empty($id))
			$this->_redirect('index/emails');
		
		$email = $db->getReportAlertById($id);
		
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];

		$rights = $db->getRights($role['access'],'emails');
		$this->view->rights = $rights;
		//echo "<pre>";print_r($rights);echo "</pre>";die();
		
	   $descriptionMonitor = $db->getMenu($role['access'],"m");
	   $description1Activity = $db->getMenu($role['access'],"a");			

	   $this->view->reportMonitor = $descriptionMonitor;
	   $this->view->reportActivity = $description1Activity;		
		
		/*  Part for the organigram filters from db*/
		$status = array();
		$um = array();
		$dm = array();
		$temp_um = array();
		$temp_dm = array();
		$deptbanned = array();

		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;	
						
		$departments = $db->getAllDepartments();
	    $usersToDepartments = $db->getAllUsersFromDepartments();
		$currentUser = $db->getUserDetails($userInfo->id);
		
		$temp_dm = explode("/",$currentUser['departments_monitorized']);
		$temp_um = explode("/",$currentUser['users']);
			
		for($i=0;$i<count($temp_um);$i++)
		{
			$um[$temp_um[$i]] = $temp_um[$i];
		}
		
		for($i=0;$i<count($temp_dm);$i++)
		{
			$dm[$temp_dm[$i]] = $temp_dm[$i];
		}

		foreach($departments as $department)
		{
			if (!in_array($department['id'], $dm))
			{
				$status[$department['id']] = $department['id'];
			}
		}

		foreach($status as $item)
		{
			$deptbanned[$item] = $item;

			$deptbanned = $this->getAllSubDepartments($deptbanned, $item);
		}			

		$this->view->departmentsMonitorized = $dm;
		$this->view->usersMonitorized = $um;
		$this->view->deptBanned = $deptbanned;
		 /* End Organigram */
		
	    $this->view->departments = $departments;
		$this->view->usersToDepartments = $usersToDepartments;	
		$this->view->email = $email;
		
	}
	
	function editreportemailAction()
	{
		$this->_helper->layout()->setLayout('editemailreportlayout');
				
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
		 
		$id = $this->getRequest()->getParam('id');
		if(empty($id))
			$this->_redirect('index/emails');
		
		$email = $db->getReportAlertById($id);
		
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];

		$rights = $db->getRights($role['access'],'emails');
		$this->view->rights = $rights;
		//echo "<pre>";print_r($rights);echo "</pre>";die();
		
	   $descriptionMonitor = $db->getMenu($role['access'],"m");
	   $description1Activity = $db->getMenu($role['access'],"a");			

	   $this->view->reportMonitor = $descriptionMonitor;
	   $this->view->reportActivity = $description1Activity;		
	   
	   $rightsReportEmail = $db->getRights($role['access'],'report_email');
		
	   if($rightsReportEmail == 'nr')
	   {
	   	  $this->_redirect('index/index');
	   }
	   //echo "<pre>";print_r($rightsReportEmail);echo "</pre>";die();
	   
		/*  Part for the organigram filters from db*/
		$status = array();
		$um = array();
		$dm = array();
		$temp_um = array();
		$temp_dm = array();
		$deptbanned = array();

		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;	
						
		$departments = $db->getAllDepartments();
	    $usersToDepartments = $db->getAllUsersFromDepartments();
		$currentUser = $db->getUserDetails($userInfo->id);
		
		$temp_dm = explode("/",$currentUser['departments_monitorized']);
		$temp_um = explode("/",$currentUser['users']);
			
		for($i=0;$i<count($temp_um);$i++)
		{
			$um[$temp_um[$i]] = $temp_um[$i];
		}
		
		for($i=0;$i<count($temp_dm);$i++)
		{
			$dm[$temp_dm[$i]] = $temp_dm[$i];
		}

		foreach($departments as $department)
		{
			if (!in_array($department['id'], $dm))
			{
				$status[$department['id']] = $department['id'];
			}
		}

		foreach($status as $item)
		{
			$deptbanned[$item] = $item;

			$deptbanned = $this->getAllSubDepartments($deptbanned, $item);
		}			

		$this->view->departmentsMonitorized = $dm;
		$this->view->usersMonitorized = $um;
		$this->view->deptBanned = $deptbanned;
		 /* End Organigram */
		
		$reports = $db->getAllReports();
		
	    $this->view->departments = $departments;
		$this->view->usersToDepartments = $usersToDepartments;
		$this->view->reports = $reports;	
		$this->view->email = $email;
		
	}	
	
	//functie care preia informatii din index/raportperformanta
	function reportperformanceAction()
	{
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];
		
	   $descriptionMonitor = $db->getMenu($role['access'],"m");
	   $description1Activity = $db->getMenu($role['access'],"a");			

	   $this->view->reportMonitor = $descriptionMonitor;
	   $this->view->reportActivity = $description1Activity;	
	   	
	   $this->_helper->layout()->setLayout('reportperformancelayout');
		
		/*  Part for the organigram filters from db*/
		$status = array();
		$um = array();
		$dm = array();
		$temp_um = array();
		$temp_dm = array();
		$deptbanned = array();

		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;	
						
		$departments = $db->getAllDepartments();
	    $usersToDepartments = $db->getAllUsersFromDepartments();
		$currentUser = $db->getUserDetails($userInfo->id);
		
		$temp_dm = explode("/",$currentUser['departments_monitorized']);
		$temp_um = explode("/",$currentUser['users']);
			
		for($i=0;$i<count($temp_um);$i++)
		{
			$um[$temp_um[$i]] = $temp_um[$i];
		}
		
		for($i=0;$i<count($temp_dm);$i++)
		{
			$dm[$temp_dm[$i]] = $temp_dm[$i];
		}

		foreach($departments as $department)
		{
			if (!in_array($department['id'], $dm))
			{
				$status[$department['id']] = $department['id'];
			}
		}

		foreach($status as $item)
		{
			$deptbanned[$item] = $item;

			$deptbanned = $this->getAllSubDepartments($deptbanned, $item);
		}			

		$this->view->departmentsMonitorized = $dm;
		$this->view->usersMonitorized = $um;
		$this->view->deptBanned = $deptbanned;
		 /* End Organigram */
		
	    $this->view->departments = $departments;
		$this->view->usersToDepartments = $usersToDepartments;		
	}
	
	
	//functie care preia informatii din index/raporttopangajati
	function reporttopAction()
	{
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];
		
	   $descriptionMonitor = $db->getMenu($role['access'],"m");
	   $description1Activity = $db->getMenu($role['access'],"a");			

	   $this->view->reportMonitor = $descriptionMonitor;
	   $this->view->reportActivity = $description1Activity;		
	   
		$this->_helper->layout()->setLayout('rapoarte');
		
		/*  Part for the organigram filters from db*/
		$status = array();
		$um = array();
		$dm = array();
		$temp_um = array();
		$temp_dm = array();
		$deptbanned = array();

		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;	
						
		$departments = $db->getAllDepartments();
	    $usersToDepartments = $db->getAllUsersFromDepartments();
		$currentUser = $db->getUserDetails($userInfo->id);
		
		$temp_dm = explode("/",$currentUser['departments_monitorized']);
		$temp_um = explode("/",$currentUser['users']);
			
		for($i=0;$i<count($temp_um);$i++)
		{
			$um[$temp_um[$i]] = $temp_um[$i];
		}
		
		for($i=0;$i<count($temp_dm);$i++)
		{
			$dm[$temp_dm[$i]] = $temp_dm[$i];
		}

		foreach($departments as $department)
		{
			if (!in_array($department['id'], $dm))
			{
				$status[$department['id']] = $department['id'];
			}
		}

		foreach($status as $item)
		{
			$deptbanned[$item] = $item;

			$deptbanned = $this->getAllSubDepartments($deptbanned, $item);
		}			

		$this->view->departmentsMonitorized = $dm;
		$this->view->usersMonitorized = $um;
		$this->view->deptBanned = $deptbanned;
		 /* End Organigram */
		
	    $this->view->departments = $departments;
		$this->view->usersToDepartments = $usersToDepartments;		
	}
	
	//functie care preia informatii din index/raportpontaj
	function reporttimekeepingAction()
	{
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];
		
	   $descriptionMonitor = $db->getMenu($role['access'],"m");
	   $description1Activity = $db->getMenu($role['access'],"a");			

	   $this->view->reportMonitor = $descriptionMonitor;
	   $this->view->reportActivity = $description1Activity;	
	   	
		$this->_helper->layout()->setLayout('rapoarte');
		
		/*  Part for the organigram filters from db*/
		$status = array();
		$um = array();
		$dm = array();
		$temp_um = array();
		$temp_dm = array();
		$deptbanned = array();

		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;	
						
		$departments = $db->getAllDepartments();
	    $usersToDepartments = $db->getAllUsersFromDepartments();
		$currentUser = $db->getUserDetails($userInfo->id);
		
		$temp_dm = explode("/",$currentUser['departments_monitorized']);
		$temp_um = explode("/",$currentUser['users']);
			
		for($i=0;$i<count($temp_um);$i++)
		{
			$um[$temp_um[$i]] = $temp_um[$i];
		}
		
		for($i=0;$i<count($temp_dm);$i++)
		{
			$dm[$temp_dm[$i]] = $temp_dm[$i];
		}

		foreach($departments as $department)
		{
			if (!in_array($department['id'], $dm))
			{
				$status[$department['id']] = $department['id'];
			}
		}

		foreach($status as $item)
		{
			$deptbanned[$item] = $item;

			$deptbanned = $this->getAllSubDepartments($deptbanned, $item);
		}			

		$this->view->departmentsMonitorized = $dm;
		$this->view->usersMonitorized = $um;
		$this->view->deptBanned = $deptbanned;
		/* End organigram filters */
		
	    $this->view->departments = $departments;
		$this->view->usersToDepartments = $usersToDepartments;
				
	}
	
	//functie care preia informatii din index/raportactivitate
	function reportactivityAction()
	{
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];
		
	   $descriptionMonitor = $db->getMenu($role['access'],"m");
	   $description1Activity = $db->getMenu($role['access'],"a");			

	    $this->view->reportMonitor = $descriptionMonitor;
	    $this->view->reportActivity = $description1Activity;		
	    
		$this->_helper->layout()->setLayout('rapoarte');
		
		/*  Part for the organigram filters from db*/
		$status = array();
		$um = array();
		$dm = array();
		$temp_um = array();
		$temp_dm = array();
		$deptbanned = array();

		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;	
						
		$departments = $db->getAllDepartments();
	    $usersToDepartments = $db->getAllUsersFromDepartments();
		$currentUser = $db->getUserDetails($userInfo->id);
		
		$temp_dm = explode("/",$currentUser['departments_monitorized']);
		$temp_um = explode("/",$currentUser['users']);
			
		for($i=0;$i<count($temp_um);$i++)
		{
			$um[$temp_um[$i]] = $temp_um[$i];
		}
		
		for($i=0;$i<count($temp_dm);$i++)
		{
			$dm[$temp_dm[$i]] = $temp_dm[$i];
		}

		foreach($departments as $department)
		{
			if (!in_array($department['id'], $dm))
			{
				$status[$department['id']] = $department['id'];
			}
		}

		foreach($status as $item)
		{
			$deptbanned[$item] = $item;

			$deptbanned = $this->getAllSubDepartments($deptbanned, $item);
		}			

		$this->view->departmentsMonitorized = $dm;
		$this->view->usersMonitorized = $um;
		$this->view->deptBanned = $deptbanned;
		/* End organigram filters */
		
	    $this->view->departments = $departments;
		$this->view->usersToDepartments = $usersToDepartments;		
	}
    
    function reportAction() {
        //$this->getHelper('layout')->disableLayout();
        //$this->getHelper('viewRenderer')->setNoRender();
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];
		
	   $descriptionMonitor = $db->getMenu($role['access'],"m");
	   $description1Activity = $db->getMenu($role['access'],"a");			

	   $this->view->reportMonitor = $descriptionMonitor;
	   $this->view->reportActivity = $description1Activity;
	   		
/*  Part for the organigram filters from db*/
		$status = array();
		$um = array();
		$dm = array();
		$temp_um = array();
		$temp_dm = array();
		$deptbanned = array();

		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;	
						
		$departments = $db->getAllDepartments();
	    $usersToDepartments = $db->getAllUsersFromDepartments();
		$currentUser = $db->getUserDetails($userInfo->id);
		
		$temp_dm = explode("/",$currentUser['departments_monitorized']);
		$temp_um = explode("/",$currentUser['users']);
			
		for($i=0;$i<count($temp_um);$i++)
		{
			$um[$temp_um[$i]] = $temp_um[$i];
		}
		
		for($i=0;$i<count($temp_dm);$i++)
		{
			$dm[$temp_dm[$i]] = $temp_dm[$i];
		}

		foreach($departments as $department)
		{
			if (!in_array($department['id'], $dm))
			{
				$status[$department['id']] = $department['id'];
			}
		}

		foreach($status as $item)
		{
			$deptbanned[$item] = $item;

			$deptbanned = $this->getAllSubDepartments($deptbanned, $item);
		}			

		$this->view->departmentsMonitorized = $dm;
		$this->view->usersMonitorized = $um;
		$this->view->deptBanned = $deptbanned;
		/* End organigram filters */
		
	    $this->view->departments = $departments;
		$this->view->usersToDepartments = $usersToDepartments;
		        
        $reportId = $this->getRequest()->getParam("departmentID");
        $a = $this->getRequest()->getParams();
        
        if(empty($reportId))
            $this->_redirect('/index/index');
        
        $arrHeader = array("","Ianuarie", "Februarie", "Martie", "Aprilie", "Mai", "Iunie", "Iulie", "August");
        if($reportId == 1) {
            $arrData[] = array("Ore utilizare PC" ,92, 77, 60, 61, 92, 77, 60, 61);
            $arrData[] = array("Ore online" ,58, 60, 38, 96, 58, 60, 38, 96);
        } else if ($reportId == 2) {
            $arrData[] = array("Ore utilizare PC" ,10, 57, 10, 91, 92, 100, 60, 61);
            $arrData[] = array("Ore online" ,5, 50, 5, 86, 58, 60, 38, 6);
        }
        
        $mdExcel = new Application_Model_excel("Export");

        if (!empty($array)) {
            $mdExcel->addHeader($arrHeader);
        }
        //$array = $this->renault->getPAPasshoudersNieuwe();

        $mdExcel->addRows($arrData);

        ob_end_clean();
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header("Content-Type: application/x-msexcel; name=\"export.xls\"");
        header("Content-Disposition: inline; filename=\"export.xls\"");
        $this->excel->downloadNoSave();
        
    }

	function reportemailAction()
	{
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];
		
	   $descriptionMonitor = $db->getMenu($role['access'],"m");
	   $description1Activity = $db->getMenu($role['access'],"a");			

	   $this->view->reportMonitor = $descriptionMonitor;
	   $this->view->reportActivity = $description1Activity;		
	   
		$this->_helper->layout()->setLayout('emailreportlayout');
		
/*  Part for the organigram filters from db*/
		$status = array();
		$um = array();
		$dm = array();
		$temp_um = array();
		$temp_dm = array();
		$deptbanned = array();

		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;	
						
		$departments = $db->getAllDepartments();
	    $usersToDepartments = $db->getAllUsersFromDepartments();
		$currentUser = $db->getUserDetails($userInfo->id);
		
		$temp_dm = explode("/",$currentUser['departments_monitorized']);
		$temp_um = explode("/",$currentUser['users']);
			
		for($i=0;$i<count($temp_um);$i++)
		{
			$um[$temp_um[$i]] = $temp_um[$i];
		}
		
		for($i=0;$i<count($temp_dm);$i++)
		{
			$dm[$temp_dm[$i]] = $temp_dm[$i];
		}

		foreach($departments as $department)
		{
			if (!in_array($department['id'], $dm))
			{
				$status[$department['id']] = $department['id'];
			}
		}

		foreach($status as $item)
		{
			$deptbanned[$item] = $item;

			$deptbanned = $this->getAllSubDepartments($deptbanned, $item);
		}			

		$this->view->departmentsMonitorized = $dm;
		$this->view->usersMonitorized = $um;
		$this->view->deptBanned = $deptbanned;
		/* End organigram filters */
		
		$reports = $db->getAllReports();
	    // echo "<pre>"; print_r($reports);die();echo "</pre>";
	    $this->view->departments = $departments;
		$this->view->usersToDepartments = $usersToDepartments;			
		$this->view->reports = $reports;	
		
		
	}
	
	function parseemailreportAction()
	{
		$db = new Application_Model_Database();
				
	    $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
		$temp = array();
		
		//echo "<pre>";print_r($_POST);echo "</pre>";die();
		
		$temp['users'] = $this->parseUsersFromAlert($_POST);
		$temp['departments'] = $this->parseDepartmentsFromAlert($_POST);
		$temp['destination'] = $_POST['alertdestination1'];
		$temp['reports'] = $this->parseReportsFromAlert($_POST);
		$temp['frequency'] = $_POST['reportfrequency'];
		$temp['name'] = $_POST['reportname'];
		$temp['type'] = "re";					

		$db->addReportAlert($temp);
		$this->_redirect('index/reportemail');
		//echo "<pre>"; var_dump($temp); echo"</pre>";die();			
		
	}


	function userrightsAction(){
		
		$db = new Application_Model_Database();
		
		$result = $db->login();
	//	$result = "";
		if(!empty($result))
		{
			//$ProductivoSession = new Zend_Session_Namespace('ProductivoSession');
			//$ProductivoSession->user = "myusername";
			$this->view->user = $result;
		}
		else
			$this->_redirect('index/index');
	}
	
	function departmentsAction()
	{
		
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];
				
		$this->_helper->layout()->setLayout('departmentslayout');	
		$parents = array();	
		
	    $rights = $db->getRights($role['access'],'departments');
		
		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;		
		
		$rows = $db->getAllDepartments();
		
		foreach($rows as $row)
		{
			if($row['parent'] == 0)
				$parents[$row['parent']] = 'Radacina';
			$parents[$row['id']] = $row['name'];
		}
		
		//print_r($parents);die();
		
		$this->view->rights = $rights;
		$this->view->parents = $parents;
		$this->view->rows = $rows;
	}
	
	function rolemanagementAction()
	{
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];
		
		$result = $db->getAccessLevel();
		
		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;
				
		$rolesSidebar = $db->getAccessLevel();
		$this->view->rolesSidebar = $rolesSidebar;
		
		$this->view->roles = $result;
		
		$rights = $db->getRights($role['access'],'access_accounts');
		
		$this->view->rights = $rights;
		
	}
	function accessaccountsAction()
	{
		$i = 1;
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role_login = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role_login['access'];
		
		 $description = $db->getMenu($role_login['access'],"u");
		 
		$this->view->menu = $description;		
		$rows = $db->getAllUsers();
		$roles[] = array();
		
		$rolesSidebar = $db->getAccessLevel();
		$this->view->rolesSidebar = $rolesSidebar;
		
		$rights = $db->getRights($role_login['access'],'access_accounts');		
		
		foreach($rows as $row)
		{
			$role = $db->getRole($row['access_level']);
			$roles[$i] = $role['access'];
			$i++;
		}
		
		$this->view->rows = $rows;	
		$this->view->roles = $roles;

		
		$this->view->rights = $rights;
		
	
	}
	
	function emailsAction()
	{
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		
	    $this->_helper->layout()->setLayout('emaillayout');
			
	    $db = new Application_Model_Database();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];

		$rights = $db->getRights($role['access'],'emails');
		$this->view->rights = $rights;
		
		$reportalertsRight = $db->getRights($role['access'],'report_alerts');
		$reportemailsRight = $db->getRights($role['access'],'report_alerts');
		$this->view->reportalertsRight = $reportalertsRight;
		$this->view->reportemailsRight = $reportemailsRight;
		
		
		$emails = $db->getReportAlertsByType('ra');
		$this->view->emails = $emails;
		
		//echo "<pre>";print_r($reportalertsRight);echo "</pre>";die();
				
		//$this->_redirect('index/home');
		
		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;	
			
		$rolesSidebar = $db->getAccessLevel();
		$this->view->rolesSidebar = $rolesSidebar;
	}

	function viewreportemailsAction()
	{
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		
	    $this->_helper->layout()->setLayout('emaillayout');
			
	    $db = new Application_Model_Database();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];

		$rights = $db->getRights($role['access'],'emails');
		$this->view->rights = $rights;
		
		$emails = $db->getReportAlertsByType('re');
		$this->view->emails = $emails;
		
		//echo "<pre>";print_r($emails);echo "</pre>";die();
				
		//$this->_redirect('index/home');
		
		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;	
			
	}	
	
	function emailaddressAction()
	{
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    		
			
	    $db = new Application_Model_Database();
	    $dbAddress = new Application_Table_dbEmailAddress();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];

		$rights = $db->getRights($role['access'],'emailaddress');
		$this->view->rights = $rights;

		//echo "<pre>";print_r($reportalertsRight);echo "</pre>";die();
				
		//$this->_redirect('index/home');
		
		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;	
			
		$rolesSidebar = $db->getAccessLevel();
		$this->view->rolesSidebar = $rolesSidebar;
		
		$currentAddress = $dbAddress->getAddress();
						
		$form = new Application_Form_frmEmailAddress(array('id' => 'EmailAddress'));
		
		if(!empty($currentAddress))
		{
			$form->email->setValue($currentAddress['email']);
			$form->password->setValue($currentAddress['password']);
			$form->server->setValue($currentAddress['server']);
			$form->port->setValue($currentAddress['port']);
		}		
		
        $data = array();	
        if (!empty($_POST) && $form->isValid($_POST)) {
           $this->view->formSucces = 1;

           $arrData = $this->_request->getPost();
           
           $data['id'] = 1;
           $data['email'] = $arrData['email'];
		   $data['password'] = $arrData['password'];    
		   $data['server'] = $arrData['server'];
		   $data['port'] = $arrData['port'];			     
		   
		   if(empty($currentAddress))
		   {
		 	  $dbAddress->addAddress($data);
		   }
		   else
		   {
		   	  $dbAddress->updateAddress($data);
		   }
           
		   $this->_redirect('index/emailaddress');


        } elseif ($_POST) {
            $form->populate($_POST);
        }
		else
			$form->populate(array());
        
    	$this->view->form = $form;		
		
		
	}	

	function categoriesAction()
	{	
		$temp[] = array();
		$tempApplications[] = array();
		$tempChats[] = array();
		$verify = array();
		$verifyApplication = array();
		$i = 0;
		$categoriesNumber = 0;
		$numberRowsPerPage = 20;
		
		$notSet = array('Google Chrome'     => 'Google Chrome',
						'Yahoo! Messenger'  => 'Yahoo! Messenger',
						'Internet Explorer' => 'Internet Explorer',
						'Opera'             => 'Opera',
						'Safari'            => 'Safari',
						'Fireforx'          => 'Firefox');
				
		$this->_helper->layout()->setLayout('categorylayout');
		
        if (!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		
		$db = new Application_Model_Database();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];
		
		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;		
		
		$filtru1 = $this->getRequest()->getParam('filtru1');
		$filtru2 = $this->getRequest()->getParam('filtru2');
		$filtru3 = $this->getRequest()->getParam('filtru3');
		
		$selectedCateg = $this->getRequest()->getParam('categ');
		
		$rights = $db->getRights($role['access'],'categories');
		
		$this->view->rights = $rights;
		$this->view->notSet = $notSet;
		
		$page = $this->getRequest()->getParam('p');
		
		//echo (Zend_Controller_Front::getInstance()->getRequest()->getRequestUri());die();
		
		// no category or filter selected (all selected case)
		if (empty($selectedCateg) && empty($filtru1) && empty($filtru2) && empty($filtru3))
		{
			/*$resultSites       = $db->getInternetSites();
			$resultApplication = $db->getApplicationForCategories();
			$resultChat        = $db->getChatForCategories();		
				
			foreach ($resultChat as $chat)
			{		
				if ($chat['reWnd'] != 'Yahoo! Messenger' && $chat['reWnd'] != 'Contact Information Card')
				{
					$tempChats[$i]['reUser'] = $chat['reUser'];
					$tempChats[$i]['category'] = 'chat';
					$tempChats[$i]['windowName'] = $chat['reWnd'];
					$tempChats[$i]['type'] = 'unclassified';
					$tempChats[$i]['name'] = $chat['reWnd'];
					
					$i++;
				}
			}				
			
			$i = 0;				
			foreach($resultSites as $category)
			{
				$httpData = parse_url($category['rhaeURL']);
					
				if(!empty($httpData['host']))
					if(empty($verify[$httpData['host']]))
					{
						$temp[$i]['reUser'] = $category['reUser'];
						$temp[$i]['category'] = 'site';
						$temp[$i]['windowName'] = $category['reWnd'];
						$temp[$i]['type'] = 'unclassified';
						$temp[$i]['name'] = $httpData['host'];
					
						$i++;
					
						$verify[$httpData['host']] = '1';
					}
			}
			
			$i = 0;
			foreach($resultApplication as $application)
			{
				if (empty($verifyApplication[$application['reProcess']]) && (!empty($application['reProcess'])))
				{
					$tempApplications[$i]['reUser'] = $application['reUser'];
					$tempApplications[$i]['category'] = 'application';
					
					if (!empty($application['reWnd']))
						$tempApplications[$i]['windowName'] = $application['reWnd'];
					else
						$tempApplications[$i]['windowName'] = ' ';	
					
					$tempApplications[$i]['type'] = 'unclassified';
					$tempApplications[$i]['name'] = $application['reProcess'];
					
					$i++;
						
					$verifyApplication[$application['reProcess']] = '1';
				}
			}
							
			if (!empty($temp[0]))
				$db->insertSitesInCategory($temp);
			
			if (!empty($tempApplications[0]))	
				$db->insertApplicationsInCategory($tempApplications);
			
			if (!empty($tempChats[0]))	
				$db->insertChatsInCategory($tempChats);				
			*/	
			
			$categoriesNumber = $db->getCategoriesNumber();
			
			if(floor($categoriesNumber/$numberRowsPerPage) < $page || $page <=0 || empty($page))
				$this->_redirect('index/categories/p/1');
			
			$result = $db->getNCategorieS($page,$numberRowsPerPage);
			
			//echo "<pre>"; print_r($numberCategories); echo "</pre>";die();
		}
		else
		{
			
			//echo (Zend_Controller_Front::getInstance()->getRequest()->getRequestUri());die();
			$categoriesNumber = $db->getCategoriesNumberFilters($selectedCateg, $filtru1, $filtru2, $filtru3);
			
			//daca trebuie sa afisam doar o singura pagina , nu mai punem limitare in query => $flag= 0 	
			if($categoriesNumber < $numberRowsPerPage)
			{
				$pageNumberTemp = 1;
				$flag = 0;
			}
			else
			{
				$pageNumberTemp = floor($categoriesNumber/$numberRowsPerPage);
				$flag = 1;
			}
			if(empty($page))
				$page = 1;
			if($pageNumberTemp < $page || $page <=0)
				$this->_redirect('index/categories/p/1');		

			
			$result = $db->getFilterdCategories($selectedCateg, $filtru1, $filtru2, $filtru3,$page,$numberRowsPerPage,$flag);
			//echo "<pre>";print_r($page);echo "</pre>";die();
		}
		
		
		$uri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
		$pos1 = strrpos($uri, "/p/");
		$extract1 = substr($uri,0,$pos1);
		
		//if we have /p in our string we remove it, else we just substract untill we find /categories
		if(strpos($uri,"/p/")== true)
		{
			$pos2 = strrpos($extract1, "/categories");
			$extract2 = substr($extract1,$pos2,strlen($extract1));	
		}else
			{
					$pos2 = strrpos($uri, "/categories");
					$extract2 = substr($uri,$pos2,strlen($uri));
					$extract2 = substr($extract2, 0, -1);
					
			}		
		
		$this->view->categories = $result;
		$this->view->currentPage = $page; 
		$this->view->totalPages = floor($categoriesNumber/$numberRowsPerPage) == 0 ? 1 : floor($categoriesNumber/$numberRowsPerPage);
		$this->view->rowsPerPage = $numberRowsPerPage;
		$this->view->currentURL = $extract2;
	}

	/*
	 * function that lists all sites from categories
	 */
	function categorysiteAction()
	{
		$this->_helper->layout()->setLayout('categorylayout');
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];
		
		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;	
				
		$data = array();
		
		$rights = $db->getRights($role['access'],'categories');
		$this->view->rights = $rights;
		
		$result = $db->getCategoryBytypes('site');
		
		$this->view->sites = $result;
		
		/*$form = new Application_Form_frmCategory(array('id' => 'addCategoryForm'));
        
        if (!empty($_POST) && $form->isValid($_POST)) {
           $this->view->formSucces = 1;

           $arrData = $this->_request->getPost();
           $data['name'] = $arrData['name'];
           $data['type'] = $arrData['type'];
		   $data['category'] = 'site';
		          
		  $db->addCategory($data);
		  
          // echo $data['name']." ".$data['type'];die();

        } elseif ($_POST) {
            $form->populate($_POST);
        }
        
    	$this->view->form = $form;	*/	
	}
	
	
	/*
	 * function that adds to the database a application as productive, unproductive or unclassified
	 */	
	function categoryapplicationAction()
	{		
		$this->_helper->layout()->setLayout('categorylayout');
		
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();

		$notSet = array(
			'Google Chrome' => 'Google Chrome',
			'Yahoo! Messenger'=> 'Yahoo! Messenger',
			'Internet Explorer' => 'Internet Explorer',
			'Opera' => 'Opera',
			'Safari' => 'Safari',
			'Fireforx' => 'Firefox'
		
		);
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];
		
		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;	
		
		$data = array();
		
		$this->_helper->layout()->setLayout('categorylayout');
		$db = new Application_Model_Database();
		
		$data = array();
		
		$rights = $db->getRights($role['access'],'categories');
		$this->view->rights = $rights;
		
		$result = $db->getCategoryBytypes('application');
		
		$this->view->applications = $result;
		$this->view->noSet = $notSet;
	}
	


	/*
	 * function that adds to the database a chat id as productive, unproductive or unclassified
	 */
	function categorychatAction()
	{
		$this->_helper->layout()->setLayout('categorylayout');
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];
		
		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;	
		
		$data = array();
		
		$this->_helper->layout()->setLayout('categorylayout');
		$db = new Application_Model_Database();
		
		$data = array();
		
		$rights = $db->getRights($role['access'],'categories');
		$this->view->rights = $rights;
		
		$result = $db->getCategoryBytypes('chat');
		
		$this->view->idchat = $result;	
	}	
		
	function editcategoryAction()
	{
		$this->_helper->layout()->setLayout('categorylayout');
		$data = array();
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);	
		$this->view->role = $role['access'];
		
		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;			
			
		
		$id = $this->getRequest()->getParam('id');
		$category = $this->getRequest()->getParam('category');
		//stripslashes()
		$details = $db->getCategoryDetails($id,$category);
		
		//print_r($details);die();
		$form = new Application_Form_frmCategory(array('id' => 'editCategoryForm'));
        if (!empty($_POST) && $form->isValid($_POST)) {
           $this->view->formSucces = 1;
           
           $arrData = $this->_request->getPost();
           $data['name'] = $details['name'];
		   $data['windowName'] = $details['windowName'];
           $data['type'] = $arrData['type'];
           $data['category'] = $category;
		   
           $db->updateCategory($id,$data);
		   $this->_redirect('index/categories');
		   
           
        } elseif ($_POST) {
            $form->populate($_POST);
        } else
        	$form->populate($details);
    	$this->view->form = $form;		
			
		
	}	
	/*
	 * function which receives a category selected for deletion and deletes it from the database
	 */
	function deleteselectedcategoryAction(){
		
		$this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
		//die("asd");
		$db = new Application_Model_Database();
		
		$id = $this->_request->getPost('category');
		
		//$response['id'] = $id;
		//$result = json_encode($response);
		
		$db->deleteSelectedCategory($id);
		return $this->_helper->json(true);
	}
	
	/*
	 * function which receives all the selected categories and deletes them from the database
	 */
	function deleteselectedcategoriesAction(){
		
		$this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
		//die("asd");
		$db = new Application_Model_Database();
		
		$categories = $this->_request->getPost('categories');
		
		//$response['id'] = $id;
		//$result = json_encode($response);
		
		
		$db->deleteSelectedCategories($categories);
		return $this->_helper->json(true);
		
	}
		
			
	/*
	 * function which receives a user selected for deletion and deletes it from the database
	 */
	function deleteselecteduserAction(){
		
		$this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
		//die("asd");
		$db = new Application_Model_Database();
		
		$id = $this->_request->getPost('user');
		
		$db->deleteUser($id);
		
		return $this->_helper->json(true);
	}
	
	/*
	 * function which receives all the selected user and deletes them from the database
	 */
	function deleteselectedusersAction(){
		
		$this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
		//die("asd");
		$db = new Application_Model_Database();
		
		$users = $this->_request->getPost('users');
		
		//$response['id'] = $id;
		//$result = json_encode($response);
		
		$db->deleteUsers($users);
		return $this->_helper->json(true);
		
	}
	
	/*
	 * function which receives the id of an user and shows his details
	 */
	function edituserAction()
	{
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];
		
		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;	
				
		$rolesSidebar = $db->getAccessLevel();
		$this->view->rolesSidebar = $rolesSidebar;
				
		$id = $this->getRequest()->getParam('id');
		$details = $db->getUserDetails($id);
		$temp = $db->getUserDetailsWithAccessID($id);
		$details['role'] = $temp['access_level'];
		
		/*  Organigrama */
				
		$departments = $db->getAllDepartments();
	    $usersToDepartments = $db->getAllUsersFromDepartments();	
				
	    $this->view->departments = $departments;
		$this->view->usersToDepartments = $usersToDepartments;		
		
		/* End Organigrama */
		
		//echo "<pre>";print_r($details);echo "</pre>";die();
		$form = new Application_Form_frmUser(array('id' => 'editUserForm'),'1');
		
		foreach($details as $key=>$value)
		{
			if( $key == 'users' )
			{
				
				$users = array();
				$users = explode("/",$value);
				$form->$key->setValue($users);				
			}
			
			if($key == 'departments_monitorized')
			{
				
				$departments = array();
				$departments = explode("/",$value);
				$form->$key->setValue($departments);				
			}
		}
		
		
		$form->username->setValue($details['username']);
		$form->name->setValue($details['name']);
		$form->email->setValue($details['email']);
		$form->role->setValue($details['role']);
		//echo "<pre>";print_r($details);echo "</pre>";die();
		
        
        if (!empty($_POST) && $form->isValid($_POST)) {
           $md5 = 0;
           $this->view->formSucces = 1;
           //$this->view->department = $this->_request->getPost("department");
           $arrData = $this->_request->getPost();
           $data['username'] = $arrData['username'];
           $data['email'] = $arrData['email'];
           $data['name'] = $arrData['name'];
		   
		   //if the user selected password modification , then we have to apply md5 to the password ( $md5 = 1)
		   if(!empty($arrData['password_check']))
           {
          		 $data['password'] = $arrData['password'];
				 $md5 = 1;
		   }
		   else
		   		$data['password'] = $details['password'];
				
		   $data['role'] = $arrData['role'];
		   if(!empty($arrData['users']))
				$data = $this->parseUsersID($data,$arrData['users']);
		   if(!empty($arrData['departments_monitorized']))
				$data = $this->parseDepartmentsID($data,$arrData['departments_monitorized']);	
		   //echo "<pre>";print_r($data);echo "</pre>";die();		   
           $db->updateUser($id,$data,$md5);
           $this->_redirect('index/accessaccounts');
        } elseif ($_POST) {
            $form->populate($_POST);
        }
    	$this->view->form = $form;		
		
	}
	
	function deleteselectedrolesAction(){
		
		$this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
		//die("asd");
		$db = new Application_Model_Database();
		
		$roles = $this->_request->getPost('roles');
		
		$db->deleteSelectedRoles($roles);		
		
	}	
	
	function deleteselectedroleAction(){
		
		$this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
		//die("asd");
		$db = new Application_Model_Database();
		
		$role = $this->_request->getPost('role');
		
		$db->deleteSelectedRole($role);		
		
	}	
	
	/*
	 *  Action for the adduser page 
	 *  Receives post information about new user and adds them to the database
	 */
	function adduserAction()
	{
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];
		
		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;	

		$rolesSidebar = $db->getAccessLevel();
		$this->view->rolesSidebar = $rolesSidebar;

		/*  Organigrama */
				
		$departments = $db->getAllDepartments();
	    $usersToDepartments = $db->getAllUsersFromDepartments();	
				
	    $this->view->departments = $departments;
		$this->view->usersToDepartments = $usersToDepartments;		
		
		/* End Organigrama */
				
		$form = new Application_Form_frmUser(array('id' => 'editUserForm'),'0');
        	
        if (!empty($_POST) && $form->isValid($_POST)) {
           $this->view->formSucces = 1;

           $arrData = $this->_request->getPost();
		   
		   //echo "<pre>";print_r($arrData);echo "</pre>";die();
		   
           $data['username'] = $arrData['username'];
           $data['email'] = $arrData['email'];
           $data['name'] = $arrData['name'];
		   $data['password'] = $arrData['password'];
  		   $data['access_level'] = $arrData['role'];
			if(!empty($arrData['users']))
				$data = $this->parseUsersID($data,$arrData['users']);
				
		   if(!empty($arrData['departments_monitorized']))
				$data = $this->parseDepartmentsID($data,$arrData['departments_monitorized']);	
				
					          
           $temp = $db->addUser($data);
		   if ($temp == 'true')
		   		$this->_redirect('index/accessaccounts');
		   else
		   		echo $temp;

        } elseif ($_POST) {
            $form->populate($_POST);
        }
        
    	$this->view->form = $form;
		
	}
	
	/*
	 *  Action for the addrole page 
	 *  Receives post information about new roles and adds them to the database
	 */
	function addroleAction(){
		$data = array();
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];
		
		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;
		
		$rolesSidebar = $db->getAccessLevel();
		$this->view->rolesSidebar = $rolesSidebar;		
		
		$form = new Application_Form_frmRoles(array('id' => 'addRole'));
		
		
		if (!empty($_POST) && $form->isValid($_POST)) {
           $this->view->formSucces = 1;

           $arrData = $this->_request->getPost();
		   
		   //echo "<pre>";print_r($arrData);echo "</pre>";die();
		   
		   if(!empty($arrData['reports']))
		  	 	$data = $this->formulateRolesReport($arrData['reports'], $data,false);
		   else
		   		$data = $this->formulateRolesReport(array(), $data,true);
				
		   if(!empty($arrData['departments']))
		 		  $data = $this->formulateRoleAdministration('departments', $arrData['departments'], $data,false);
		   else
		   		$data = $this->formulateRoleAdministration('departments', array(), $data,true);
				
		   if(!empty($arrData['access_accounts']))
		  		 $data = $this->formulateRoleAdministration('access_accounts', $arrData['access_accounts'], $data,false);
		   else
		   		 $data = $this->formulateRoleAdministration('access_accounts', array(), $data,true);
				 
		   if(!empty($arrData['role_management']))
		  		 $data = $this->formulateRoleAdministration('role_management', $arrData['role_management'], $data,false);
		   else
		   		$data = $this->formulateRoleAdministration('role_management', array(), $data,true);		 
				 
		   if(!empty($arrData['emails']))
		  	 	$data = $this->formulateRoleAdministration('emails', $arrData['emails'], $data,false);
		   else
		   		$data = $this->formulateRoleAdministration('emails', array(), $data,true);
		   if(!empty($arrData['filters']))
		  	 	$data = $this->formulateRoleAdministration('filters', $arrData['filters'], $data,false);
		   else
		   		$data = $this->formulateRoleAdministration('filters', array(), $data,true);				
		   if(!empty($arrData['access']))
		  		 $data['access'] = $arrData['access'];
				 
		   if(!empty($arrData['categories']))
		  		$data = $this->formulateRoleAdministration('categories', $arrData['categories'], $data,false);
		   else
		   		$data = $this->formulateRoleAdministration('categories', array(), $data,true);
				
		   if(!empty($arrData['productivo_clients']))
		  		$data = $this->formulateRoleAdministration('productivo_clients', $arrData['productivo_clients'], $data,false);
		   else
		   		$data = $this->formulateRoleAdministration('productivo_clients', array(), $data,true);

		   if(!empty($arrData['emailaddress']))
		  		$data = $this->formulateRoleAdministration('emailaddress', $arrData['emailaddress'], $data,false);
		   else
		   		$data = $this->formulateRoleAdministration('emailaddress', array(), $data,true);		   		
							 			
		 //  echo '<pre>'.print_r($data,true).'</pre>';die();
		   $db->addRole($data);
           $this->_redirect('index/rolemanagement');
        } elseif ($_POST) {
            $form->populate($_POST);
        }
        
    	$this->view->form = $form;		
	}

	function parseUsersID($data,$users)
	{
		$temp = '';
		foreach ($users as $user)
		{
			$temp =$temp.$user."/";
		}
		$temp[strlen($temp)-1] ='';
		$data['users'] = $temp;
		
		return $data;
	}
	
	function parseDepartmentsID($data,$departments)
	{
		$temp = '';
		foreach ($departments as $department)
		{
			$temp =$temp.$department."/";
		}
		$temp[strlen($temp)-1] ='';
		$data['departments_monitorized'] = $temp;
		
		return $data;
	}	

	/*
	 *  Action for the editrole page 
	 *  Receives post information about a specific role and updates the new information to the database
	 */
	function editroleAction()
	{
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];
		
		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;
				
		$reports = array();
		$details = array();
		$data = array();
		
		$rolesSidebar = $db->getAccessLevel();
		$this->view->rolesSidebar = $rolesSidebar;
				
		$form = new Application_Form_frmRoles(array('id' => 'editRole'));

		$id = $this->getRequest()->getParam('id');
		
		$details = $db->getAccessLevelById($id);
		
		foreach($details as $key=>$value)
		{
			if($value == 'r')
			{
				array_push($reports,$key);
			}
			elseif($value == 'nr')
				;
			elseif($key != 'id' && $key != 'access' && $key != 'users' && $key != 'departments_monitorized')
				{
					$temp = array();
					list($read,$add,$modify,$delete) = explode(',',$value,5);
					
					($read=='r')?array_push($temp,'r'):'';
					($add=='a')?array_push($temp,'a'):'';
					($modify=='m')?array_push($temp,'m'):'';
					($delete=='d')?array_push($temp,'d'):'';
	
					$form->$key->setValue($temp);
				}
		}

		$form->access->setValue($details['access']);
		//echo '<pre>'.print_r($data,true).'</pre>';die();
		
		$form->reports->setValue($reports);
		
		if (!empty($_POST) && $form->isValid($_POST)) {
           $this->view->formSucces = 1;

           $arrData = $this->_request->getPost();
			//echo '<pre>'.print_r($arrData,true).'</pre>';die();		   
		   if(!empty($arrData['reports']))
		  	 	$data = $this->formulateRolesReport($arrData['reports'], $data,false);
		   else
		   		$data = $this->formulateRolesReport(array(), $data,true);
				
		   if(!empty($arrData['departments']))
		 		  $data = $this->formulateRoleAdministration('departments', $arrData['departments'], $data,false);
		   else
		   		$data = $this->formulateRoleAdministration('departments', array(), $data,true);
				
		   if(!empty($arrData['access_accounts']))
		  		 $data = $this->formulateRoleAdministration('access_accounts', $arrData['access_accounts'], $data,false);
		   else
		   		 $data = $this->formulateRoleAdministration('access_accounts', array(), $data,true);
				 
		   if(!empty($arrData['role_management']))
		  		 $data = $this->formulateRoleAdministration('role_management', $arrData['role_management'], $data,false);
		   else
		   		$data = $this->formulateRoleAdministration('role_management', array(), $data,true);		 
		   if(!empty($arrData['filters']))
		  		 $data = $this->formulateRoleAdministration('filters', $arrData['filters'], $data,false);
		   else
		   		$data = $this->formulateRoleAdministration('filters', array(), $data,true);					 
		   if(!empty($arrData['emails']))
		  	 	$data = $this->formulateRoleAdministration('emails', $arrData['emails'], $data,false);
		   else
		   		$data = $this->formulateRoleAdministration('emails', array(), $data,true);
				
		   if(!empty($arrData['productivo_clients']))
		  	 	$data = $this->formulateRoleAdministration('productivo_clients', $arrData['productivo_clients'], $data,false);
		   else
		   		$data = $this->formulateRoleAdministration('productivo_clients', array(), $data,true);
				
		   if(!empty($arrData['access']))
		  		 $data['access'] = $arrData['access'];
		   if(!empty($arrData['categories']))
		  		$data = $this->formulateRoleAdministration('categories', $arrData['categories'], $data,false);
		   else
		   		$data = $this->formulateRoleAdministration('categories', array(), $data,true);

		   if(!empty($arrData['emailaddress']))
		  		$data = $this->formulateRoleAdministration('emailaddress', $arrData['emailaddress'], $data,false);
		   else
		   		$data = $this->formulateRoleAdministration('emailaddress', array(), $data,true);		   		
		   //echo '<pre>'.print_r($data,true).'</pre>';die();
		   
		   $db->updateRole($id,$data);
		   $this->_redirect('index/rolemanagement');
		  
        } elseif ($_POST) {
            $form->populate($_POST);
        }
        
    	$this->view->form = $form;				
	}

	
	/*
	 *  Returns an array with the rights for the reports
	 *  This array will be inserted in the database
	 */
	function formulateRolesReport($array,$result,$setNoRights)
	{	
		$db = new Application_Table_dbDescribeAccess();
		
		$reportsM = $db->getAllOfType("m");
		$reportsA = $db->getAllOfType("a");
		
		foreach($reportsM as $reportM)
		{
			$result[$reportM['name_access_level']] = "nr";
		}
		
		foreach($reportsA as $reportA)
		{
			$result[$reportA['name_access_level']] = "nr";
		}
		
		if($setNoRights == false)
		{
			for($i=0;$i<count($array);$i++)
			{
				$result[$array[$i]] = "r";
			}
		}
		
		return $result;
						
	}

	/*
	 *  Returns an array with the rights for an administration module
	 *  This array will be inserted in the database
	 */	
	function formulateRoleAdministration($column_name,$rights,$result,$setNoRights)
	{
		$tempRights = array("nr","na","nm","nd");
		
		if($setNoRights == false)
		{
			for($i=0;$i<count($rights);$i++)
			{

				$tempRights[0] = "r";
				if($rights[$i] == "a" )
					$tempRights[1] = "a";	
				if($rights[$i] == "m" )
					$tempRights[2] = "m";
				if($rights[$i] == "d" )
					$tempRights[3] = "d";									
			}
		}
		
		$result[$column_name] = $tempRights[0].",".$tempRights[1].",".$tempRights[2].",".$tempRights[3];
		
		return $result;
	}
	
	function adddepartmentAction()
	{
		$this->_helper->layout()->setLayout('departmentslayout');
		$ok = 0;				
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];
		
		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;
					
		$data = array();
		
		$form = new Application_Form_frmDepartment(array('id' => 'addDepartmentForm'),"adddepartment");
        
        if (!empty($_POST) && $form->isValid($_POST)) {
           $this->view->formSucces = 1;

           $arrData = $this->_request->getPost();
		  
           $data['name'] = $arrData['name'];
           $data['parent'] = $arrData['department'];
		   $data['working_hours'] = $arrData['working_hours'];
		   $data['cost_per_hour'] = $arrData['cost_per_hour'];
		   $data['day1_start'] = $arrData['day1_start'];
		   $data['day1_stop'] = $arrData['day1_stop'];
		   if(strtotime($data['day1_start']) > strtotime($data['day1_stop']))
		   		$ok = 1;
				
		   $data['day2_start'] = $arrData['day2_start'];
		   $data['day2_stop'] = $arrData['day2_stop'];
		   if(strtotime($data['day2_start']) > strtotime($data['day2_stop']))
		   		$ok = 1;	
					   
		   $data['day3_start'] = $arrData['day3_start'];
		   $data['day3_stop'] = $arrData['day3_stop'];
		   if(strtotime($data['day3_start']) > strtotime($data['day3_stop']))
		   		$ok = 1;		   
		   
		   $data['day4_start'] = $arrData['day4_start'];
		   $data['day4_stop'] = $arrData['day4_stop'];
		   if(strtotime($data['day4_start']) > strtotime($data['day4_stop']))
		   		$ok = 1;	
					   
		   $data['day5_start'] = $arrData['day5_start'];
		   $data['day5_stop'] = $arrData['day5_stop']; 
		   if(strtotime($data['day5_start']) > strtotime($data['day5_stop']))
		   		$ok = 1;		   
		   $data['day6_start'] = $arrData['day6_start'];
		   $data['day6_stop'] = $arrData['day6_stop'];
		   if(strtotime($data['day6_start']) > strtotime($data['day6_stop']))
		   		$ok = 1;
						   
		   $data['day7_start'] = $arrData['day7_start'];		   
		   $data['day7_stop'] = $arrData['day7_stop'];
		   if(strtotime($data['day7_start']) > strtotime($data['day7_stop']))
		   		$ok = 1;
		   
		   $data['break_lenght'] = $arrData['break_lenght'];
		   $data['payment'] = $arrData['payment'];	
		   //echo "<pre>"; print_r($data);echo "</pre>";die();
		   if($ok == 0){
			   $db->addDepartment($data);	
			   $this->_redirect('index/departments');
		   }
		   else
		   		echo "<p style=\"margin-left:-106px;float:left;color:red;margin-top:49px;font-size:15px;\">Ora de Start trebuie sa fie mai mica decat ora de Sfarsit</p>";		      
          // echo $data['name']." ".$data['type'];die();

        } elseif ($_POST) {
            $form->populate($_POST);
        }
        
    	$this->view->form = $form;			
		
	}
	
	function editdepartmentAction()
	{
		$ok = 0;
		$this->_helper->layout()->setLayout('departmentslayout');		
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);	
		$this->view->role = $role['access'];
		
		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;		
			
		$data = array();
		$details = array();
		
		$id = $this->getRequest()->getParam('id');
		$resultData = $db->getDepartmentById($id);
		if($resultData['parent'] == 0)
			$parentName = $resultData['name'];
		else
			{
				$parent = $db->getDepartmentById($resultData['parent']);
				$parentName = $parent['name'];
			}
		$details[$resultData['parent']] = $parentName;		
		$details['name'] = $resultData['name'];
	   $details['working_hours'] = $resultData['working_hours'];
	   $details['cost_per_hour'] = $resultData['cost_per_hour'];
	   $details['day1_start'] = $resultData['day1_start'];
	   $details['day1_stop'] = $resultData['day1_stop'];
	   $details['day2_start'] = $resultData['day2_start'];
	   $details['day2_stop'] = $resultData['day2_stop'];
	   $details['day3_start'] = $resultData['day3_start'];
	   $details['day3_stop'] = $resultData['day3_stop'];
	   $details['day4_start'] = $resultData['day4_start'];
	   $details['day4_stop'] = $resultData['day4_stop'];
	   $details['day5_start'] = $resultData['day5_start'];
	   $details['day5_stop'] = $resultData['day5_stop'];
	   $details['day6_start'] = $resultData['day6_start'];
	   $details['day6_stop'] = $resultData['day6_stop'];	   
	   $details['day7_start'] = $resultData['day7_start'];
	   $details['day7_stop'] = $resultData['day7_stop'];	   
	   $details['break_lenght'] = $resultData['break_lenght'];
	   $details['payment'] = $resultData['payment'];		

		
		$details['department'] = $resultData['parent'];
		//print_r($details);die();
		
		$form = new Application_Form_frmDepartment(array('id' => 'editDepartmentForm'),$id);
        
        if (!empty($_POST) && $form->isValid($_POST)) {
           $this->view->formSucces = 1;

           $arrData = $this->_request->getPost();
           $data['name'] = $arrData['name'];
		   if(empty($arrData['department']))
		   		$data['parent'] = '0';
		   else
          		$data['parent'] = $arrData['department'];
		   $data['working_hours'] = $arrData['working_hours'];
		   $data['cost_per_hour'] = $arrData['cost_per_hour'];
		   $data['day1_start'] = $arrData['day1_start'];
		   $data['day1_stop'] = $arrData['day1_stop'];
		   if(strtotime($data['day1_start']) > strtotime($data['day1_stop']))
		   		$ok = 1;
				
		   $data['day2_start'] = $arrData['day2_start'];
		   $data['day2_stop'] = $arrData['day2_stop'];
		   if(strtotime($data['day2_start']) > strtotime($data['day2_stop']))
		   		$ok = 1;	
					   
		   $data['day3_start'] = $arrData['day3_start'];
		   $data['day3_stop'] = $arrData['day3_stop'];
		   if(strtotime($data['day3_start']) > strtotime($data['day3_stop']))
		   		$ok = 1;		   
		   
		   $data['day4_start'] = $arrData['day4_start'];
		   $data['day4_stop'] = $arrData['day4_stop'];
		   if(strtotime($data['day4_start']) > strtotime($data['day4_stop']))
		   		$ok = 1;	
					   
		   $data['day5_start'] = $arrData['day5_start'];
		   $data['day5_stop'] = $arrData['day5_stop']; 
		   if(strtotime($data['day5_start']) > strtotime($data['day5_stop']))
		   		$ok = 1;		   
		   $data['day6_start'] = $arrData['day6_start'];
		   $data['day6_stop'] = $arrData['day6_stop'];
		   if(strtotime($data['day6_start']) > strtotime($data['day6_stop']))
		   		$ok = 1;
						   
		   $data['day7_start'] = $arrData['day7_start'];		   
		   $data['day7_stop'] = $arrData['day7_stop'];
		   if(strtotime($data['day7_start']) > strtotime($data['day7_stop']))
		   		$ok = 1;
						   
		   $data['break_lenght'] = $arrData['break_lenght'];
		   $data['payment'] = $arrData['payment'];		   
		   
		  
		   if($ok == 0){
			   $db->updateDepartment($id,$data);	
			   $this->_redirect('index/departments');
		   }
		   else
		   	   echo "<p style=\"margin-left:-106px;float:left;color:red;margin-top:49px;font-size:15px;\">Ora de Start trebuie sa fie mai mica decat ora de Sfarsit</p>";		      
          // echo $data['name']." ".$data['type'];die();

        } elseif ($_POST) {
            $form->populate($_POST);
        } 
		 else
        	$form->populate($details);
		
        
    	$this->view->form = $form;			
		
	}

	function deleteselecteddepartmentAction()
	{
		$this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
		//die("asd");
		$db = new Application_Model_Database();
		
		$id = $this->_request->getPost('department');
		
		//$response['id'] = $id;
		//$result = json_encode($response);
		
		$db->deleteSelectedDepartment($id);		
		return $this->_helper->json(true);
		//$this->_redirect('index/departments');
	}
	
	function deleteselecteddepartmentsAction()
	{
		$this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
		//die("asd");
		$db = new Application_Model_Database();
		$departments = $this->_request->getPost('departments');
		
		//$response['id'] = $id;
		//$result = json_encode($response);
		
		$db->deleteSelectedDepartments($departments);	
		return $this->_helper->json(true);
		//$this->_redirect('index/departments');		
	}	
	
	
	function usertodepartmentsAction()
	{
		$i = 0;
		$this->_helper->layout()->setLayout('departmentslayout');		
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
	    $db = new Application_Model_Database();
		 
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];
		
		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;
		 		
		$users[] = array();
		$temp = array();
			
		//$employees = $db->getEmployeesFromServer();		
		$employees = $db->getUsers();	
		//echo "<pre>";print_r($employees);echo "</pre>";die();
		
		foreach($employees as $employee)
		{
			$users[$i]['name'] = $employee['reUser'];
			
			$dept = $db->getUserFromDepartmentsByName($employee['reUser']);
			if(!empty($dept))
			{
				$result = $db->getDepartmentById($dept['department']);
				if(!empty($result['name']))
					$nameDepartment = $result['name'];
				$users[$i]['department'] = $nameDepartment;
				$users[$i]['id'] = $dept['id'];
				$users[$i]['first_name'] = $dept['first_name'];
				$users[$i]['last_name'] = $dept['last_name'];
				$nameDepartment = '';
			}
			else
			{
				$users[$i]['department'] = 'Nedefinit';
			}
			$i++;	
	
		}
						
		//echo "<pre>"; print_r($users);echo "</pre>";die();
		
	    $rights = $db->getRights($role['access'],'departments');		
		$this->view->rights = $rights;
		$this->view->rows = $users;
	}

	/*
	 * This function receives two ids : 
	 *    - idFromServer - the id from the servers table
	 *    - id - the id of the employee ( 0 if he is not asigned to a departemnt)
	 *  After that the application displays an form with the details of the employee selected
	 */
	function editusertodeptAction()
	{
		$this->_helper->layout()->setLayout('departmentslayout');		
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];
		
		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;		
		$this->view->role = $role['access'];	
		$data = array();
		$details = array();
		
		$id = $this->getRequest()->getParam('id');
		$idFromServer = $this->getRequest()->getParam('idfromserver');
		
		if($id == 0)
		{
			//$resultData = $db->getEmployeesFromServerByID($idFromServer);
			$details['name'] = $idFromServer;
			//$details['rcID'] = $resultData['rcID'];
			
			//echo "<pre>";print_r($details);echo "</pre>";die();
		}
		else
		{
			$resultData = $db->getUserFromDepartmentsByID($id);
		 	$result = $db->getDepartmentById($resultData['department']);
			$resultRoot = $db->getDepartmentById('1');
		    if(!empty($result['name'])){
				$nameDepartment = $result['name'];
			}
			else
				$nameDepartment = $resultRoot['name'];
		   			
		   $details['name'] = $resultData['name'];
		   $details['first_name'] = $resultData['first_name'];
		   $details['last_name'] = $resultData['last_name'];
		   $details['department'] = $resultData['department'];
		   $details['cnp'] = $resultData['cnp'];
		   $details['working_hours'] = $resultData['working_hours'];
		   $details['cost_per_hour'] = $resultData['cost_per_hour'];
		   $details['day1_start'] = $resultData['day1_start'];
		   $details['day1_stop'] = $resultData['day1_stop'];
		   $details['day2_start'] = $resultData['day2_start'];
		   $details['day2_stop'] = $resultData['day2_stop'];
		   $details['day3_start'] = $resultData['day3_start'];
		   $details['day3_stop'] = $resultData['day3_stop'];
		   $details['day4_start'] = $resultData['day4_start'];
		   $details['day4_stop'] = $resultData['day4_stop'];
		   $details['day5_start'] = $resultData['day5_start'];
		   $details['day5_stop'] = $resultData['day5_stop'];
		   $details['day6_start'] = $resultData['day6_start'];
		   $details['day6_stop'] = $resultData['day6_stop'];		   
		   $details['day7_start'] = $resultData['day7_start'];
		   $details['day7_stop'] = $resultData['day7_stop'];		   
		   $details['break_lenght'] = $resultData['break_lenght'];
		   $details['payment'] = $resultData['payment'];
		   $details['company'] = $resultData['company'];
			$details['rcID'] = $resultData['rcID'];
			$details['rcClientVersion'] = $resultData['rcClientVersion'];		   	
		   //$details['rcID'] = $resultData['rcID'];		
		}	
		
		
		//$resultData[$resultData['id']] = $resultData['name'];
		//print_r($details);die();
		
		$form = new Application_Form_frmUserToDepartment(array('id' => 'editDepartmentForm'));
        
        if (!empty($_POST) && $form->isValid($_POST)) {
           $this->view->formSucces = 1;
		   $form->populate($details);

           $arrData = $this->_request->getPost();
		   
           $data['name'] = $idFromServer;
		   $data['first_name'] = $arrData['first_name'];
		   $data['last_name'] = $arrData['last_name'];		   
           $data['department'] = $arrData['department'];
		   $data['cnp'] = $arrData['cnp'];
		   $data['working_hours'] = $arrData['working_hours'];
		   $data['cost_per_hour'] = $arrData['cost_per_hour'];
		   $data['day1_start'] = $arrData['day1_start'];
		   $data['day1_stop'] = $arrData['day1_stop'];
		   if(strtotime($data['day1_start']) > strtotime($data['day1_stop']))
		   		$ok = 1;
				
		   $data['day2_start'] = $arrData['day2_start'];
		   $data['day2_stop'] = $arrData['day2_stop'];
		   if(strtotime($data['day2_start']) > strtotime($data['day2_stop']))
		   		$ok = 1;	
					   
		   $data['day3_start'] = $arrData['day3_start'];
		   $data['day3_stop'] = $arrData['day3_stop'];
		   if(strtotime($data['day3_start']) > strtotime($data['day3_stop']))
		   		$ok = 1;		   
		   
		   $data['day4_start'] = $arrData['day4_start'];
		   $data['day4_stop'] = $arrData['day4_stop'];
		   if(strtotime($data['day4_start']) > strtotime($data['day4_stop']))
		   		$ok = 1;	
					   
		   $data['day5_start'] = $arrData['day5_start'];
		   $data['day5_stop'] = $arrData['day5_stop']; 
		   if(strtotime($data['day5_start']) > strtotime($data['day5_stop']))
		   		$ok = 1;		   
		   $data['day6_start'] = $arrData['day6_start'];
		   $data['day6_stop'] = $arrData['day6_stop'];
		   if(strtotime($data['day6_start']) > strtotime($data['day6_stop']))
		   		$ok = 1;
						   
		   $data['day7_start'] = $arrData['day7_start'];		   
		   $data['day7_stop'] = $arrData['day7_stop'];
		   if(strtotime($data['day7_start']) > strtotime($data['day7_stop']))
		   		$ok = 1;
		 
		   //echo "<pre>"; print_r($data);echo "</pre>";die();
		   
		   $data['break_lenght'] = $arrData['break_lenght'];
		   $data['payment'] = $arrData['payment'];
		   $data['company'] = $arrData['company'];
		   
		   //$data['rcID'] = $details['rcID'];
		   //$data['rcClientVersion'] = $details['rcClientVersion'];
		   //$data['rcID'] = $arrData['rcID'];
		   
		   if($ok == 0)
		   {
			   if($id == 0)
			   		$db->addUserToDepartment($data);	
			   else
			   		$db->updateUserToDepartment($id,$data);	
			   $this->_redirect('index/usertodepartments');	
		   }
		   else
		   		echo "<p style=\"margin-left:-106px;float:left;color:red;margin-top:49px;font-size:15px;\">Ora de Start trebuie sa fie mai mica decat ora de Sfarsit</p>";	      
          // echo $data['name']." ".$data['type'];die();

        } elseif ($_POST) {
            $form->populate($_POST);
        } 
		 else
        	$form->populate($details);
		
        
    	$this->view->form = $form;				
	}

	function usershierarchyAction()
	{
		$this->_helper->layout()->setLayout('departmentslayout');
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];
		
		$status = array();
		$um = array();
		$dm = array();
		$temp_um = array();
		$temp_dm = array();
		$deptbanned = array();

		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;	
						
		$departments = $db->getAllDepartments();
	    $usersToDepartments = $db->getAllUsersFromDepartments();
		$currentUser = $db->getUserDetails($userInfo->id);
		
		$temp_dm = explode("/",$currentUser['departments_monitorized']);
		$temp_um = explode("/",$currentUser['users']);
			
		for($i=0;$i<count($temp_um);$i++)
		{
			$um[$temp_um[$i]] = $temp_um[$i];
		}
		
		for($i=0;$i<count($temp_dm);$i++)
		{
			$dm[$temp_dm[$i]] = $temp_dm[$i];
		}

		foreach($departments as $department)
		{
			if (!in_array($department['id'], $dm))
			{
				$status[$department['id']] = $department['id'];
			}
		}

		foreach($status as $item)
		{
			$deptbanned[$item] = $item;

			$deptbanned = $this->getAllSubDepartments($deptbanned, $item);
		}			
				
		$this->view->departmentsMonitorized = $dm;
		$this->view->usersMonitorized = $um;
		$this->view->deptBanned = $deptbanned;
		
	 	// echo "<pre>"; print_r($status);die();echo "</pre>";
	    $this->view->departments = $departments;
		$this->view->usersToDepartments = $usersToDepartments;
		
		
	}

	function getAllSubDepartments($data,$parent)
	{
		$db = new Application_Model_Database();	
		$dept = $db->getDepartmentByParent($parent);
		if(!empty($dept))
		{
			foreach($dept as $item)
			{
				$data[$item['id']] = $item['id'];
				$data = $this->getAllSubDepartments($data,$item['id']);
			}
		}
		return $data;
	}

	function remoteclientAction()
	{

        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
	
		$rolesSidebar = $db->getAccessLevel();
		$this->view->rolesSidebar = $rolesSidebar;	
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];
		
		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;	
			
		$rights = $db->getRights($role['access'],'productivo_clients');
		$clients = $db->getEmployeesFromServerRemote();
		
		$this->view->rights = $rights;		
		$this->view->clients = $clients;		

	}
	
	function editremoteclientAction()
	{
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];
				
		$this->_helper->layout()->setLayout('departmentslayout');
		
		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;
				
		$id = $this->getRequest()->getParam('id');
		$details = $db->getEmployeesFromServerByID($id);
		$data = array();
		$details['rcClientLastSeen'] = $details['rcClientLastSeen']->format('Y-m-d');
		$form = new Application_Form_frmRemoteClient(array('id' => 'editRemoteClient'));
		
        //$details[$details['rcEnabled']] = $details['rcEnabled'];
		//$details[$details['rcTrace']] = $details['rcTrace'];
		
		//echo "<pre>";print_r($details);echo "</pre>";die();
        if (!empty($_POST) && $form->isValid($_POST)) {
           $this->view->formSucces = 1;
		   $form->populate($details);

           $arrData = $this->_request->getPost();
		   
           //$data['rcName'] = $arrData['rcName'];
           //$data['rcClientIP'] = $arrData['rcClientIP'];
		   //$data['rcClientLastSeen'] = $arrData['rcClientLastSeen'];
		   //$data['rcClientVersion'] = $details['rcClientVersion'];
		   $data['rcEnabled'] = $arrData['rcEnabled'];
		   $data['rcTrace'] = $arrData['rcTrace'];
		  // echo "<pre>";print_r($data);echo "</pre>";die();
		   $db->updateRemoteClient($data,$id);
		   
		   $this->_redirect('index/remoteclient');		      
          // echo $data['name']." ".$data['type'];die();

        } elseif ($_POST) {
            $form->populate($_POST);
        } 
		 else
        	$form->populate($details);
		
        
    	$this->view->form = $form;		
	}

	function parseUsersFromAlert($users)
	{
		$temp ="";
		$ok = 0;
		foreach($users as $key=>$value){
			$pos = 1;
		
			if(strpos($key,'user') !== false)
			{
				$temp= $temp.$value."/";
				$ok=1;
			}
		}
		//$temp[strlen($temp)-1] ='';
		if($ok == 0)
			return '';
		return substr($temp ,0,-1);
	}
	
	function parseReportsFromAlert($reports)
	{
		$temp ="";
		$ok = 0;
		foreach($reports as $key=>$value){
			$pos = 1;
		
			if(strpos($key,'reportproductivo') !== false)
			{
				$temp= $temp.$value."/";
				$ok = 1;
			}
		}
		$temp = substr($temp ,0,-1);
		if($ok == 0)
			return '';	
		return $temp;
	}	
	
	function parseDepartmentsFromAlert($departments)
	{
		$temp ="";
		$ok = 0;
		foreach($departments as $key=>$value){
			$pos = 1;
		
			if(strpos($key,'department') !== false)
			{
				$temp= $temp.$value."/";
				$ok = 1;
			}
		}
		//$temp[strlen($temp)-1] ='';
		$temp = substr($temp ,0,-1);
		if($ok == 0)
			return '';			
		return $temp;
	}	
	
	function parsealertreportmailAction()
	{
		$db = new Application_Model_Database();
				
	    $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
		$temp = array();
		
		$temp['alert_type'] = $_POST['alerttype'];
		$temp['users'] = $this->parseUsersFromAlert($_POST);
		$temp['departments'] = $this->parseDepartmentsFromAlert($_POST);
		$temp['destination'] = $_POST['alertdestination1'];
		$temp['name'] = $_POST['alertname'];
		$temp['type'] = "ra";					
		if($_POST['alerttype'] == 'hourly_deviation')
		{
			$temp['time_deviation'] = $_POST['timepassingdeviation']."/".$_POST['leavingdeviation']."/".$_POST['timeoverpassingdeviation'];

		}
		elseif($_POST['alerttype'] == 'inactivity')
			{
				$temp['time_deviation'] = $_POST['inactivitytimedeviation'];
			}
			elseif($_POST['alerttype'] == 'unproductive')
				{
					$temp['time_deviation'] = $_POST['unproductivtimedeviation'];
				}
		//echo "<pre>"; var_dump($temp); echo"</pre>";die();	
		$db->addReportAlert($temp);
		$this->_redirect('index/reportalerts');
		//echo "<pre>"; var_dump($temp); echo"</pre>";die();		
	}

	function parseeditalertreportmailAction()
	{
		$db = new Application_Model_Database();
				
	    $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
		$temp = array();
		
		$id = $this->getRequest()->getParam('id');
		
		$temp['alert_type'] = $_POST['alerttype'];
		$temp['users'] = $this->parseUsersFromAlert($_POST);
		$temp['departments'] = $this->parseDepartmentsFromAlert($_POST);
		$temp['destination'] = $_POST['alertdestination1'];
		$temp['name'] = $_POST['alertname'];
		$temp['type'] = "ra";					
		
		if($_POST['alerttype'] == 'hourly_deviation')
		{
			$temp['time_deviation'] = $_POST['timepassingdeviation']."/".$_POST['leavingdeviation']."/".$_POST['timeoverpassingdeviation'];

		}
		elseif($_POST['alerttype'] == 'inactivity')
			{
				$temp['time_deviation'] = $_POST['inactivitytimedeviation'];
			}
			elseif($_POST['alerttype'] == 'unproductive')
				{
					$temp['time_deviation'] = $_POST['unproductivtimedeviation'];
				}
		//echo "<pre>"; var_dump($temp); echo"</pre>";die();	
		$db->updateReportAlert($id,$temp);
		$this->_redirect('index/emails');
		//echo "<pre>"; var_dump($temp); echo"</pre>";die();		
	}
	
	function parseeditreportmailAction()
	{
		$db = new Application_Model_Database();
				
	    $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
		$temp = array();
		
		$id = $this->getRequest()->getParam('id');
		//echo "<pre>";print_r($_POST);echo "</pre>";die();
		$temp['users'] = $this->parseUsersFromAlert($_POST);
		$temp['departments'] = $this->parseDepartmentsFromAlert($_POST);
		$temp['reports'] = $this->parseReportsFromAlert($_POST);
		$temp['destination'] = $_POST['alertdestination1'];
		$temp['name'] = $_POST['reportname'];
		$temp['type'] = "re";					
		
		//echo "<pre>"; var_dump($temp); echo"</pre>";die();	
		$db->updateReportAlert($id,$temp);
		$this->_redirect('index/viewreportemails');
		//echo "<pre>"; var_dump($temp); echo"</pre>";die();		
	}
	
	function filtersAction()
	{
		$this->_helper->layout()->setLayout('filterslayout');
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];
			
		$parents = array();	
		
	    $rights = $db->getRights($role['access'],'filters');
		
		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;		
		
		$rows = $db->getAllFilters();
		
		//print_r($parents);die();
		
		$this->view->rights = $rights;
		$this->view->filters = $rows;		
	}

	function addfilterAction()
	{
		$this->_helper->layout()->setLayout('filterslayout');		
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];
		
		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;	
		
	    $rights = $db->getRights($role['access'],'filters');
		$this->view->rights = $rights;		
						
		$form = new Application_Form_frmFilters(array('id' => 'editFilterForm'));
        	
        if (!empty($_POST) && $form->isValid($_POST)) {
           $this->view->formSucces = 1;

           $arrData = $this->_request->getPost();
           $data['path'] = $arrData['path'];
		          
           $temp = $db->addFilter($data);
		   $this->_redirect('index/filters');


        } elseif ($_POST) {
            $form->populate($_POST);
        }
        
    	$this->view->form = $form;		
	}
	
	function editfilterAction()
	{
		$data = array();
		$this->_helper->layout()->setLayout('filterslayout');		
        if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];
		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;	
		
	    $rights = $db->getRights($role['access'],'filters');
		$this->view->rights = $rights;		
			
		$id = $this->getRequest()->getParam('id');
		$details = $db->getFilterById($id);
		
						
		$form = new Application_Form_frmFilters(array('id' => 'editFilterForm'));
        	
        if (!empty($_POST) && $form->isValid($_POST)) {
           $this->view->formSucces = 1;

           $arrData = $this->_request->getPost();
           $data['path'] = $arrData['path'];
		          
           $temp = $db->updateFilter($id,$data);
		   $this->_redirect('index/filters');


        } elseif ($_POST) {
            $form->populate($_POST);
        }
		else
			$form->populate($details);
        
    	$this->view->form = $form;		
	}	
	
	function deleteselectedfilterAction()
	{
		$this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
		//die("asd");
		$db = new Application_Model_Database();
		
		$id = $this->_request->getPost('filter');
		
		//$response['id'] = $id;
		//$result = json_encode($response);
		$db->deleteSelectedFilter($id);	
		return $this->_helper->json(true);	
	}

	function deleteselectedfiltersAction()
	{
		$this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
		//die("asd");
		$db = new Application_Model_Database();
		
		$filters = $this->_request->getPost('filters');
		
		//$response['id'] = $id;
		//$result = json_encode($response);
		$db->deleteSelectedFilters($filters);		
		return $this->_helper->json(true);
	} 
	
	function settypecategoriesAction()
	{
		$this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
		//die("asd");
		$db = new Application_Model_Database();
		
		$categories = $this->_request->getPost('categories');	
		$type = $this->_request->getPost('type');	
		
		$db->setCategoriesType($categories,$type);
		
		return $this->_helper->json(true);
	}   
	
	function deleteselectedclientAction()
	{
		
		$this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
		//die("asd");
		$db = new Application_Model_Database();
		
		$computer = $this->_request->getPost('computer');
		
		//$response['id'] = $id;
		//$result = json_encode($response);
		
		$db->deleteSelectedClient($computer);	
			
		return $this->_helper->json(true);
	}
	
	function settypecategoryAction()
	{
		$this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
		//die("asd");
		$db = new Application_Model_Database();
		
		$data = $this->_request->getPost('inf');		
		//print_r($data['name']." ".$data['type']);die();
		$db->setCategoryType($data['name'],$data['type']);
	}   
	
	function getcomputernameAction()
	{
		$this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
		//die("asd");
		$db = new Application_Model_Database();
		
		$data = $this->_request->getPost('computer');		
		//print_r($data['name']." ".$data['type']);die();
		
		$result = $db->getEmployeesFromServerByID($data);		
		
		return $this->_helper->json($result['rcName']);
	} 
	
	function changepasswordAction()
	{
		if(!Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('index/index');
        }    	
		 $db = new Application_Model_Database();
		 $data = array();
		 
 		$userInfo = Zend_Auth::getInstance()->getIdentity();
		$role = $db->getAccessLevelById($userInfo->access_level);
		$this->view->role = $role['access'];
		//echo "<pre>";print_r($userInfo); echo "</pre>";
		$description = $db->getMenu($role['access'],"u");
		$this->view->menu = $description;	

		$rolesSidebar = $db->getAccessLevel();
		$this->view->rolesSidebar = $rolesSidebar;

				
		$form = new Application_Form_frmPassword(array('id' => 'editPassword'));
        	
        if (!empty($_POST) && $form->isValid($_POST)) {
           $this->view->formSucces = 1;

           $arrData = $this->_request->getPost();
           $data['password'] = $arrData['password'];	
		   $data['username'] = $userInfo->username;
		   $data['name'] = $userInfo->name;
		   $data['email'] = $userInfo->email;
		   $data['role'] = $userInfo->access_level;		
		   $data['users'] = $userInfo->users;	
		   $data['departments_monitorized'] = $userInfo->departments_monitorized;		
		   
		   //echo "<pre>";print_r($data); echo "</pre>";die();
		             
           $db->updateUser($userInfo->id,$data,"1");
		   $this->_redirect('index/home');
		   

        } elseif ($_POST) {
            $form->populate($_POST);
        }
        
    	$this->view->form = $form;		
	}

	function deleteselectedemailAction()
	{
		$this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
		//die("asd");
		$db = new Application_Model_Database();
		
		$id = $this->_request->getPost('email');
		
		$db->deleteSelectedEmail($id);
		return $this->_helper->json(true);		
	}
	
	function deleteselectedemailsAction()
	{
		$this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
		$db = new Application_Model_Database();
		
		$emails = $this->_request->getPost('emails');
		
		$db->deleteSelectedEmails($emails);
		return $this->_helper->json(true);		
	}	
	
	
	
}