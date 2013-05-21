<?php

//  cms

class OffersController extends Zend_Controller_Action
{
    protected $_offers;


    public function init()
    {
		if(!Zend_Auth::getInstance()->hasIdentity()){
			$store = new Zend_Session_Namespace('store_cms');
			if (!$store->logged) {
	        	$store->controller = $this->getRequest()->getControllerName();
                        $store->action = $this->getRequest()->getActionName();
			$store->params = $this->_getAllParams();
                        $this->_redirect('index');
	        } 
	  	}
        $this->_offers= new Application_Model_offers();
 

    }

    public function indexAction()
    {
    
			$aAllOffers =$this->_offers->getAllOffers();
			$this->view->layout()->title = 'Offers';
			$this->view->name = 'offers';
			$this->view->rows = $aAllOffers;
			$this->view->content = '';
			$this->renderScript('offers/index.phtml');

    }

    public function chooseinterestsAction()
    {
    
    		$id = $this->_getParam('id');
			$aInterestsbyOffer =$this->_offers->getInterestsByOfferId($id);
			if(empty($aInterestsbyOffer)){
				$this->view->empty=1;
				$this->view->aInterestsbyOffer = $aInterestsbyOffer;
				$aInterestsWithOutOffer = $this->_offers->getInterests();
				$this->view->aInterestsWithOutOffer = $aInterestsWithOutOffer;
			}else{
				$aInterestsWithOutOffer = $this->_offers->getInterestsNotOfOfferId($id, $aInterestsbyOffer);
				$this->view->aInterestsbyOffer = $aInterestsbyOffer;
				$this->view->aInterestsWithOutOffer = $aInterestsWithOutOffer;
			}
			$this->view->layout()->title = 'Choose Interests';
			$this->view->name = 'choose Interests';
			$this->view->id = $id;
			$this->view->content = '';
			$this->renderScript('offers/choose.phtml');

    }
    
	public function newAction(){
		$this->view->layout()->title = 'Create new offer';
		$form = $this->getForm('offers/new');
		if($_POST){
			$form->populate($form->getValues());
		}
		 if ($_POST && $form->isValid($_POST)) {
           	$aData = $this->_offers->saveoffer($form->getValues());
			if ($form->img->receive()) {
				$fileName = $form->img->getValue();
				
				if(!empty($fileName)) {
					$this->_offers->_createThumbnail($form->img->getFileName());
				}
			}
			$form->populate($aData);
			
			$this->view->content = '<p class="OK">Offer '.$aData['title'].' has been created <p>';
        }
		
		$this->view->form = $form;
		$this->renderScript('offers/form.phtml');
	}
	

	

	
	public function editAction(){
		
		$nFaqId = $this->_getParam('id');
		$form = $this->getForm('offers/edit/id/'.$nFaqId);
		$aData=$this->_offers->getOffersData($nFaqId);
		$form->populate($aData);
		if($_POST){
			$form->populate($form->getValues());
		}
		if ($_POST && $form->isValid($_POST)) {
           	$aData = $this->_offers->saveoffer($form->getValues());
			$form->populate($aData);

			if ($form->img->receive()) {
				$fileName = $form->img->getValue();
				
				if(!empty($fileName)) {
					$this->_offers->_createThumbnail($form->img->getFileName());
				}
			}
			

			$this->view->content = '<p class="OK">Offer '.$aData['title'].' has been edited <p>';
        }
/*       echo "<pre>";
        var_dump($aData);
        echo "</pre>";
        die();*/
		$this->view->form = $form;
		$this->view->layout()->title = 'Edit Offer - '.$aData['title'];
		$this->renderScript('offers/form.phtml');
	}
	
	public function deleteAction(){
		$nFaqId = $this->_getParam('id');
		$aData=$this->_offers->getOffersData($nFaqId);
		$this->view->layout()->title = 'Delete FAQ - '.$aData['title'];
		$form = $this->getDeleteForm('offers/delete/id/'.$nFaqId,$aData);
		$form->populate($aData);
		if($_POST){
			if ($_POST['delete']){
				$this->_offers->deleteOffers($this->_getParam('id'));
				$this->_redirect('offers');
			}
			if ($_POST['cancel']){
				$this->_redirect('offers');
			}
		}
		$this->view->content = '<p class="errors">Are you sure you want to delete '.$aData['title'].' offer<p>';
		$this->view->form = $form;
		$this->renderScript('offers/form.phtml');
		
	}

      
	
	public function getDeleteForm($sUrl,$aPageDate)
    {
	
		$form = new Zend_Form();
      	$form->setAction($this->view->baseUrl($sUrl))
           ->setAttrib('autocomplete', 'off')
           ->setMethod('post');

		$faq_id = $form->createElement('hidden', 'faq_ID')
						->removeDecorator('htmlTag')
						->removeDecorator('label');

		$submit = $form->createElement('submit', 'delete',array('class' =>'submit'))
						->removeDecorator('htmlTag')
						->removeDecorator('DtDdWrapper')
						->removeDecorator('label');
		$cancel = $form->createElement('submit', 'cancel',array('class' =>'submit'))
						->removeDecorator('htmlTag')
						->removeDecorator('DtDdWrapper')
						->removeDecorator('label');
		$form->addElements(array($faq_id,$cancel, $submit));
		
		$form->addDisplayGroup(array('faq_ID','delete','cancel'), 'buttons'); 

		$form->getDisplayGroup('buttons');
		$form->setDisplayGroupDecorators(
		array(
		'FormElements',
		array('HtmlTag', array('tag' => 'div', 'class' => 'FormRow'))
		)
		);         
		return $form;
    }

	public function getForm($sUrl)
    {

		$aData=array('faq_ID' =>0); 
		
                $form = new Zend_Form();
                $form->setAction($this->view->baseUrl($sUrl))
                        ->setAttrib('autocomplete', 'off')
                        ->setMethod('post');


	  $pagetitle = $form->createElement('text', 'title');
          $pagetitle->setLabel('Title')
                   ->addValidator('stringLength', false, array(1, 200))
                  ->setAttrib('autocomplete', 'off')
                   ->setRequired(true)
				->setDecorators(array(
				'ViewHelper',
				'Errors',
				array('Label', array('placement' => 'prepend')),
				array('HtmlTag', array('tag' => 'div', 'class' => 'FormRow'))
				))
			   ;
	  $form->addElement($pagetitle);



          $active = $form->createElement('radio', 'active');
	  $active->setLabel('Active')
                        ->setRequired(true)
			->addMultiOptions(array('yes' => 'Ja', 'no' => 'Nee'))
			->setValue(array('no','Nee'))
                        ->setDecorators(array(
                            'ViewHelper',    
                            'Errors',
                            array('Label', array('placement' => 'prepend')),
			    array('HtmlTag', array('tag' => 'div', 'class' => 'CheckBoxes'))
			                ))
			        ;
                 $form->addElement($active);



                $start_date = $form->createElement('text', 'start_date', array('class' => 'Arrow','id'=>'datepicker_start'));
                                        $start_date->setLabel('Start date')
                                        ->setValue(date('Y-m-d H:i:s'))
                                        ->setAttrib('autocomplete', 'off')
					->setDecorators(array(
			                    'ViewHelper',
			                    'Errors',
			                    array('Label', array('placement' => 'prepend', 'class' => 'Arrow', 'style' => 'height:50px')),
			                    array('HtmlTag', array('tag' => 'div', 'class' => 'FormRow shortLabel'))
			                ))
			        ;
                 $form->addElement($start_date);




                $end_date = $form->createElement('text', 'end_date', array('class' => 'Arrow','id'=>'datepicker_exp'));
                                        $end_date->setLabel('End date')
                                                ->setAttrib('autocomplete', 'off')
                                                ->setValue('2030-12-31 00:00:00')
                                                ->setDecorators(array(
                                                    'ViewHelper',
                                                    'Errors',
                                                    array('Label', array('placement' => 'prepend', 'class' => 'Arrow', 'style' => 'height:50px')),
                                                    array('HtmlTag', array('tag' => 'div', 'class' => 'FormRow shortLabel'))
			                ))
			        ;
                 $form->addElement($end_date);

	  $offers_contact_company = $form->createElement('text', 'offers_contact_company');
          $offers_contact_company->setLabel('Contact company')
                   ->addValidator('stringLength', false, array(1, 200))
                  ->setAttrib('autocomplete', 'off')
				->setDecorators(array(
				'ViewHelper',
				'Errors',
				array('Label', array('placement' => 'prepend')),
				array('HtmlTag', array('tag' => 'div', 'class' => 'FormRow'))
				))
			   ;
	  $form->addElement($offers_contact_company);

	  $offers_contact_street = $form->createElement('text', 'offers_contact_street');
          $offers_contact_street->setLabel('Contact street')
                   ->addValidator('stringLength', false, array(1, 200))
                  ->setAttrib('autocomplete', 'off')
				->setDecorators(array(
				'ViewHelper',
				'Errors',
				array('Label', array('placement' => 'prepend')),
				array('HtmlTag', array('tag' => 'div', 'class' => 'FormRow'))
				))
			   ;
	  $form->addElement($offers_contact_street);

	  $offers_contact_city = $form->createElement('text', 'offers_contact_city');
          $offers_contact_city->setLabel('Contact city')
                   ->addValidator('stringLength', false, array(1, 200))
                  ->setAttrib('autocomplete', 'off')
				->setDecorators(array(
				'ViewHelper',
				'Errors',
				array('Label', array('placement' => 'prepend')),
				array('HtmlTag', array('tag' => 'div', 'class' => 'FormRow'))
				))
			   ;
	  $form->addElement($offers_contact_city);

	  $offers_contact_country = $form->createElement('text', 'offers_contact_country');
          $offers_contact_country->setLabel('Contact country')
                   ->addValidator('stringLength', false, array(1, 200))
                  ->setAttrib('autocomplete', 'off')
				->setDecorators(array(
				'ViewHelper',
				'Errors',
				array('Label', array('placement' => 'prepend')),
				array('HtmlTag', array('tag' => 'div', 'class' => 'FormRow'))
				))
			   ;
	  $form->addElement($offers_contact_country);

	  $offers_contact_phone = $form->createElement('text', 'offers_contact_phone');
          $offers_contact_phone->setLabel('Contact phone')
                   ->addValidator('stringLength', false, array(1, 200))
                  ->setAttrib('autocomplete', 'off')
				->setDecorators(array(
				'ViewHelper',
				'Errors',
				array('Label', array('placement' => 'prepend')),
				array('HtmlTag', array('tag' => 'div', 'class' => 'FormRow'))
				))
			   ;
	  $form->addElement($offers_contact_phone);


	   	$short_description = $form->createElement('textarea', 'short_description');
		$short_description->setLabel('Short description: ')
				   ->setRequired(true)
                                   ->setAttrib('autocomplete', 'off')
					->setDecorators(array(
					'ViewHelper',
					'Errors',
					array('Label', array('placement' => 'prepend')),
					array('HtmlTag', array('tag' => 'div', 'class' => 'FormRow'))
					))
				   ;

		  $form->addElement($short_description);
		  
		  $faq_answer = $form->createElement('textarea', 'content');
		  $faq_answer->setLabel('Content: ')
				   ->setRequired(true)
                          ->setAttrib('autocomplete', 'off')
					->setDecorators(array(
					'ViewHelper',
					'Errors',
					array('Label', array('placement' => 'prepend')),
					array('HtmlTag', array('tag' => 'div', 'class' => 'FormRow'))
					))
				   ;

		  $form->addElement($faq_answer);



	     /* $file = new Zend_Form_Element_File('img');
	      $file->setLabel('Upload an image:')
	              ->setDestination('upload');
	      $file->addValidator('Count', false, 1);
	      $file->setAttrib('enctype', 'multipart/form-data');
	      $file->addValidator('Size', false, 102400);
	      $file->addValidator('Extension', false, 'jpg,png,gif');*/

	      $file = $form->createElement('file', 'img');
		  @$file->setLabel('Upload an image:')
					->addValidator('Size', false, 1024000)
					->addValidator('Extension', false, 'jpg')
					->setAttrib('enctype', 'multipart/form-data')
                    ->setDestination('../public/img/upload/offers')
					->setDecorators(array(
					'File',
					'Errors',
					array('Label', array('placement' => 'prepend')),
					array('HtmlTag', array('tag' => 'div', 'class' => 'FormRow'))
					))
				   ;
	      
	      $form->addElement($file);
		  
		  $pdf = $form->createElement('file', 'pdf');
		  @$pdf->setLabel('Upload a PDF file:')
					->addValidator('Size', false, 10240000)
					->addValidator('Extension', false, 'pdf')
					->setAttrib('enctype', 'multipart/form-data')
                    ->setDestination('../public/img/upload/offers')
					->setDecorators(array(
					'File',
					'Errors',
					array('Label', array('placement' => 'prepend')),
					array('HtmlTag', array('tag' => 'div', 'class' => 'FormRow'))
					))
				   ;
	      
	      $form->addElement($pdf);	
	      
              $creation_date = $form ->createElement('hidden', 'creation_date')
						  ->removeDecorator('htmlTag')
						  ->removeDecorator('label');
		
              $form->addElement($creation_date);
		  	      
		  
		  
              $faq_id = $form->createElement('hidden', 'id');
		  
              $submit = $form->createElement('submit', 'submit',array('class' =>'submit'));
              $submit->setLabel('submit')
					->setDecorators(array(
					'ViewHelper',
					array('HtmlTag', array('tag' => 'div', 'class' => 'FormRow'))
					))
				   ;
		$form->addElement($faq_id);
		$form->addElement($submit);
        return $form;
    }
 
    public function saveofferinterestsAction(){
    
    	/* prevents no action phtml from the view is loaded */
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
        
        $id = $_POST['offer_id'];
        $interests = explode(",",$_POST['interests']);
		unset($interests[0]);

        $this->_offers->saveInterestsOffer($id,$interests);
        
        $data = $this->_request->getParams();
        $arrayToJson = array('test'	=> 33, 'field' => 'tho');
        echo json_encode($arrayToJson);
        exit;

    }
    
    public function ajaxAction(){
    
    	/* prevents no action phtml from the view is loaded */
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
        
        $id = $_POST['id'];
        $order = $_POST['order'];
        
        $this->_offers->setOffersOrder($id,$order);
        
        $data = $this->_request->getParams();
        $arrayToJson = array('test'	=> 33, 'field' => 'tho');
        echo json_encode($arrayToJson);
        exit;

    }
    
}
