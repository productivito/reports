<?php

class Login_IndexController extends Login_Library_Controller_Action_Abstract {

    public function init() {
        $this->db = Zend_Registry::get('db');
        $this->arrConfig = Zend_Registry::get('config')->toArray();

        if (isset($this->arrConfig['login'])) {
            $this->arrConfigPaths = $this->arrConfig['login'];
        } else {
            print_r('login module settings are not set in config.ini!<br /><br />');
            print_r("login.checkCredentials = 'lgin/login'<br />login.login = 'lgin/index'<br />login.loggedIn = 'lgin/logged'<br />login.forgotPassword = 'lgin/forgotpsw'<br />login.checkMail = 'lgin/index'<br />login.afterLogout = 'index/index'<br />login.dobCheck = 1");
            die;
        }

        if (!isset($this->arrConfigPaths['dobCheck'])) {
            print_r('login.dobCheck is not set in config.ini!');
            die;
        }
    }

    public function indexAction() {
    	
        $this->view->checkCredentialsLink = $this->arrConfigPaths['checkCredentials'];
        $this->view->loginLink = $this->arrConfigPaths['login'];
        $this->view->loggedInLink = $this->arrConfigPaths['loggedIn'];
        $this->view->firstLoggedInLink = $this->arrConfigPaths['firstLoggedIn'];
        $this->view->checkEmailLink = $this->arrConfigPaths['forgotPassword'];
        $this->view->dobCheck = $this->arrConfigPaths['dobCheck'];
        $strError = $this->getRequest()->getParam('error');
        $this->view->email = $this->getRequest()->getParam('email');
		
        $auth = Zend_Auth::getInstance();
        
        if ($auth->hasIdentity()) {
            $this->_redirect('index/rapoarte');
        } else {
			
			
				/*
				 * $auth = Zend_Auth::getInstance();
				 * $user = $auth->getIdentity();
				 */

            if ($this->view->dobCheck == '1')
                $this->view->months = array("januari", "februari", "maart", "april", "mei", "juni", "juli", "augustus", "september", "oktober", "november", "december");

            $this->view->error = '';
            $this->view->mailError = '';
            
            $arrCredentials = $this->getRequest()->getParam('credentials');
			
			//echo '<pre>'.var_dump($arrCredentials,true)."</pre>";die();
            //echo 'KOWEEK';die();
			
            if (!empty($arrCredentials)) {
                
                //$firstLogin = $this->getRequest()->getParam('first_login');
                
                Zend_Loader::loadClass('Zend_Auth_Adapter_DbTable');
                $auth = new Zend_Auth_Adapter_DbTable($this->db, 'users', 'username', 'password');

                $username = $arrCredentials['username'];
                $password = md5($arrCredentials['password']);

                $auth->setIdentity($username)->setCredential($password);
                $result = $auth->authenticate();
				
                if ($result->isValid()) {

                    $objUserInfo = $auth->getResultRowObject();

                    $storage = new Zend_Auth_Storage_Session();
                    $storage->write($objUserInfo);
					
                    $objAuth = Zend_Auth::getInstance();
					
                    $objAuth->setStorage($storage);
					
                    $userInfo = $objAuth->getIdentity();
					
                    if (!empty($userInfo)) {
                        //if(!empty($firstLogin) && $firstLogin == 'true')
                          //  $this->_redirect($this->view->firstLoggedInLink);
                        $this->_redirect('index/rapoarte');
                    }
                }

                $this->view->error = 'Username-ul sau parola sunt incorecte!';
            } elseif(is_array($arrCredentials)) {
                $this->view->error = 'Username-ul sau parola sunt incorecte!';
            }

            if ($strError == '1')
                $this->view->mailError = 'Username-ul sau email-ul nu exista';
            else if ($strError == 'dob') {
                $this->view->mailError = 'Introdu data nasterii';
                $this->view->dob = 1;
            }
        }
    }

    public function forgotpswAction() {
        $email = $this->getRequest()->getParam('email');
        $month = $this->getRequest()->getParam('month');
        $year = $this->getRequest()->getParam('year');
        $day = $this->getRequest()->getParam('day');
        $this->view->loginLink = $this->arrConfigPaths['login'];
        $this->view->checkMailLink = $this->arrConfigPaths['checkMail'];

        if (!empty($email)) {

            if (!empty($month) && !empty($year) && !empty($day)) {
                $dob = $year . '-' . $month . '-' . $day . ' ' . '00:00:00';
            }

            $mdEmail = new Login_Model_mdEmail();
            $dbUser = new Login_Table_dbUser();

            if (isset($dob))
                $arrUser = $mdEmail->checkEmail($email, $dob);
            else
                $arrUser = $mdEmail->checkEmail($email);

            if (!empty($arrUser) && count($arrUser) == 1) {
                    
                    //echo '<pre>'.print_r($arrUser,true);
                    $arrForMail = array(); 
                    $arrForMail['user_username'] = $arrUser[0]['user_username'];
                    $arrForMail['new_password'] = Login_Model_mdLogin::generatePassword();
                    $dbUser->resetPasswordByUserID($arrUser[0]['user_ID'],$arrForMail['new_password']);
                    
                   $objMail = new Application_Object_objMail();
                   $objMail->setTemplate('forgotPassword', $arrForMail)
                    ->setSubject('FI cup - Inloggegevens')
                    ->setReceiver($arrUser[0]['user_business_email'])
                    ->sendMail();

                    $this->_redirect($this->view->checkMailLink);
                    

            } elseif (!empty($arrUser) && count($arrUser) > 1) {

                $this->_redirect($this->view->loginLink . '/mailerror/dob/email/' . $email);
            } else {
                $this->_redirect($this->view->loginLink . '/mailerror/1');
            }
        } else {
            $this->_redirect($this->view->loginLink . '/mailerror/1');
        }
    }

    public function logoutAction() {
        $this->view->logoutLink = $this->arrConfigPaths['afterLogout'];

        Zend_Session::destroy();
        $this->_redirect($this->view->logoutLink);
    }

}

?>
