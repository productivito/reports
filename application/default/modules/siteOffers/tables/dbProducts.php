<?php

class SiteOffers_Table_dbProducts extends Zend_Db_Table_Abstract {

    protected $_name = 'shop_products';
    protected $_primary = array('product_ID');
    protected $_intLanguageID = 1;

    public function __construct() {
        parent::__construct();
    }

         public function getProductByID($intProductID = false) {

        $arrReturn = array();
        if (!empty($intProductID)) {
            $sql = $this->select()
                            ->from('shop_products')
                            ->where('product_ID = ?', $intProductID)
                            ->where('product_active = ?', 'active');
            $arrReturn = $this->fetchRow($sql)->toArray();
        }
        return $arrReturn;
    }



    public function checkIfProductExists($product_id, $category_id) {
        if (!empty($product_id) && !empty($category_id)) {

            $sql = $this->select()
                            ->from(array('shop_products'))
                            ->where('product_ID_xml = ?', $product_id)
                            ->where('category_ID = ?', $category_id);
            $arrArray = $this->fetchRow($sql)->toArray();
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
            $intResult = $this->insert('shop_products', $arrData);
            if (!empty($intResult)) {
                return $this->lastInsertId();
            }
        }
        return false;
    }
    public function getProductsByCategory($intCategoryID = false) {

        $arrReturn = array();
        if (!empty($intCategoryID)) {
            $sql = $this->select()
                            ->from('shop_products')
                            ->where('category_ID = ?', $intCategoryID)
                            ->where('product_active = ?', 'active');
            $arrReturn = $this->fetchAll($sql)->toArray();
        }
        return $arrReturn;
    }
}

?>