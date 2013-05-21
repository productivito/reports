<?php

class Application_Model_Database {

    public function __construct() {
        //$this->db = Zend_Registry::get('db');

    }
    
    public function getAll() {
        $db = new Application_Table_dbUser();
        return $db->getAll();
    }
	
	public function login(){
		
		$db = new Application_Table_dbUser();
		
		$userLogin =  array(
			'user_username'=>'admin',
			'user_password'=>'admin'	
		);
		
		$result = $db->userLoginAttempt($userLogin);
		
		return $result;
	}
	
	/* 
	 * Gets all the rights and a description for a role and type send as parameter
	 * Ex. role=Admin,
	 *  type = "u" (user) or "a" (activity) or "m" (monitor)
	 */
     
	function getMenu($role,$type)
	{
		$i = 0;
		$db = new Application_Table_dbMenu();
		$dbDescribeAccess = new Application_Table_dbDescribeAccess();
		
		$result = $db->getMenu($role);

		$finalResults[] = array();
		/*echo "<pre>";
		print_r($result);
		echo "</pre>";
		die();*/
		foreach ($result as $column => $value) {
			if($column != "id" && $column != "access" && $column != 'users' && $column != 'departments_monitorized'){
				if($value =='r' || $value == 'nr')
					$read = $value;
				else
					list($read,$add,$modify,$delete) = explode(",",$value,5);
					
				//echo "column = ".$column." value=".$value."</br>";
				//echo "read= ".$read." add ".$add." modify ".$modify." delete ".$delete."</br></br>";
				
				if($read =="r")
				{
					$description = $dbDescribeAccess->getMenuElement($column,$type);
					if(!empty($description) ){
						$finalResults[$i] = $description;
						$i++;
					}
				}
			}
			
		}
		return $finalResults;
		
		
	}
	
	/*
	 * Gets all the users accounts from the database
	 * returns an array
	 */
	function getAllUsers(){
		
		$db = new Application_Table_dbUser();
		
		$rows = $db->getAllUsers();
		
		return $rows;
		
	}
	
	/*
	 * Gets the Role of an user based on an ID
	 * returns an array with the role
	 */
	function getRole($id){
		
   		$db = new Application_Table_dbMenu();
		
		$result = $db->getRole($id);
		
		return $result;
		
	}
	
	/*
	 * Deletes the user with the id sent as parameter
	 */
	function deleteUser($id){
		$db = new Application_Table_dbUser();
		
		$db->deleteUser($id);		
		
	}
	
	/*
	 * Deletes all users with the id sent as parameter
	 * 
	 */
	function deleteUsers($users){
		$db = new Application_Table_dbUser();
		
		$result = $db->deleteUsers($users);		
		
		return $result;
	}	
	
	/*
	 * Gets all the details for a user with the ID sent as parameter
	 * returns an array
	 */
	function getUserDetails($id){
		
		$db = new Application_Table_dbUser();
		$db2 = new Application_Table_dbMenu();
		
		$details = $db->getUserDetails($id);
		$role = $db2->getRole($details['access_level']);
		$details['access_level'] = $role['access'];
		
		return $details;
				
	}
	
	/*
	 * Gets all the details for a user with the ID sent as parameter
	 * returns an array
	 */
	function getUserDetailsWithAccessID($id){
		
		$db = new Application_Table_dbUser();
		$db2 = new Application_Table_dbMenu();
		
		$details = $db->getUserDetails($id);
		//$role = $db2->getRole($details['access_level']);
		//$details['access_level'] = $role['access'];
		
		return $details;
				
	}	
	
	/*
	 * Gets all the access levels from the database
	 */
	function getAccessLevel()
	{
		$db = new Application_Table_dbMenu();
		
		$result = $db->getAccessLevel();
		
		return $result;
	}
	
	/*
	 * Gets the access levels from the database with a specified id
	 */
	function getAccessLevelById($id)
	{
		$db = new Application_Table_dbMenu();
		
		$result = $db->getAccessLevelById($id);
		
		return $result;
	}	
	
	function deleteSelectedRoles($roles){
		
		$db = new Application_Table_dbMenu();

		$result = $db->deleteSelectedRoles($roles);
		
		return $result;		
	}
	
	
	function deleteSelectedRole($role){
		
		$db = new Application_Table_dbMenu();

		$result = $db->deleteSelectedRole($role);
		
		return $result;		
	}	
	
	/*
	 * Returns the rights that a role has on a page
	 */
	function getRights($role,$page){
		
		$db = new Application_Table_dbMenu();
		
		$result = $db->getRights($role,$page);
		
		return $result;	
	}
	
	function updateUser($id,$data,$md5)
	{
		$db = new Application_Table_dbUser();
		
		$db->updateUser($id,$data,$md5);
		
	}
	
	function addUser($data)
	{
		$db = new Application_Table_dbUser();
		
		$result = $db->addUser($data);
		
		return $result;
	}
	
	function addRole($data)
	{
		$db = new Application_Table_dbMenu();
		
		$result = $db->addRole($data);
		
	}
	function updateRole($id,$data)
	{
		$db = new Application_Table_dbMenu();
		
		$result = $db->updateRole($id,$data);
		
	}	
	
	function getAllCategories()
	{
		$db = new Application_Table_dbCategory();
		
		$result = $db->getAllCategories();
		
		return $result;
	}
	
	function getCategoryDetails($id,$category)
	{
		$db = new Application_Table_dbCategory();
		
		$result = $db->getCategoryDetails($id,$category);
		
		return $result;		
	}
	
	function getCategoryBytypes($category)
	{
		$db = new Application_Table_dbCategory();
		
		$result = $db->getCategoryBytypes($category);
		
		return $result;		
	}
		
	
	function updateCategory($id,$data)
	{
		$db = new Application_Table_dbCategory();
		
		$result = $db->updateCategory($id,$data);
		
		return $result;				
	}
	
	function addCategory($data)
	{
		$db = new Application_Table_dbCategory();
		
		$result = $db->addCategory($data);
		
		return $result;				
	}
	
	function deleteSelectedCategory($id){
		
		$db = new Application_Table_dbCategory();

		$result = $db->deleteSelectedCategory($id);
		
		return $result;		
	}
	
	
	function deleteSelectedCategories($categories){
		
		$db = new Application_Table_dbCategory();

		$result = $db->deleteSelectedCategories($categories);
		
		return $result;		
	}		
	
	/*
	 * Gets all the categories whith the selected filter
	 */
	function getFilterdCategories($selectedCateg, $filtru1, $filtru2, $filtru3,$page,$numberRowsPerPage,$flag)
	{
		$db = new Application_Table_dbCategory();

		$result = $db->getFilterdCategories($selectedCateg, $filtru1, $filtru2, $filtru3,$page,$numberRowsPerPage,$flag);
		
		return $result;			
	}
	
	function userLoginAttempt($arrCredentials) {
		
		$db = new Application_Table_dbUser();

		$result = $db->userLoginAttempt($arrCredentials);
		
		return $result;		
				
	}
	
	function getAllUsersFromDepartments() {
		
		$db = new Application_Table_dbUserToDepartments();
		
		$result = $db->getAllUsersFromDepartments();
		
		return $result;		
				
	}	
	
	function addDepartment($data)
	{
		$db = new Application_Table_dbDepartments();
		
		$result = $db->addDepartment($data);
		
		return $result;
	}	
	
	function getAllDepartments()
	{
		$db = new Application_Table_dbDepartments();
		
		$result = $db->getAllDepartments();
		
		return $result;
	}
	
	function getDepartmentById($id)
	{
		$db = new Application_Table_dbDepartments();
		
		$result = $db->getDepartmentById($id);
		
		return $result;		
	}
	
	function getDepartmentByParent($parent)
	{
		$db = new Application_Table_dbDepartments();
		
		$result = $db->getDepartmentByParent($parent);
		
		return $result;		
	}	
	
	function updateDepartment($id,$data)
	{
		$db = new Application_Table_dbDepartments();
		
		$result = $db->updateDepartment($id,$data);
		
		return $result;			
	}
	
	function deleteSelectedDepartment($id)
	{
		$db = new Application_Table_dbDepartments();
		
		$result = $db->deleteSelectedDepartment($id);
		
		return $result;			
	}

	function deleteSelectedDepartments($departments)
	{
		$db = new Application_Table_dbDepartments();
		
		$result = $db->deleteSelectedDepartments($departments);
		
		return $result;			
	}		
	
	function getEmployeesFromServer()
	{
		$db = new Application_Table_dbReports();
		
		$result = $db->getEmployeesFromServer();
		
		return $result;			
	}
	
	function getEmployeesFromServerRemote()
	{
		$db = new Application_Table_dbIdle();
		
		$result = $db->getEmployeesFromServerRemote();
		
		return $result;			
	}	

	function getEmployeesFromServerByID($id)
	{
		$db = new Application_Table_dbIdle();
		
		$result = $db->getEmployeesFromServerByID($id);
		
		return $result;			
	}
	
	function getUserFromDepartmentsByName($name)
	{
		$db = new Application_Table_dbUserToDepartments();
		
		$result = $db->getUserFromDepartmentsByName($name);
		
		return $result;			
	}
	
	function getUserFromDepartmentsByRCID($id)
	{
		$db = new Application_Table_dbUserToDepartments();
		
		$result = $db->getUserFromDepartmentsByRCID($id);
		
		return $result;			
	}	
	
	function getUserFromDepartmentsByID($id)
	{
		$db = new Application_Table_dbUserToDepartments();
		
		$result = $db->getUserFromDepartmentsByID($id);
		
		return $result;			
	}	
	
	function addUserToDepartment($data)
	{
		
		$db = new Application_Table_dbUserToDepartments();
		
		$result = $db->addUserToDepartment($data);
		
		return $result;			
	}
	
	function updateUserToDepartment($id,$data)
	{
		$db = new Application_Table_dbUserToDepartments();
		
		$result = $db->updateUserToDepartment($id,$data);
		
		return $result;			
	}
	
	function getApplicationInformation()
	{
		$db = new Application_Table_dbIdle();
		
		$result = $db->getApplicationInformation();
		
		return $result;			
	}
	
	function getInternetSites()
	{
		$db = new Application_Table_dbReports();
		
		$result = $db->getInternetSites();
		
		return $result;		
	}
	
	
	function insertSitesInCategory($data)
	{
		$db = new Application_Table_dbCategory();
		
		$result = $db->insertSitesInCategory($data);
		
		return $result;				
	}
	
	function getApplicationForCategories()
	{
		$db = new Application_Table_dbReports();
		
		$result = $db->getApplicationForCategories();
		
		return $result;			
	}
	
	function insertApplicationsInCategory($data)
	{
		$db = new Application_Table_dbCategory();
		
		$result = $db->insertApplicationsInCategory($data);
		
		return $result;				
	}	
	
	function insertChatsInCategory($data)
	{
		$db = new Application_Table_dbCategory();
		
		$result = $db->insertChatsInCategory($data);
		
		return $result;				
	}

	function getChatForCategories()
	{
		$db = new Application_Table_dbReports();
		
		$result = $db->getChatForCategories();
		
		return $result;			
	}	
	
	
	function updateRemoteClient($data,$id)
	{
		$db = new Application_Table_dbIdle();
		
		$result = $db->updateRemoteClient($data,$id);
		
		return $result;			
	}
	
	function getAllReports()
	{
		$db = new Application_Table_dbDescribeAccess();
		
		$result = $db->getAllReports();
		
		return $result;
	}
	
	function addReportAlert($data)
	{
		$db = new Application_Table_dbEmail();
		
		$result = $db->addReportAlert($data);
		
		return $result;		
	}
	
	function getAllFilters()
	{
		$db = new Application_Table_dbFilters();
		
		$result = $db->getAllFilters();
		
		return $result;		
	}
	
	function addFilter($data)
	{
		$db = new Application_Table_dbFilters();
		
		$result = $db->addFilter($data);
		
		return $result;		
	}
	
	function updateFilter($id,$data)
	{
		$db = new Application_Table_dbFilters();
		
		$result = $db->updateFilter($id,$data);
		
		return $result;		
	}
	
	function getFilterById($data)
	{
		$db = new Application_Table_dbFilters();
		
		$result = $db->getFilterById($data);
		
		return $result;		
	}
	
	function deleteSelectedFilter($id)
	{
		$db = new Application_Table_dbFilters();
		
		$result = $db->deleteSelectedFilter($id);
		
		return $result;			
	}
	
	function deleteSelectedFilters($data)
	{
		$db = new Application_Table_dbFilters();
		
		$result = $db->deleteSelectedFilters($data);
		
		return $result;			
	}
	
	function setCategoriesType($categories,$type)
	{
		$db = new Application_Table_dbCategory();
		
		$result = $db->setCategoriesType($categories,$type);
		
		return $result;		
	}
	
	function setCategoryType($category,$type)
	{
		$db = new Application_Table_dbCategory();
		
		$result = $db->setCategoryType($category,$type);
		
		return $result;		
	}	
	
	function deleteSelectedClient($computer)
	{
		$db = new Application_Table_dbIdle();
		
		$result = $db->deleteSelectedUserFromIdle($computer);
		
		return $result;		
	}
	
	function getReportAlertById($id)
	{
		$db = new Application_Table_dbEmail();
		
		$result = $db->getReportAlertById($id);
		
		return $result;		
	}	
	
	function updateReportAlert($id,$data)
	{
		$db = new Application_Table_dbEmail();
		
		$result = $db->updateReportAlert($id,$data);
		
		return $result;			
	}				
	
	function getReportAlertsByType($type)
	{
		$db = new Application_Table_dbEmail();
		
		$result = $db->getReportAlertsByType($type);
		
		return $result;			
	}		
	
	function deleteSelectedEmail($id)
	{
		$db = new Application_Table_dbEmail();
		
		$result = $db->deleteSelectedReportAlert($id);
		
		return $result;				
	}	
	
	function deleteSelectedEmails($emails)
	{
		$db = new Application_Table_dbEmail();
		
		$result = $db->deleteSelectedReportsAlerts($emails);
		
		return $result;				
	}
	
	function getNCategories($page,$numberRowsPerPage)
	{
		$db = new Application_Table_dbCategory();
		
		$result = $db->getNCategories($page,$numberRowsPerPage);
		
		return $result;		
	}
	
	function getCategoriesNumber()
	{
		$db = new Application_Table_dbCategory();
		
		$result = $db->getCategoriesNumber();
		
		return $result;		
	}
	
	function getCategoriesNumberFilters($selectedCateg, $filtru1, $filtru2, $filtru3)
	{
		$db = new Application_Table_dbCategory();
		
		$result = $db->getCategoriesNumberFilters($selectedCateg, $filtru1, $filtru2, $filtru3);
		
		return $result;		
	}

	function getUsers()
	{
		$db = new Application_Table_dbRemoteUsers();
		
		$result = $db->getUsers();
		
		return $result;		
	}		
}
