<?php

class Zend_View_Helper_MceHelper extends Zend_View_Helper_Abstract {
    
	function mceHelper() { 
		return $sScript='<script type="text/javascript" src="'.$this->view->baseUrl('js/tiny_mce/tiny_mce.js').'"></script>

<script type="text/javascript">
	tinyMCE.init({
		// General options
		mode : "textareas",
		theme : "advanced",
		plugins : "pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,inlinepopups,autosave",

		// Theme options
		theme_advanced_buttons1 : "bold,cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,undo,redo,|,link,unlink,anchor",
		theme_advanced_buttons2 : "",
		theme_advanced_buttons3 : "",
		theme_advanced_buttons4 : "",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true
		});
</script>
';

    }

}
?>