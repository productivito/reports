<?php
class GoogleMap_Model_googleApi {

    public $_arrConfig =  array();
    
    public function __construct($arrTest){
        

        $arrConfig = Zend_Registry::get('config')->toArray();
        if(!empty($arrConfig['googlemap']) &&!empty($arrConfig['googlemap']['key']) && !empty($arrConfig['googlemap']['navigate']) && !empty($arrConfig['googlemap']['navigate']['street']) && !empty($arrConfig['googlemap']['navigate']['city'])){
            $this->_strGoogleKey = $arrConfig['googlemap']['key'];
            $this->_arrConfig = $arrConfig['googlemap'];
        } else {
            echo 'Please set cofig in your main config file. Example<br/><br/>googlemap.navigate.street = "Leidseplein 18"<br/>googlemap.navigate.city = "Amsterdam"';die();
        }

    }

    public function getLocation(){

        $strGoogleLink = str_replace(' ','+','http://maps.googleapis.com/maps/api/geocode/json?address='.$this->_arrConfig['navigate']['street'].','.$this->_arrConfig['navigate']['city'].'&sensor=true');
        $arrResult = json_decode(file_get_contents($strGoogleLink),true);
        if(!empty($arrResult['results'][0])){
            return $arrResult['results'][0];
        }

    }
    public function getRoute(){

        $strGoogleLink = str_replace(' ','','http://maps.googleapis.com/maps/api/directions/json?origin=hermitage68,zaandam&destination='.$this->_arrConfig['navigate']['street'].','.$this->_arrConfig['navigate']['city'].'&region=nl&sensor=false');
        $arrResult = json_decode(file_get_contents($strGoogleLink),true);

        if(!empty($arrResult['results'][0])){
            return $arrResult['results'][0];
        }

    }
    public function getApiKey(){
        //echo '<pre>'.print_r($this->_arrConfig,true);die();
        return $this->_strGoogleKey;
    }
}

?>
