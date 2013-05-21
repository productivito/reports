<?php
class ImageController extends Zend_Controller_Action
{

	public function init()
	{

	
	}
	
	public function indexAction()
	{

        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
		
		if($this->_hasParam('folder')&&is_dir('../public/img/upload/'.$this->_getParam('folder'))) $path = '../public/img/upload/'.$this->_getParam('folder');
		else $path = '../public/img/upload/';
		
		if($this->_hasParam('img')&&is_file($path.'/'.$this->_getParam('img'))) $img = $path.'/'.$this->_getParam('img');
		else die('this is not a valid resource');
		

		$im = @imagecreatefromjpeg($img);


	    if($im)
     	{
     		
			header('Content-Type: image/jpeg');
			
			imagejpeg($im);
			imagedestroy($img);
			
		}
     	else die('this is not a valid resource');
	} 
	
	
	

}