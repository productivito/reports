<?php

class Application_Table_dbRemoteUsers extends Zend_Db_Table_Abstract {

    protected $_db;
    protected $_name = 'RemoteUsers';
    protected $_primary = "id";

    public function __construct() {
        $this->_db = Zend_Registry::get('db');
    }

   public function getUsers(){
 
        $query = $this->select();
		$query->from('RemoteUsers');
        $result = $this->fetchAll($query);
        
        if(!empty($result)) {
            $result = $result->toArray();

            //echo '<prE>'.print_r($result,true);die();
            return $result;
        }
        
        return array();  	
   }
 
   
   function addUser($data)
   {
   		$this->insert($data);
   }
   
 
 
}