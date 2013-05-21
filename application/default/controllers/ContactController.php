<?php

/**
 * Index controller for PROJECT_NAME
 *
 * Made by:     Thomas Bredenbeek
 * Modified by: -
 * Copyright:   MaxWebresults 2011
 *
 */
class ContactController extends Zend_Controller_Action {

    public function init() {
        $this->_objPages = new Application_Object_objPages();
    }

    function indexAction() {
        $this->view->pageContent = $this->_objPages->getPage();
        $this->view->pageContactForm = $this->view->action('index', 'index', 'contactForm');
        $this->view->pageCategoryOffers = $this->view->action('categoriesoffers', 'index', 'siteOffers');
        $this->view->pagePopulairOffers = $this->view->action('populairoffers', 'index', 'siteOffers');
    }

}