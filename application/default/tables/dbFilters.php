<?php

class Application_Table_dbFilters extends Zend_Db_Table_Abstract {

    protected $_db;
    protected $_name = 'filters';

    public function __construct() {
        $this->_db = Zend_Registry::get('db');
    }

   public function getAllFilters(){
 
         $query = $this->select();
		 $query->from('filters');
        $result = $this->fetchAll($query);
        //echo '<prE>'.print_r($result,true);die();
        if(!empty($result)) {
            $result = $result->toArray();

            //echo '<prE>'.print_r($result,true);die();
            return $result;
        }
        
        return array();  	

   }
   
   function getFilterById($id)
   {

        $query = $this->select();
		$query->from('filters')->where("id = ?", $id);
        $result = $this->fetchRow($query);
        //echo '<prE>'.print_r($result,true);die();
        if(!empty($result)) {
            $result = $result->toArray();

            //echo '<prE>'.print_r($result,true);die();
            return $result;
        }
        
        return array();  	  
   }
   
   function updateFilter($id,$data)	
   {
   		$where = $this->getAdapter()->quoteInto('id = ?', $id);
		
        $this->update($data, $where);
		
   }
   
   function addFilter($data)
   {
   		$this->insert($data);
   }
   
   function deleteSelectedFilter($id)
   {
	   $where = $this->getAdapter()->quoteInto("id = ?", $id);

	    $this->delete($where);
	  
	 		
   }
 
   function deleteSelectedFilters($filters)
   {
   	   
		for($i=0;$i<count($filters);$i++)
		{
			 $where = $this->getAdapter()->quoteInto("id = ?", $filters[$i]);
			 $this->delete($where);
			 
		}		
   }  
}