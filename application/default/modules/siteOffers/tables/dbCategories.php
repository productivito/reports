<?php

class SiteOffers_Table_dbCategories extends Zend_Db_Table_Abstract {

    protected $_name = 'shop_categories';
    protected $_primary = array('category_ID');
    protected $_intLanguageID = 1;

    public function __construct() {
        parent::__construct();
    }

    public function getAllCategories() {
        $sql = $this->select()
                        ->from('shop_categories')
                        ->where('category_active = ?', 'active');
        return $this->fetchAll($sql)->toArray();
    }

    public function checkIfCategoryExists($category_id) {
        if (!empty($category_id)) {

            $sql = $this->select()
                            ->from('shop_categories')
                            ->where('category_ID = ?', $category_id);
            $arrArray = $this->fetchRow($sql);
            if (!empty($arrArray)) {
                return true;
            }
        }
        return false;
    }

    public function insertCategory($arrData) {
        if (!empty($arrData)) {

            $sql = $this->insert('shop_categories', $arrData);
            if (!empty($arrArray)) {
                return true;
            }
        }
        return false;
    }
}

?>