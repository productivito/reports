<?php 

class Application_Model_permissions{

    protected $db, $user;

    public function __construct() {
    	
        $this->db = Zend_Registry::get('db');
        $this->user= new Application_Model_user();
        
    }

    public function getPermissions($id_user, $permission_required){
    	
    	$user_permission = $this->user->getUserGroup($id_user);
    	
    	if($user_permission <= $permission_required) return true;
    	else return false;
    	
    }
    
}    
    

?>