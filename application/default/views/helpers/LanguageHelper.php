<?php

class Zend_View_Helper_LanguageHelper extends Zend_View_Helper_Abstract {

    function languageHelper($languageId, $languageUpper = true,$disableToLower = false) {
        $zendConfig = Zend_Registry::get('config')->toArray();
        //echo APPLICATION_PATH;die();

        foreach ($zendConfig['app']['languages'] as $strLangCode => $strLangFile) {
            if (empty($objTranslate)) {
                $objTranslate = new Zend_Translate(array(
                            'adapter' => 'ini',
                            'content' => '../languages/' . $strLangFile,
                            'locale' => $strLangCode
                        ));
            } else {
                $objTranslate->addTranslation(
                        array(
                            'content' => '../languages/' . $strLangFile,
                            'locale' => $strLangCode
                ));
            }
        }

        $objTranslate->setLocale('nl');
        if (!empty($frontendUser->userDetails['language_code'])) {
            if ($objTranslate->isAvailable($this->_languageCode)) {
                $objTranslate->setLocale($this->_languageCode);
            }
        }
        $translatedStr = $objTranslate->_($languageId);
        if($disableToLower === false){
            $translatedStr = strtolower($objTranslate->_($languageId));
        }
        if ($languageUpper === true) {
            $translatedStr = ucfirst($translatedStr);
        }
        //return '#'.$translatedStr.'#';
        /* Translate tester */
        //$translatedStr = "###";
        return $translatedStr;
    }

}
?>