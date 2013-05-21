<?php

class ContactForm_Bootstrap extends Zend_Application_Module_Bootstrap {

    protected $_translate;

    protected function _initLibraryAutoloader() {

      $this->_translate = 'EN';

      /*$translate = new Zend_Translate(
          array(
              'adapter' => 'ini',
              'content' => '/path/to/mytranslation.ini',
              'locale'  => 'de'
          )
      );
      $translate->addTranslation(
          array(
              'content' => '/path/to/other.ini',
              'locale' => 'it'
          )
      );*/

       return $this->getResourceLoader()->addResourceType('library','library','library');
    }

}
?>
