<?php

class SiteOffers_Object_objSoap {

    protected $_objDb, $_arrSoapUrls, $_intSoapTimeout;

    public function __construct() {
        $arrConfig = Zend_Registry::get('config')->toArray();
        if (!empty($arrConfig['soapclient']) && !empty($arrConfig['soapclient']['timeout']) && !empty($arrConfig['soapclient']['url'])) {
            $this->_arrSoapUrls = $arrConfig['soapclient']['url'];
            $this->_intSoapTimeout = $arrConfig['soapclient']['timeout'];
        } else {
            echo 'Please set cofig in your main config file. Example<br/><br/>soapclient.timeout = 3<br />soapclient.url.products = "http://xml.to/link"<br/soapclient.url.example = "http://xml.to/example"';
            die();
        }
        $this->_objDb = Zend_Registry::get('db');
    }

    public function getXmlInArray($strUrlName) {
        $arrReturn = false;
        if (!empty($this->_arrSoapUrls[$strUrlName])) {
            $strXml = $this->openUrlLocation($this->_arrSoapUrls[$strUrlName]);
            if (!empty($strXml)) {
                $arrReturn = $this->xmlToArray($strXml);
            }
        }
        return $arrReturn;
    }

    private function openUrlLocation($strUrl) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $strUrl);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        ob_start();
        curl_exec($ch);
        curl_close($ch);
        $strXml = ob_get_contents();
        ob_end_clean();
        return $strXml;
    }

    private function xmlToArray($strXml) {
        return json_decode(Zend_Json::fromXml($strXml, true), true);
    }

    public function soapCall($arrValues) {
        $arrResult = array();
        $arrResult['result'] = false;
        $arrResult['msg'] = false;
        if (!empty($arrValues)) {
            try {
                $this->soap_client = new SoapClient("http://tc-h02.tc.nl/mptickets/services/tcshop1.svc/?wsdl");
            } catch (Exception $e) {
                throw new Zend_Exception($e->getMessage(), 500);
            }
            if(!empty($arrValues) && isset($arrValues['ClientID']) && isset($arrValues['BuyerAddress']) && isset($arrValues['Products']) && isset($arrValues['ShipMethod'])){
                $objResult = $this->soap_client->Checkout($arrValues['ClientID'],$arrValues['BuyerAddress'],$arrValues['Products'],$arrValues['ShipMethod']);
            }

            if(!empty($objResult) && isset($objResult->Result)){
                $arrResult['result'] = $objResult->Result;
                if($arrResult['result'] == 1){
                    echo 'koek ->'.$objResult->CheckoutID;
                    $arrResult['msg'] = $objResult->CheckoutID;
                } else {
                    $arrResult['msg'] = $objResult->Message;
                }
            }
            return $arrResult;
        }
    }

}

?>