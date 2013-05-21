<?php

class SiteOffers_Model_mdlProducts {

    protected $_objDb, $_currentLanguageID;

    public function __construct() {
        //$this->_objDb = Zend_Registry::get('db');
        $this->_dbProducts = new SiteOffers_Table_dbProducts();
        $this->_dbTickets = new SiteOffers_Table_dbTickets();
        $this->_dbCategories = new SiteOffers_Table_dbCategories();

        $this->_intLanguageID = 1; /* Not in use */
        $this->_objGD = new SiteOffers_Object_objGD();
    }


    public function processProductsFromXml($arrData = false) {
        $arrReturn = array();
        if (!empty($arrData)) {
            $arrOfPath = array();
            $arrTmp = array();
            $arrReturn = array();
            $arrProducts = $this->sortAllProducts($arrData);

            foreach ($arrProducts as $strCatName => $arrCategory) {
                foreach ($arrCategory as $arrCategoryInfo) {
                    if ($this->_dbCategories->checkIfCategoryExists($arrCategoryInfo['category']['category']) === false) {
                        $this->_dbCategories->insertCategory(array('category_id' => $arrCategoryInfo['category'], 'category_name' => $strCatName));
                    }
                    $intProductID = $this->processProductArray($arrCategoryInfo);
                    $intTicketID = $this->processTicketArray($intProductID, $arrCategoryInfo['products']);
                }
            }
        }
    }

    private function processTicketArray($intProductID, $arrTickets) {

        if (!empty($intProductID) && !empty($arrTickets)) {
            if ($arrTickets['total'] == 1) {
                $this->ticketCompareDb($intProductID,$arrTickets['product']);
            } elseif ($arrTickets['total'] > 1) {
                foreach ($arrTickets['product'] as $arrTicket) {
                    $this->ticketCompareDb($intProductID,$arrTicket);
                }
            }
        }
    }

    private function processProductArray($arrProduct) {
        $intReturn = false;
        if (!empty($arrProduct)) {
            $intProductId = false;
            $strProductVal = false;
            $arrText = false;
            $arrImages = false;
            $blExistsInDb = false;
            if (!empty($arrProduct) && !empty($arrProduct['category'])) {

                foreach ($arrProduct as $strCatKey => $strCatVal) {
                    if (stristr($strCatKey, '_code') && $strCatKey != 'postal_code') {

                        $intProductId = $strCatVal;
                        $strProductVal = $strCatKey;
                        $blExistsInDb = $this->_dbProducts->checkIfProductExists($intProductId, $arrProduct['category']);
                    }

                    if (substr($strCatKey, 0, 4) == 'more') {
                        if (substr($strCatKey, -4) == 'text') {
                            $arrText[$strCatKey[4]] = $strCatVal;
                        }
                        if (substr($strCatKey, -3) == 'img') {
                            $arrImg[$strCatKey[4]] = $strCatVal;
                        }
                    }
                }

                if (!empty($intProductId) && $blExistsInDb === false) {
                    $strProductLogoThumb = $arrProduct['logo_s'];
                    $blImageResized = $this->_objGD->createThumbnail($strProductLogoThumb,$strProductVal.'_'.$intProductId.'.jpg');
                    if($blImageResized === true){
                        $strProductLogoThumb = $strProductVal.'_'.$intProductId.'.jpg';
                    }
                    $arrReturn = array('product_ID_xml' => $intProductId,
                        'product_ID_xml_key' => $strProductVal,
                        'product_name' => @$arrProduct['name'],
                        'product_location' => @$arrProduct['location'],
                        'product_address' => @$arrProduct['address'],
                        'product_postal_code' => @$arrProduct['postal_code'],
                        'product_city' => @$arrProduct['city'],
                        'product_www' => @$arrProduct['www'],
                        'product_tel' => @$arrProduct['tel'],
                        'product_open_from' => @$arrProduct['open_from'],
                        'product_open_to' => @$arrProduct['open_to'],
                        'product_logo' => @$arrProduct['logo_s'],
                        'product_logo_thumb' => $strProductLogoThumb,
                        'product_intro' => @$arrProduct['intro_text'],
                        'product_thumb' => @$arrProduct['intro_img'],
                        'product_text' => $arrText,
                        'product_images' => $arrImg,
                        'product_active' => 'active'
                    );

                    $intReturn = $this->_dbProducts->insertProduct($arrReturn, $arrProduct['category']);
                } else {
                    $intReturn = $blExistsInDb;
                }
            }
        }
        return $intReturn;
    }

    private function sortAllProducts($arrData) {
        $arrReturn = array();
        if (!empty($arrData)) {
            foreach ($arrData as $strCatName => $arrCategories) {
                if (!empty($arrCategories)) {
                    foreach ($arrCategories as $arrCategoryInfo) {
                        if (is_array($arrCategoryInfo)) {
                            $arrReturn[$strCatName] = $arrCategoryInfo;
                        }
                    }
                }
            }
        }
        return $arrReturn;
    }

    public function ticketCompareDb($intProductID,$arrTicket) {
        $arrTicketDB = $this->_dbTickets->checkIfTicketExists($arrTicket['product_code'], $intProductID);
        if (empty($arrTicketDB)) {
           $this->_dbTickets->insertTicket($intProductID,$arrTicket);
        } else {
            //check for update
        }
    }

   /* public function insertTicket($intProductID,$arrTicket) {

        $arrInsert = array( 'product_ID'            => $intProductID,
                            'ticket_code'           => $arrTicket['product_code'],
                            'ticket_name'           => $arrTicket['name'],
                            'ticket_description'    => $arrTicket['description'],
                            'ticket_price'          => $arrTicket['price'],
                            'ticket_price_old'      => $arrTicket['normalprice'],
                            'ticket_valid_from'     => $arrTicket['valid_from'],
                            'ticket_valid_to'       => $arrTicket['valid_to'],
                            'ticket_position'       => $arrTicket['pos'],
                           );
        return $this->_objDb->insert('shop_tickets', $arrInsert);
        
    }




    public function checkIfTicketExists($strProductCode, $intProductID) {
        if (!empty($strProductCode)) {

            $sql = $this->_objDb->select()
                            ->from(array('shop_tickets'))
                            ->where('ticket_code = ?', $strProductCode)
                            ->where('product_ID = ?', $intProductID);
            
            $arrArray = $this->_objDb->fetchRow($sql);
            if (!empty($arrArray)) {
                return $arrArray;
            }
        }
        return false;
    }

    public function checkIfProductExists($product_id, $category_id) {
        if (!empty($product_id) && !empty($category_id)) {

            $sql = $this->_objDb->select()
                            ->from(array('shop_products'))
                            ->where('product_ID_xml = ?', $product_id)
                            ->where('category_ID = ?', $category_id);
            $arrArray = $this->_objDb->fetchRow($sql);
            if (!empty($arrArray)) {
                return $arrArray['product_ID'];
            }
        }
        return false;
    }

    public function insertProduct($arrData, $category_id) {
        if (!empty($arrData) && !empty($category_id)) {
            $arrData['category_ID'] = $category_id;
            if (!empty($arrData['product_text'])) {
                $arrData['product_text'] = json_encode($arrData['product_text']);
            }
            if (!empty($arrData['product_images'])) {
                $arrData['product_images'] = json_encode($arrData['product_images']);
            }
            $intResult = $this->_objDb->insert('shop_products', $arrData);
            if (!empty($intResult)) {
                return $this->_objDb->lastInsertId();
            }
        }
        return false;
    }

    public function checkIfCategoryExists($category_id) {
        if (!empty($category_id)) {

            $sql = $this->_objDb->select()
                            ->from(array('shop_categories'))
                            ->where('category_ID = ?', $category_id);
            $arrArray = $this->_objDb->fetchRow($sql);
            if (!empty($arrArray)) {
                return true;
            }
        }
        return false;
    }

    public function insertCategory($arrData) {
        if (!empty($arrData)) {

            $sql = $this->_objDb->insert('shop_categories', $arrData);
            if (!empty($arrArray)) {
                return true;
            }
        }
        return false;
    }*/

}
?>