<?php

class Application_Table_dbUserToDepartments extends Zend_Db_Table_Abstract {

    protected $_db;
    protected $_name = 'user_to_departments';

    public function __construct() {
        $this->_db = Zend_Registry::get('db');
    }

   public function getAllUsersFromDepartments(){
 
         $query = $this->select();
		$query->from('user_to_departments');
        $result = $this->fetchAll($query);
        //echo '<prE>'.print_r($result,true);die();
        if(!empty($result)) {
            $result = $result->toArray();

            //echo '<prE>'.print_r($result,true);die();
            return $result;
        }
        
        return array();  	

   }
   
  function getUserFromDepartmentsByRCID($id)
  {
         $query = $this->select();
		$query->from('user_to_departments')->where("rcID = ?", $id);
        $result = $this->fetchRow($query);
        //echo '<prE>'.print_r($result,true);die();
        if(!empty($result)) {
            $result = $result->toArray();

            //echo '<prE>'.print_r($result,true);die();
            return $result;
        }
        
        return array();  	 	
  } 
   
   function getUserFromDepartmentsByName($name)
   {

        $query = $this->select();
		$query->from('user_to_departments')->where("name = ?", $name);
        $result = $this->fetchRow($query);
        //echo '<prE>'.print_r($result,true);die();
        if(!empty($result)) {
            $result = $result->toArray();

            //echo '<prE>'.print_r($result,true);die();
            return $result;
        }
        
        return array();  	  
   }
   
   function addUserToDepartment($data)
   {
   		//echo '<pre>'.print_r($data,true).'</pre>';die();
   		$this->insert($data);
   }
 
    function getUserFromDepartmentsByID($id)
   {

        $query = $this->select();
		$query->from('user_to_departments')->where("id = ?", $id);
        $result = $this->fetchRow($query);
        //echo '<prE>'.print_r($result,true);die();
        if(!empty($result)) {
            $result = $result->toArray();

            //echo '<prE>'.print_r($result,true);die();
            return $result;
        }
        
        return array();  	  
   }
   
   function updateUserToDepartment($id,$data)	
   {
   		$where = $this->getAdapter()->quoteInto('id = ?', $id);
        
        $this->update($data, $where);
		
   }
   
   function deleteSelectedCategory($id)
   {
	   $where = $this->getAdapter()->quoteInto("id = ?", $id);

	    $this->delete($where);
	  
	 		
   }
 
 
   function deleteSelectedCategories($categories)
   {
   	   
		for($i=0;$i<count($categories);$i++)
		{
			 $where = $this->getAdapter()->quoteInto("id = ?", $categories[$i]);
			 $this->delete($where);
			 
		}		
   }  

	/*
	 * Gets all the categories whith the selected filter
	 */   
   function getFilterdUsers($filtru1,$filtru2,$filtru3)
   {
	
	    //mssql_select_db('productivo', $link);
   		$query = $this->select();
   		if(!empty($filtru1) && empty($filtru2) && empty($filtru3))
		{
			$query->from('categories')->where('type =?',$filtru1);
			
		}
		if(!empty($filtru1) && !empty($filtru2) && empty($filtru3))
		{
			$query->from('categories')->where('type =?',$filtru1)
				->orWhere('type =?',$filtru2);
		}
		if(!empty($filtru1) && empty($filtru2) && !empty($filtru3))
		{
			$query->from('categories')->where('type =?',$filtru1)
				->orWhere('type =?',$filtru3);
			
		}
		if(!empty($filtru1) && !empty($filtru2) && !empty($filtru3))
		{
			$query->from('categories')->where('type =?',$filtru1)
				->orWhere('type =?',$filtru2)
				->orWhere('type =?',$filtru3);
			
		}
		
		 $result = $this->fetchAll($query);
        //echo '<prE>'.print_r($result,true);die();
        if(!empty($result)) {
            $result = $result->toArray();

            //echo '<prE>'.print_r($result,true);die();
            return $result;
        }
        
        return array();						
   } 
}