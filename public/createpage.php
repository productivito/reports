<?php

$text = "[id[Label]67]sdfkfdkfd[href[Label]http://www.gaga.nl]ksk sdjsjds sdksklfd [route[Label]index/contact]";
echo $text.'<br/><br /><br />';


$arrResults = array();
preg_match_all("#\[((?>[^\[\]]+)|(?R))*\]#x", $text, $arrResults);
$arrButtons = array();
foreach ($results[0] as $key => $value) {
    $arrTmp = explode('|',str_replace(']','|',str_replace('[','|',substr($value, 1, -1))).'<br />');
    if(count($arrTmp) == 3){
        $arrButtons[] = $arrTmp;
    }
}

echo '-><pre>' . print_r($arrButtons, true);


die();
$arrPage = array('left' => array(
        'type' => 'widget',
        'blocks' => Array
            (
            0 => 51
        ),
        'class' => '',
        'scroll' => 'auto'
    ),
    'right' => array(
        'type' => 'widget',
        'blocks' => array
            (
            0 => 53
        ),
        'class' => '',
        'scroll' => 'auto'
        ));





echo '<pre>' . print_r($arrPage, true);
echo json_encode($arrPage);
?>