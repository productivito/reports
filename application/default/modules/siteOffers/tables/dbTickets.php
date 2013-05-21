<?php

class SiteOffers_Table_dbTickets extends Zend_Db_Table_Abstract {

    protected $_name = 'shop_tickets';
    protected $_primary = array('ticket_ID');
    protected $_intLanguageID = 1;

    public function __construct() {
        parent::__construct();
    }

    public function insertTicket($intProductID, $arrTicket) {
        
        $arrInsert = array('product_ID' => $intProductID,
            'ticket_code' => $arrTicket['product_code'],
            'ticket_name' => $arrTicket['name'],
            'ticket_description' => $arrTicket['description'],
            'ticket_price' => $arrTicket['price'],
            'ticket_price_old' => $arrTicket['normalprice'],
            'ticket_valid_from' => $arrTicket['valid_from'],
            'ticket_valid_to' => $arrTicket['valid_to'],
            'ticket_position' => $arrTicket['pos'],
        );
        return $this->insert('shop_tickets', $arrInsert);

    }

    public function checkIfTicketExists($strProductCode, $intProductID) {
        if (!empty($strProductCode)) {
            $sql = $this->select()
                            ->from(array('shop_tickets'))
                            ->where('ticket_code = ?', $strProductCode)
                            ->where('product_ID = ?', $intProductID);

            $arrArray = $this->fetchRow($sql)->toArray();
            if (!empty($arrArray)) {
                return $arrArray;
            }
        }
        return false;
    }
    public function getTicketsByProduct($intProductID = false) {

         $arrReturn = array();
        if (!empty($intProductID)) {
            $sql = $this->select()
                            ->from('shop_tickets')
                            ->where('product_ID = ?', $intProductID)
                            ->where('ticket_active = ?', 'active');
            $arrReturn = $this->fetchAll($sql)->toArray();
        }

        return $arrReturn;

    }
        public function getPopulairOffers(){

            $sql = $this->select()
                        ->setIntegrityCheck(false)
                        ->from('shop_tickets')
                        ->joinLeft(array('shop_products'), 'shop_products.product_ID=shop_tickets.product_ID')
                        ->where('shop_tickets.ticket_active = ?', 'active');

            $arrResult = $this->fetchAll($sql)->toArray();
            
            foreach($arrResult as $intKey => $arrTicket){
                //$arrResult[$intKey]['ticket_discount'] = round((($arrTicket['ticket_price_old'] - $arrTicket['ticket_price']) / ($arrTicket['ticket_price_old'] / 100)));
                $arrResult[$intKey]['ticket_discount'] = ($arrTicket['ticket_price_old'] - $arrTicket['ticket_price']);
            }
            return $arrResult;


    }

}

?>