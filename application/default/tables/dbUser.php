<?php

class Application_Table_dbUser extends Zend_Db_Table_Abstract {

    protected $_db;
    protected $_name = 'users';

    public function __construct() {
        $this->_db = Zend_Registry::get('db');
    }
	
   public function userLoginAttempt($arrCredentials) {
   	
        $user = $arrCredentials['username'];
        $password = md5($arrCredentials['password']);
		
        $query = $this->select();
        $query->from('users')->where("username = ?", $user)
              ->where("password = ?", $password);
        $result = $this->fetchRow($query);
		
        //echo '<prE>'.print_r($result,true);die();
        if(!empty($result)) {
            $result = $result->toArray();

            //echo '<prE>'.print_r($result,true);die();
            return $result;
        }
        
        return array();
    }	
   

   public function getAllUsers(){
   	  	
		$query = $this->select();
		$query->from('users');
		
		$result = $this->fetchAll($query);

        if(!empty($result)) {
            $result = $result->toArray();

            //echo '<prE>'.print_r($result,true);die();
            return $result;
        }
        
        return array();
   }
   
   public function deleteUser($id){
   		
		 $where = $this->getAdapter()->quoteInto("id = ?", $id);

		 $this->delete($where);
		 
	    /*$link = mssql_connect('89.38.209.13', 'productivo', 'productivo');
   	    mssql_select_db('productivo', $link);
   	     
		$sql = " DELETE users FROM user WHERE id = ".$id;
		$query = mssql_query($sql);	*/		 

		
   }
   
   public function deleteUsers($users){
   			
		for($i=0;$i<count($users);$i++)
		{
			 $where = $this->getAdapter()->quoteInto("id = ?", $users[$i]);
			 $this->delete($where);
				
		}
		
		return 'success';
	
   }
   
   public function getUserDetails($id){
   		
	
		$query = $this->select();
		$query->from('users')->where('id = ?',$id);

        $result = $this->fetchRow($query);
        if(!empty($result)) {
            $result = $result->toArray();
			
            //echo '<prE>'.print_r($result,true);die();
            return $result;
        }
        
        return array();
   }  
   
   public function getUserDetailsWithAccessID($id){
   		
	
		$query = $this->select();
		$query->from('users')->where('id = ?',$id);

        $result = $this->fetchRow($query);
        if(!empty($result)) {
            $result = $result->toArray();
			
            //echo '<prE>'.print_r($result,true);die();
            return $result;
        }
        
        return array();
   }   
   
   public function updateUser($id,$data,$md5){
   		$pass = $data['password'];
		if($md5 == 1)
		{
			$pass = md5($data['password']);
		}
		
        $info= array('username' => $data['username'],
					'name' => $data['name'],
					'email' => $data['email'],
					'password' => $pass,
					'access_level' => $data['role'],
					'users' => empty($data['users'])?'':$data['users'],
					'departments_monitorized' => empty($data['departments_monitorized'])?'':$data['departments_monitorized'],
		);
        $where = $this->getAdapter()->quoteInto('id = ?', $id);
        
        $this->update($info, $where);
	
   }
   
   public function addUser($data){
   	   
	
		$query = $this->select();
		$query->from('users')->where('username = ?',$data['username']);
		
        $result = $this->fetchRow($query);
        if(!empty($result)) {
            $result = $result->toArray();
			
            //echo '<prE>'.print_r($result,true);die();
            return "<p style=\"margin-left:-106px;float:left;color:red;margin-top:49px\">Username-ul ".$data['username']. " este deja utilizat.</p>";
        }	   
	    
	   
   	  $data['password'] = md5($data['password']);	
	  $this->insert($data);
	  return "true";
	 
   }
   

   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
	
    public function findFacebookId($id) {
       $sql = $this->select()->setIntegrityCheck(false)->where('facebook_ID = ?', $id);
       
       $result = $this->fetchRow($sql);
       return $result;
    }

    public function findUserByEmail($email) {
        $sql = $this->select()->setIntegrityCheck(false)->where('user_email = ?', $email);
       
        $result = $this->fetchRow($sql);
        if(!empty($result))
            return $result;
        else return false;
    }
    
    public function setFacebookIdToUser($userId, $facebookId) {
        $data = array('facebook_ID' => $facebookId);
        $where = $this->getAdapter()->quoteInto('user_ID = ?', $userId);
        
        $this->update($data, $where);
    }
    
    public function getUserIdByEmail($email) {
        $sql = $this->select()->setIntegrityCheck(false)->where('user_email = ?', $email);
       
        $result = $this->fetchRow($sql);
        return $result;
    }

    
    public function addNewUser($arrData, $intUserId = 0) {
        if(!empty($arrData) && $intUserId != 0) {
            $where = "user_ID = ".$intUserId;
            $this->update($arrData, $where);
            return $intUserId;
        } elseif (!empty($arrData) && $intUserId == 0) {
            return $this->insert($arrData);
        }
        
        return false;
    }
}