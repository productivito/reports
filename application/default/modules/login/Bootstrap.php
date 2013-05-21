<?php

class Login_Bootstrap extends Zend_Application_Module_Bootstrap {


    protected function _initLibraryAutoloader() {

       return $this->getResourceLoader()->addResourceType('library','library','library')
                                        ->addResourceType('object', 'objects', 'Object')
                                        ->addResourceType('table', 'table', 'Table')
                                        ->addResourceType('form', 'forms', 'Form');

    }




}
?>
