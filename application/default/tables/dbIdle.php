<?php

class Application_Table_dbIdle extends Zend_Db_Table_Abstract {

    protected $_db;
    //protected $_name = 'Idle';
	//protected $_primary = 'rcID';
    protected $_name = "";
    protected $_primary = "";
	
    public function __construct() {
        $this->_db = Zend_Registry::get('db');
    }

   public function getEmployeesFromServerRemote(){
 		 
        $this->_name = "RemoteClient";
        $this->_primary = "rcID";
         $query = $this->select()
		 				->from('RemoteClient');
        $result = $this->fetchAll($query);
        //echo '<prE>'.print_r($result,true);die();
        if(!empty($result)) {
            $result = $result->toArray();

            //echo '<prE>'.print_r($result,true);die();
            return $result;
        }
        
        return array();  	

   }
   
   function getEmployeesFromServerByID($id)
   {
        $this->_name = "RemoteClient";
        $this->_primary = "rcID";
		   	
         $query = $this->select();
		 $query->from('RemoteClient')
		 	   ->where('rcID = ?',$id);
        $result = $this->fetchRow($query);
        //echo '<prE>'.print_r($result,true);die();
        if(!empty($result)) {
            $result = $result->toArray();

            //echo '<prE>'.print_r($result,true);die();
            return $result;
        }
        
        return array();  	
   	
   }
   
   
   function getApplicationInformation()
   {
        $query = $this->select();
		$query->from('Idle');
        $result = $this->fetchAll($query);
        //echo '<prE>'.print_r($result,true);die();
        if(!empty($result)) {
            $result = $result->toArray();

            //echo '<prE>'.print_r($result,true);die();
            return $result;
        }
        
        return array();  	   		
   }
   
   
   function updateRemoteClient($data,$id)	
   {
		$this->_name = "RemoteClient";
        $this->_primary = "rcID";   
        	
   		$where = $this->getAdapter()->quoteInto('rcID = ?', $id);
        
        $this->update($data, $where);
		
   }
   
   function deleteSelectedUserFromIdle($id)
   {
   	
	   $this->_name = "RemoteClient";
       $this->_primary = "rcID"; 
	       	
	   $where = $this->getAdapter()->quoteInto("rcID = ?", $id);

	    $this->delete($where);
	  
	 		
   }
 
 
   function deleteSelectedUsersFromIdle($categories)
   {
   	   
		for($i=0;$i<count($categories);$i++)
		{
			 $where = $this->getAdapter()->quoteInto("id = ?", $categories[$i]);
			 $this->delete($where);
			 
		}		
   }

}