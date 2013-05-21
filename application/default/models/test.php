<?php

class Application_Model_test {

    public function __construct() {
        $this->db = Zend_Registry::get('db');

    }
    
    public function getAll() {
        $dbTest = new Application_Table_dbTest();
        return $dbTest->getAll();
    }

}