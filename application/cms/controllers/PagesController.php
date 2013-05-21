<?php
class PagesController extends Zend_Controller_Action
{

	public function init()
	{

		$this->_content= new Application_Model_pages();

	
	}
	
	public function indexAction()
	{
		
	
		
		$aAllContent =$this->_content->getAllContent();
		$this->view->layout()->title = 'Pages';
		$this->view->name = 'page';
		$this->view->rows = $aAllContent;
		$this->view->content = '';
		$this->renderScript('pages/index.phtml');
	} 
	
	public function editAction(){
	
	  $contentId = $this->_getParam('id');
	  $form = $this->getForm('pages/edit/id/'.$contentId);
	  $aData=$this->_content->getContentData($contentId);
		

	  $form->populate($aData);

	  if($_POST){
		  $form->populate($form->getValues());
	  }
	  if ($_POST && $form->isValid($_POST)) {
		  $aData = $this->_content->saveContent($form->getValues());
		  $this->view->content = '<p class="OK">Page has been edited <p>';
	  }
	 	    
	  $this->view->form = $form;
	  $this->view->layout()->title = 'Edit content';
	  $this->renderScript('pages/form.phtml');
	}
	public function getForm($sUrl)
	{

	  $form = new Zend_Form();
	  $form	->setAction($this->view->baseUrl($sUrl))
	  	  	->addElementPrefixPath('Custom_Validate', 'Custom/Validate/',Zend_Form_Element::VALIDATE)
                                ->setAttrib('autocomplete', 'off')
			->setMethod('post');
	  $content_left = $form->createElement('textarea', 'page_resources_left');
	  $content_left  ->setLabel('Content:')
                  ->setAttrib('autocomplete', 'off')
			  ->setRequired(true)
			  ->setDecorators(array(
			  'ViewHelper',
			  'Errors',
			  array('Label', array('placement' => 'prepend')),
			  array('HtmlTag', array('tag' => 'div', 'class' => 'FormRow'))
			  ))
			  ;
	  $content_right = $form->createElement('textarea', 'page_resources_right');
	  $content_right  ->setLabel('Content:')
                  ->setAttrib('autocomplete', 'off')
			  ->setRequired(true)
			  ->setDecorators(array(
			  'ViewHelper',
			  'Errors',
			  array('Label', array('placement' => 'prepend')),
			  array('HtmlTag', array('tag' => 'div', 'class' => 'FormRow'))
			  ))
			  ;
	  $content_id = $form ->createElement('hidden', 'page_ID')
						  ->removeDecorator('htmlTag')
						  ->removeDecorator('label');
						  $submit = $form->createElement('submit', 'submit',array('class' =>'submit'));
						  $submit->setLabel('submit')
						  ->setDecorators(array(
						  'ViewHelper',
						  array('HtmlTag', array('tag' => 'div', 'class' => 'FormRow'))
						  ))
						  ;
	
	  $form->addElements(array($content_left, $content_right, $content_id));

		$submit = $form->createElement('submit', 'submit',array('class' =>'submit'));
					  $submit->setLabel('submit')
					  ->setDecorators(array(
					  'ViewHelper',
					  array('HtmlTag', array('tag' => 'div', 'class' => 'FormRow'))
					  ))
					  ;
	  $form->addElement($submit);
	  return $form;
	}
}