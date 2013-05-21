<?php

class Login_Table_dbUser extends Zend_Db_Table_Abstract {

    protected $_db;
    protected $_name = 'user';

    public function __construct() {
        parent::__construct();
        $this->_objAdapter = $this->getAdapter();
    }
    
    public function checkEmail($email, $dob = '') {
        if(!empty($email)){
            if($dob != ''){
                $sql = $this->_objAdapter->fetchAll("CALL getUserByEmailAndDob('". (string)$email."', '". (string)$dob."');");
            } else
                $sql = $this->_objAdapter->fetchAll("CALL getUserDetailsByEmail('". (string)$email."');");
            return $sql;
        }
        return array();
    }
    public function resetPasswordByUserID($intUserID,$strNewPassword){
        if (isset($intUserID) && is_numeric($intUserID)) {
            $strWhere = $this->_db->quoteInto('user_ID = ?', $intUserID);
            $this->_db->update('user', array('user_password' => md5($strNewPassword) ), $strWhere);
        }
    }
}