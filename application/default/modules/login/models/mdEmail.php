<?php

class Login_Model_mdEmail {
    
    public function init() {
        
    }
    
    public function checkEmail($email, $dob = '') {
        $dbEmail = new Login_Table_dbUser();
        if($dob != '')
            $arrEmail = $dbEmail->checkEmail($email, $dob);
        else
            $arrEmail = $dbEmail->checkEmail($email);
        return $arrEmail;
    }
   
}

?>