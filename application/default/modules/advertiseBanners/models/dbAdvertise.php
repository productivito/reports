<?php

class AdvertiseBanners_Model_dbAdvertise {

    protected $_objDb;

    public function __construct() {
        $this->_objDb = Zend_Registry::get('db');
    }

    public function getRandomAdvertisement($intWidth = false, $intHeight = false) {
        if (!empty($intWidth) && !empty($intHeight)) {
            $sql = $this->_objDb->select('*')
                            ->from('advertisements')
                            ->where('advertisement_width = "'.$intWidth.'"')
                            ->where('advertisement_height = "'.$intHieght.'"')
                            ->order('RAND()');

            return $this->_objDb->fetchRow($sql);
        } else {
            return false;
        }
    }

}
?>
