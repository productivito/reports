<?php

class Application_Model_Excel {

    private $published;
    protected $_db, $name, $data, $filename, $fields;

    public function __construct($file_name = false) {
        //ini_set('display_errors', 1);
        
        $this->name = $file_name;

        $this->filename = 'tmp/' . $file_name . ".xls";

        $this->objPHPExcel = new PHPExcel();

        $this->set_props();

        $this->data = Array();
        $this->fields = Array();

        $this->published = false;
    }

    private function set_props() {

        $this->objPHPExcel->getProperties()
                ->setCreator("")
                ->setLastModifiedBy("")
                ->setTitle($this->name)
                ->setSubject($this->name)
                ->setDescription($this->name);
    }

    public function is_published() {

        return $this->published;
    }

    public function getFileName() {

        return $this->filename;
    }

    public function setCreator($creator = null) {

        if (empty($creator))
            $creator = $this->name;
        $this->objPHPExcel->getProperties()
                ->setCreator($creator);

        return $creator;
    }

    public function setLastModifiedBy($lastModifiedName = null) {

        if (empty($lastModifiedName))
            $lastModifiedName = $this->name;
        $this->objPHPExcel->getProperties()
                ->setLastModifiedBy($lastModifiedName);

        return $lastModifiedName;
    }

    public function setTitle($title = null) {

        if (empty($title))
            $title = $this->name;
        $this->objPHPExcel->getProperties()
                ->setTitle($title);

        return $title;
    }

    public function setSubject($subject = null) {

        if (empty($subject))
            $subject = $this->name;
        $this->objPHPExcel->getProperties()
                ->setSubject($subject);

        return $subject;
    }

    public function setDescription($description = null) {

        if (empty($description))
            $description = $this->name;
        $this->objPHPExcel->getProperties()
                ->setDescription($description);

        return $description;
    }

    public function setBgColor($iColumn, $iRow, $sColor) {


        //$cell = $this->objPHPExcel->setActiveSheetIndex(0)->getActiveCell();

        $cell = PHPExcel_Cell::stringFromColumnIndex($iColumn) . $iRow;

        $this->objPHPExcel->setActiveSheetIndex(0)
                ->getStyle($cell)->getFill()
                ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF' . $sColor);
    }

    public function setTextColor($iColumn, $iRow, $sColor) {

        $objColor = new PHPExcel_Style_Color('FF' . $sColor);
        //$cell = $this->objPHPExcel->setActiveSheetIndex(0)->getActiveCell();

        $cell = PHPExcel_Cell::stringFromColumnIndex($iColumn) . $iRow;

        $this->objPHPExcel->setActiveSheetIndex(0)
                ->getStyle($cell)->getFont()
                ->setColor($objColor);
    }

    public function setBold($iColumn, $iRow, $sColor) {

        $objColor = new PHPExcel_Style_Color('FF' . $sColor);
        //$cell = $this->objPHPExcel->setActiveSheetIndex(0)->getActiveCell();

        $cell = PHPExcel_Cell::stringFromColumnIndex($iColumn) . $iRow;

        $this->objPHPExcel->setActiveSheetIndex(0)
                ->getStyle($cell)->getFont()
                ->setBold(true);
    }

    public function addHeader($aHeader) {

        $i = 0;
        foreach ($aHeader as $headerTitle) {
            $this->objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValueByColumnAndRow($i, 1, $headerTitle);

            $i++;
        }
    }

    public function setField($iColumn, $iRow, $value) {


        //$cell = $this->objPHPExcel->setActiveSheetIndex(0)->getActiveCell();

        $cell = PHPExcel_Cell::stringFromColumnIndex($iColumn) . $iRow;

        $this->fields[$cell] = $value;
    }

    public function addRow($aData) {

        $this->data[] = $aData;
    }

    public function addRows($aData) {
        foreach ($aData as $row) {
            $this->data[] = $row;
        }
    }

    public function publish($force = false) {

        if (!$this->is_published() || $force == true) {
            $this->writeToXls();

            $this->objWriter = new PHPExcel_Writer_Excel5($this->objPHPExcel);

            $this->objWriter->save($this->filename);

            $this->published = true;
        }

        return $this->filename;
    }

    public function send($email) {

        if (!$this->is_published())
            $this->publish();

        $file = $this->filename;

        $my_file = file_get_contents($file);
        $mail = new Zend_Mail();
        $mail->setBodyText('Exported file is in the attachement.');
        $mail->setFrom('lpportal@loyaltyprofs.nl', 'LoyaltyProfs lpportal');
        $mail->addTo($email, $email);
        $mail->setSubject('[export][lpportal] ' . $this->name);
        $at = $mail->createAttachment($my_file);

        $at->type = 'application/octet-stream';
        $at->filename = $this->name . '.xls';
        $mail->send();
    }

    public function download() {

        if (!$this->is_published())
            $this->publish();

        header("Content-Type: application/x-msexcel; name=\"" . $this->name . ".xls\"");
        header("Content-Disposition: inline; filename=\"" . $this->name . ".xls\"");
        $fh = fopen($this->filename, "r");
        fpassthru($fh);
        unlink($this->filename);
    }

    public function downloadNoSave() {

        $this->writeToXls();
        $this->objWriter = new PHPExcel_Writer_Excel5($this->objPHPExcel);
        ob_end_clean();
        $this->objWriter->save('php://output');
        
        $this->objPHPExcel->disconnectWorksheets();
        unset($this->objPHPExcel);
    }

    protected function writeToXls() {
        $i = 2;

        foreach ($this->data as $row) {
            $y = 0;
            foreach ($row as $field) {

                $this->objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValueByColumnAndRow($y, $i, $field);



                $y++;
            }
            $i++;
        }

        foreach ($this->fields as $key => $field) {


            $this->objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue($key, $field);
        }
    }

}
