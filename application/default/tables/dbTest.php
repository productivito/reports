<?php

//Frontend FAQ model
class Application_Table_dbTest {

    protected $_db;

    public function __construct() {
        $this->_db = Zend_Registry::get('db');
    }

    /* below andrei's functions */

    public function getAll() {
        $sql = "SELECT * FROM `test`";

        $aResult = $this->_db->fetchAll($sql);
        //print_r($aResult);

        //die(var_dump($aReturnData));
        return $aResult;
    }
}