<?php
class ContactForm_IndexController extends GoogleMap_Library_Controller_Action_Abstract {

    public function init() {
    }

    public function indexAction() {

        $form = new ContactForm_Form_frmContact(array('id' => 'contactForm'));
        
        if (!empty($_POST) && $form->isValid($_POST)) {
           $this->view->formSucces = 1;
        } elseif ($_POST) {
            $form->populate($_POST);
        }
        $this->view->form = $form;
    }

}
?>