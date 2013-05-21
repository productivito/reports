<?php

class SiteOffers_Bootstrap extends Zend_Application_Module_Bootstrap {

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
        $autoLoaderResource = new Zend_Loader_Autoloader_Resource(
                        array(
                            'basePath' => APPLICATION_PATH,
                            'namespace' => 'Application'
                        )
        );
        // The name of the Class. Firstname is the Application_[yourname]_[filename] without capitals, the socond the directory name -> application/yourname/filename and
        // the third the same as the first but only with first letter capitalised. This is the one you call like Application_Yourname_Filename
       return $this->getResourceLoader()->addResourceType('library','library','library')
                                        ->addResourceType('object', 'objects', 'Object')
                                        ->addResourceType('table', 'tables', 'Table');
    }

}
?>
