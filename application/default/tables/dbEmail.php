<?php

class Application_Table_dbEmail extends Zend_Db_Table_Abstract {

    protected $_db;
    protected $_name = 'email';

    public function __construct() {
        $this->_db = Zend_Registry::get('db');
    }

   public function getAllReports(){
 
         $query = $this->select();
		$query->from('email');
        $result = $this->fetchAll($query);
        //echo '<prE>'.print_r($result,true);die();
        if(!empty($result)) {
            $result = $result->toArray();

            //echo '<prE>'.print_r($result,true);die();
            return $result;
        }
        
        return array();  	

   }
   
  function getReportAlertById($id)
  {
         $query = $this->select();
		$query->from('email')->where("id = ?", $id);
        $result = $this->fetchRow($query);
        //echo '<prE>'.print_r($result,true);die();
        if(!empty($result)) {
            $result = $result->toArray();

            //echo '<prE>'.print_r($result,true);die();
            return $result;
        }
        
        return array();  	 	
  } 
  
  function getReportAlertsByType($type)
  {
         $query = $this->select();
		$query->from('email')->where("type = ?", $type);
        $result = $this->fetchAll($query);
        //echo '<prE>'.print_r($result,true);die();
        if(!empty($result)) {
            $result = $result->toArray();

            //echo '<prE>'.print_r($result,true);die();
            return $result;
        }
        
        return array();  	 	
  }  
  
   
   function getReportAlertByName($name)
   {

        $query = $this->select();
		$query->from('email')->where("name = ?", $name);
        $result = $this->fetchRow($query);
        //echo '<prE>'.print_r($result,true);die();
        if(!empty($result)) {
            $result = $result->toArray();

            //echo '<prE>'.print_r($result,true);die();
            return $result;
        }
        
        return array();  	  
   }
   
   function addReportAlert($data)
   {
   		//echo '<pre>'.print_r($data,true).'</pre>';die();
   		$this->insert($data);
   }
 
   
   function updateReportAlert($id,$data)	
   {
   		$where = $this->getAdapter()->quoteInto('id = ?', $id);
        
        $this->update($data, $where);
		
   }
   
   function deleteSelectedReportAlert($id)
   {
   	
	   $where = $this->getAdapter()->quoteInto("id = ?", $id);

	    $this->delete($where);
	  
	 		
   }
 
 
   function deleteSelectedReportsAlerts($reports)
   {
   	   
		for($i=0;$i<count($reports);$i++)
		{
			 $where = $this->getAdapter()->quoteInto("id = ?", $reports[$i]);
			 $this->delete($where);
			 
		}		
   }  

	/*
	 * Gets all the categories whith the selected filter
	 */   
   function getFilterdUsers($filtru1,$filtru2,$filtru3)
   {
	
	    //mssql_select_db('productivito', $link);
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
