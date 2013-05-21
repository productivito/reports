<?php

class Application_Table_dbDepartments extends Zend_Db_Table_Abstract {

    protected $_db;
    protected $_name = 'departments';

    public function __construct() {
        $this->_db = Zend_Registry::get('db');
    }

   public function getAllDepartments(){
 
         $query = $this->select();
		 $query->from('departments');
        $result = $this->fetchAll($query);
        //echo '<prE>'.print_r($result,true);die();
        if(!empty($result)) {
            $result = $result->toArray();

            //echo '<prE>'.print_r($result,true);die();
            return $result;
        }
        
        return array();  	

   }
   
   function getDepartmentbyId($id)
   {
		
        $query = $this->select();
		$query->from('departments')->where("id = ?", $id);
        $result = $this->fetchRow($query);
        //echo '<prE>'.print_r($result,true);die();
        if(!empty($result)) {
            $result = $result->toArray();
            //echo '<prE>'.print_r($result,true);die();
            return $result;
        }
        
        return array();  	  
   }
   
    function getDepartmentbyParent($parent)
   {

        $query = $this->select();
		$query->from('departments')->where("parent = ?", $parent);
        $result = $this->fetchAll($query);
        //echo '<prE>'.print_r($result,true);die();
        if(!empty($result)) {
            $result = $result->toArray();

            //echo '<prE>'.print_r($result,true);die();
            return $result;
        }
        
        return array();  	  
   }  
   
   function updateDepartment($id,$data)	
   {
   		$where = $this->getAdapter()->quoteInto('id = ?', $id);
		
        $this->update($data, $where);
		
		//echo "<pre>";print_r($data);echo "</pre>";die();
   }
   
   function addDepartment($data)
   {
   		$this->insert($data);
   }
   
   function hasSubdepartments($id)
   {
		$query = $this->select();
		$query->from('departments')->where("parent = ?", $id);
        $result = $this->fetchAll($query); 
		
        if(!empty($result)) {
			return true;
        }
        
		return false;	 		  		
   }
   
   function deleteSelectedDepartment($id)
   {
	   $where = $this->getAdapter()->quoteInto("id = ?", $id);

	    $this->delete($where);
	  
		$this->updateRecursive($id); 		
   }
   
   function updateRecursive($id)
   {
		$query = $this->select();
		$query->from('departments')->where("parent = ?", $id);
        $result = $this->fetchAll($query);
		
		foreach($result as $dept)
		{
			$temp = array();
			
			$temp['name'] = $dept['name'];
			$temp['parent'] = '1';	
						
			$where = $this->getAdapter()->quoteInto('id = ?', $dept['id']);
		
       		$this->update($temp, $where);
							
			if($this->hasSubdepartments($dept['id']) == true)
			{
				$this->updateRecursive($dept['id']);
			}
		}   		
   }
 
 
   function deleteSelectedDepartments($departments)
   {
   	   
		for($i=0;$i<count($departments);$i++)
		{
			 $where = $this->getAdapter()->quoteInto("id = ?", $departments[$i]);
			 $this->delete($where);
			 
		     $this->updateRecursive($departments[$i]);			 
			 
		}		
   }  
}