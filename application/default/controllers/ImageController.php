<?php

	class ImageController extends Zend_Controller_Action
	{
		
	    public function init() 
	    {
	        //$this->_helper->layout->disableLayout();
	       // $this->_helper->viewRenderer->setNeverRender();
	    }
	
	    public function indexAction()
	    {
	        throw new Exception('Not a valid Image request');
	    }	
			
		public function geticonAction()
		{
			 
			  $data = $this->_request->getParam('data');
			  
			  if (!empty($data)) {
			   
			   $data = base64_decode($data);
			   $im = imagecreatefromstring($data);
			
			   // Genereaza imaginea ca si fisier gif
			   header('Content-Type: image/gif');
			   if ($im !== false) {
			    imagegif($im);
				
			   }
			    imagedestroy($im);
			  
		  	}
		}
	}

?>