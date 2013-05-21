<?php

class IndexController extends Zend_Controller_Action {

    protected $permissions;

    public function init() {

        $this->permissions = new Application_Model_permissions();
    }

    public function getForm() {

        $form = new Zend_Form();
        $form->setAction($this->view->baseUrl('index/login'))
                ->setMethod('post');

        $username = $form->createElement('text', 'username');
        $username->addValidator('alnum')
                ->setLabel('Username')
                ->addValidator('regex', false, array('/^[a-z]+/'))
                ->addValidator('stringLength', false, array(4, 20))
                ->setRequired(true)
                ->addFilter('StringToLower')
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array('Label', array('placement' => 'prepend')),
                    array('HtmlTag', array('tag' => 'div', 'class' => 'FormRow'))
                ))
        ;

        $password = $form->createElement('password', 'password');
        $password->addValidator('StringLength', false, array(4, 20))
                ->setLabel('Password')
                ->setRequired(true)
                ->setDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array('Label', array('placement' => 'prepend')),
                    array('HtmlTag', array('tag' => 'div', 'class' => 'FormRow'))
                ))
        ;
        
        $submit = $form->createElement('submit', 'submit', array('class' => 'submit'));
        $submit->setLabel('submit')
                ->setDecorators(array(
                    'ViewHelper',
                    array('HtmlTag', array('tag' => 'div', 'class' => 'FormRow'))
                ))
        ;

        $form->addElement($username)
                ->addElement($password)
                ->addElement($submit);
        // use addElement() as a factory to create 'Login' button:

        return $form;
    }

    public function indexAction() {
        $auth = Zend_Auth::getInstance();
        $this->_helper->layout->setLayout('login_layout');

        if ($auth->hasIdentity()) {
            $this->_redirect('index/home');
        }

        // render cms/login.phtml
        $this->view->form = $this->getForm();
        $this->render('login');
    }

    public function logoutAction() {
        $auth = Zend_Auth::getInstance();
        $auth->clearIdentity();
        $this->_redirect('index/login');
    }

    public function loginAction() {
        $auth = Zend_Auth::getInstance();
        $this->_helper->layout->setLayout('login_layout');

        if ($auth->hasIdentity()) {
            $this->_redirect('index/home');
        }
        if (!$this->getRequest()->isPost()) {
            return $this->_forward('index');
        }
        $form = $this->getForm();
        if (!$form->isValid($_POST)) {
            // Failed validation; redisplay form
            $this->view->form = $form;
            return $this->render('login');
        }
        $values = $form->getValues();

        $username = $values['username'];
        $password = $values['password'];

        $db = Zend_Registry::get('db');
        $auth = Zend_Auth::getInstance();
        //	$auth_adapter	= new Zend_Auth_Adapter_DbTable($db, 'cms_user', 'cms_user_username', 'cms_user_password', 'md5(?)');
        $auth_adapter = new Zend_Auth_Adapter_DbTable($db, 'cms_user', 'username', 'pasword', 'md5(?)');



        $auth_adapter->setIdentity($username)->setCredential($password);

        $auth_result = $auth->authenticate($auth_adapter);
        if ($auth_result->isValid()) {

            $user_data = $auth_adapter->getResultRowObject(null, array('password_hash')); //wtf does this do?

            if ($this->permissions->getPermissions($user_data->id, 5)) { //only valid CMS users may log in
                $auth->getStorage()->write($user_data);
                $this->_redirect('index/home');
            }

            // if this line is reached redirecting went wrong for some reason, so we clear the identity just to be sure
            $auth->clearIdentity();
        }
        // login failed
        $form->addDecorator('Errors');
        $form->addErrorMessage('Gebruikersnaam en/of wachtwoord incorrect of de inlogtijd is verstreken');
        $form->markAsError();
        $this->view->form = $form;
    }

    public function homeAction() {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            $this->_redirect('index/login');
        }
        // render cms/login.phtml
        $this->view->layout()->title = 'AON';
        $this->view->content = '<p>Welkom in het AON CMS</p>';


        
    }

}
