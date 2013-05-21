<?php

/**
 * Index controller for PROJECT_NAME
 *
 * Made by:     Thomas Bredenbeek
 * Modified by: -
 * Copyright:   MaxWebresults 2011
 *
 */
class FaqController extends Zend_Controller_Action {

    public function init() {
        $this->_dbFaq = new Application_Table_dbFaq();
        $this->_objPages = new Application_Object_objPages();
    }

    function indexAction() {
        $this->view->pageContent = $this->_objPages->getPage();
        $this->view->pageQuestionData = $this->_dbFaq->getAllCategoriesWithQuestions();
        $this->view->pageCategoryOffers = $this->view->action('categoriesoffers', 'index', 'siteOffers');
        $this->view->pagePopulairOffers = $this->view->action('populairoffers', 'index', 'siteOffers');
    }

    function disclaimerAction() {
        $this->view->pageContent = $this->_objPages->getPage();
        $this->view->pageCategoryOffers = $this->view->action('categoriesoffers', 'index', 'siteOffers');
        $this->view->pagePopulairOffers = $this->view->action('populairoffers', 'index', 'siteOffers');
    }

    function termsAction() {
        $this->view->pageContent = $this->_objPages->getPage();
        $this->view->pageCategoryOffers = $this->view->action('categoriesoffers', 'index', 'siteOffers');
        $this->view->pagePopulairOffers = $this->view->action('populairoffers', 'index', 'siteOffers');
    }

}