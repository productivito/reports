<?php

class Application_Table_dbEmailAddress extends Zend_Db_Table_Abstract {

    protected $_db;
    protected $_name = 'EmailAddress';
    protected $_primary = "id";

    public function __construct() {
        $this->_db = Zend_Registry::get('db');
    }

   public function getAddress(){
 
        $query = $this->select();
		$query->from('EmailAddress');
        $result = $this->fetchRow($query);
        
        if(!empty($result)) {
            $result = $result->toArray();

            //echo '<prE>'.print_r($result,true);die();
            return $result;
        }
        
        return array();  	

   }
 
   
   function updateAddress($data)	
   {
   		$where = $this->getAdapter()->quoteInto('id = ?', 1);
		
        $this->update($data, $where);
		
   }
   
   function addAddress($data)
   {
   		$this->insert($data);
   }
   
 
 
}