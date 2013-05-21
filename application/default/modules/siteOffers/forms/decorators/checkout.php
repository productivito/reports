<?php
class Custom_Decorator_Checkout extends Zend_Form_Decorator_Abstract
    {
        public function buildLabel()
        {
            $element = $this->getElement();
            $label = $element->getLabel();
            
            if ($element->isRequired()) {
                $label .= '*';
            }
            $label .= ':';
            return $element->getView()
                           ->formLabel($element->getName(), $label);
        }

        public function buildInput()
        {
            $element = $this->getElement();
            $helper  = $element->helper;
            return $element->getView()->$helper(
                $element->getName(),
                $element->getValue(),
                $element->getAttribs(),
                $element->options
            );
        }

        public function buildErrors()
        {
            $element  = $this->getElement();
            $messages = $element->getMessages();
            if (empty($messages)) {
                return '';
            }
            return '<div class="errors">' .
                   $element->getView()->formErrors($messages) . '</div>';
        }

        public function buildDescription()
        {
            $element = $this->getElement();
            $desc    = $element->getDescription();
            if (empty($desc)) {
                return '';
            }
            return '<div class="description">' . $desc . '</div>';
        }

        public function render($content)
        {
            $element = $this->getElement();
            $attribs = $element->getAttribs();
            for($i =0;$i <= 5;$i++){
                if(empty($attribs['labelFields'][$i])){
                    $attribs['labelFields'][$i] = '';
                }
            }

            $view = new Zend_View();
            $view->setScriptPath(APPLICATION_PATH.'/modules/SiteOffers/forms/decorators/views/');
            $view->arrLabels = $attribs['labelFields'];
            $view->inputField = $this->buildInput();
            return $view->render('checkout.phtml');

           
        }
    }


    /* COMMENTED REFERENCE : */

    
                /*if (!$element instanceof Zend_Form_Element) {
                return $content;
            }
            if (null === $element->getView()) {
                return $content;
            }

            $separator = $this->getSeparator();
            $placement = $this->getPlacement();
            $label     = $this->buildLabel();
            $input     = $this->buildInput();
            $errors    = $this->buildErrors();
            $desc      = $this->buildDescription();

            $output = '<div class="form element">'
                    . $label
                    . $input
                    . $errors
                    . $desc
                    . '</div>';

            switch ($placement) {
                case (self::PREPEND):
                    return $output . $separator . $content;
                case (self::APPEND):
                default:
                    return $content . $separator . $output;
            }*/
?>