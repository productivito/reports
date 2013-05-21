<?php

class Siteoffers_Object_objGD {

    protected $_db;

    public function __construct() {
        $this->_db = Zend_Registry::get('db');
    }

    public function createThumbnail($strSourcePath,$strDestFileName,$intWidth = 70,$intHeight = 65) {

        list( $intSourceWidth, $intSourceHeight, $strSourceType ) = getimagesize($strSourcePath);

        switch ($strSourceType) {
            case IMAGETYPE_GIF:
                $strSourceGdImage = imagecreatefromgif($strSourcePath);
                break;

            case IMAGETYPE_JPEG:
                $strSourceGdImage = imagecreatefromjpeg($strSourcePath);
                break;

            case IMAGETYPE_PNG:
                $strSourceGdImage = imagecreatefrompng($strSourcePath);
                break;
        }

        if ($strSourceGdImage === false) {
            return false;
        }

        $intSourceAspectRatio = $intSourceWidth / $intSourceHeight;
        $intThumbAspectRatio = $intWidth / $intHeight;

        if ($intSourceWidth <= $intWidth && $intSourceHeight <= $intHeight) {
            $intWidth = $intSourceWidth;
            $intHeight = $intSourceHeight;
        } elseif ($intThumbAspectRatio > $intSourceAspectRatio) {
            $intWidth = (int) ( $intHeight * $intSourceAspectRatio );
        } else {
            $intHeight = (int) ( $intWidth / $intSourceAspectRatio );
        }
        $strThumbnailGdImage = imagecreatetruecolor($intWidth, $intHeight);
        imagecopyresampled($strThumbnailGdImage, $strSourceGdImage, 0, 0, 0, 0, $intWidth, $intHeight, $intSourceWidth, $intSourceHeight);
        imagejpeg($strThumbnailGdImage, APPLICATION_PATH.'/../../public/img/shop/thumbs/'.$strDestFileName, 90);
        //imagedestroy($strSourceGdImage);
        //imagedestroy($strThumbnailGdImage);

        return true;
    }

}
?>