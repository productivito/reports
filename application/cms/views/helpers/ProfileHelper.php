<?php

class Zend_View_Helper_ProfileHelper extends Zend_View_Helper_Abstract {
    
	function profileHelper() { 

		$sProfile ='';
		$oAuth	= Zend_Auth::getInstance();
		if($oAuth->hasIdentity()) {
			$userInfo = $oAuth->getStorage()->read();
			$sProfile .='<div id="Profile">
                                        <div id="info"> <p>
                                                Username: <strong>'.$userInfo->username.'</strong>
                                                <a href="'.$this->view->baseUrl('index/logout').'" style="margin-left:60px;">Logout</a>
                                                </p>
                                        </div>
                                    </div>';
		}
        return $sProfile;
    }

}
