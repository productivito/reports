<?php
	class Zend_View_Helper_LanguageHelper extends Zend_View_Helper_Abstract
	{  
	    function languageHelper($languageId,$languageUpper = true) {
                $translate = new Zend_Translate(

                array(
                    'adapter' => 'gettext',
                    'content' => '../languages/lang.mo',
                    'locale'  => 'nl'
                )

            );
            $translate->setLocale('nl');
            $translatedStr = strtolower($translate->_($languageId));
            if($languageUpper === true){
               $translatedStr = ucfirst($translatedStr);
            }
            return '#'.$translatedStr.'#';
	    }
	}
?>