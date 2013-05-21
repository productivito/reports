<?php

class ExportController extends Zend_Controller_Action
{
    const XSMALL = 0.3;
    const SMALL = 2;
    const MEDIUM = 3;
    const LARGE = 5;
    const XLARGE = 8;
    const HUGE = 11;
    
    public function init() 
    {
    	ini_set(" set_time_limit","0");
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNeverRender();
        
    }

    public function indexAction()
    {
        throw new Exception('Not a valid EXPORT request');
    }

    public function csvAction()
    {
        $action = $this->_request->getParam('dataset');
        $component = $this->_request->getParam('component');
        $filters = json_decode($this->_request->getParam('filters'), true);
		
        $headers = $this->_getHeaders($action);
        $data = $this->_getExportData($action, $filters,1,200,$component);
        
        $temp_data = array();
        if($action == 'activity')
        {
			$temp_data = $this->getPauseList($data);
			$data = $temp_data;
        }        
        if($action == 'computer')
        {
			$temp_data = $this->getStopList($data,$filters);
			$data = $temp_data;
        }   
                
        $this->_exportCsvDocument($action, $filters, $headers, $data, $component);
    }
    
    public function pdfAction()
    {
        $action = $this->_request->getParam('dataset');

        $component = $this->_request->getParam('component');
        $filters = json_decode($this->_request->getParam('filters'), true);
	
        $headers = $this->_getHeaders($action);
	
        $data = $this->_getExportData($action, $filters,1,200,$component);
        
        $temp_data = array();
        if($action == 'activity')
        {
			$temp_data = $this->getPauseList($data);
			$data = $temp_data;
        }

        if($action == 'computer')
        {
			$temp_data = $this->getStopList($data,$filters);
			$data = $temp_data;
        }        
        
		//
		//echo "<pre>";print_r($data);echo "<pre>";die();
		/*print_r($action);echo "</br>";
		print_r($component);echo "</br>";
		print_r($filters);echo "</br>";die();
		*/
        $this->_exportPdfDocument($action, $filters, $headers, $data, $component);
    }  
    
    protected function _getHeaders($action)
    {
        $header = array();
        switch($action) {
            case 'documents':
                $header = array(
                    'usage' => array(
                        array('title' => 'Icon',              'type' => 'image', 'widthpercent' => self::SMALL),
                        array('title' => 'Denumire document', 'type' => 'text',  'widthpercent' => self::HUGE),
                        array('title' => 'Durata',            'type' => 'text',  'widthpercent' => self::MEDIUM),
                        array('title' => 'Utilizare %',       'type' => 'text',  'widthpercent' => self::MEDIUM),
                    ),
                    'crons' => array(
                        array('title' => 'Icon',              'type' => 'image', 'widthpercent' => self::SMALL),
                        array('title' => 'Denumire document', 'type' => 'text',  'widthpercent' => self::HUGE),
                        array('title' => 'Calculator',        'type' => 'text',  'widthpercent' => self::LARGE),
                        array('title' => 'User',              'type' => 'text',  'widthpercent' => self::LARGE),
                        array('title' => 'Data / Ora start',  'type' => 'text',  'widthpercent' => self::LARGE),
                        array('title' => 'Ora stop',          'type' => 'text',  'widthpercent' => self::MEDIUM),
                        array('title' => 'Durata',            'type' => 'text',  'widthpercent' => self::LARGE),
                    )
                );
                break;
            case 'chat':
                $header = array(
                    'usage' => array(
                        array('title' => 'Icon',              'type' => 'image', 'widthpercent' => self::SMALL),
                        array('title' => 'User ID',           'type' => 'text',  'widthpercent' => self::XLARGE),
                        array('title' => 'Tip ID',          'type' => 'text',  'widthpercent' => self::XLARGE),
                        array('title' => 'Utilizare %',       'type' => 'text',  'widthpercent' => self::MEDIUM),
                        array('title' => 'Durata',            'type' => 'text',  'widthpercent' => self::MEDIUM),
                    ),
                    'crons' => array(
                        array('title' => 'Icon',              'type' => 'image', 'widthpercent' => self::SMALL),
                        array('title' => 'Calculator',        'type' => 'text',  'widthpercent' => self::LARGE),
                        array('title' => 'User',              'type' => 'text',  'widthpercent' => self::LARGE),
                        array('title' => 'User ID',           'type' => 'text',  'widthpercent' => self::LARGE),
                        array('title' => 'Data / Ora start',  'type' => 'text',  'widthpercent' => self::XLARGE),
                        array('title' => 'Ora stop',          'type' => 'text',  'widthpercent' => self::MEDIUM),
                        array('title' => 'Durata',            'type' => 'text',  'widthpercent' => self::MEDIUM),
                    )
                );
                break;
            case 'computer':
                $header = array(
                    'usage' => array(
                		array('title' => 'Utilizator',                  'type' => 'text', 'widthpercent' => self::LARGE),
						array('title' => 'Data',                        'type' => 'text', 'widthpercent' => self::LARGE),
                        array('title' => 'Prima deschidere PC',         'type' => 'text', 'widthpercent' => self::MEDIUM),
                        array('title' => 'Ultima inchidere PC',         'type' => 'text', 'widthpercent' => self::MEDIUM),						                		
                        array('title' => 'Timp Activ',                  'type' => 'text', 'widthpercent' => self::MEDIUM),
                        array('title' => 'Timp Pauza',                  'type' => 'text', 'widthpercent' => self::MEDIUM),
                        array('title' => 'Timp Oprit',                  'type' => 'text', 'widthpercent' => self::MEDIUM),
                        array('title' => 'Lista Opriri',                'type' => 'text', 'widthpercent' => self::XLARGE - 1),
                    ),
                );
                break;
            case 'internet':
                $header = array(
                    'usage' => array(
                        array('title' => 'Icon',              'type' => 'image', 'widthpercent' => self::SMALL),
                        array('title' => 'Site',              'type' => 'text',  'widthpercent' => self::XLARGE),
                        array('title' => 'Tip Site',          'type' => 'text',  'widthpercent' => self::XLARGE),
                        array('title' => 'Durata',            'type' => 'text',  'widthpercent' => self::MEDIUM),
                        array('title' => 'Utilizare %',       'type' => 'text',  'widthpercent' => self::MEDIUM),
                    ),
                    'crons' => array(
                    	array('title' => 'Icon',              'type' => 'image', 'widthpercent' => self::SMALL),
                    	array('title' => 'Data / Ora start',  'type' => 'text',  'widthpercent' => self::XLARGE),
                    	array('title' => 'Ora stop',          'type' => 'text',  'widthpercent' => self::MEDIUM),
                        array('title' => 'Durata',            'type' => 'text',  'widthpercent' => self::MEDIUM),
                        array('title' => 'Calculator',        'type' => 'text',  'widthpercent' => self::MEDIUM),
                        array('title' => 'User',              'type' => 'text',  'widthpercent' => self::LARGE),
                        array('title' => 'Denumire Program',  'type' => 'text',  'widthpercent' => self::MEDIUM),
                        array('title' => 'Titlul ferestrei',  'type' => 'text',  'widthpercent' => self::HUGE),
                        array('title' => 'Link complet',      'type' => 'text',  'widthpercent' => 5),
                    )
                );
                break;
            case 'applications':
                $header = array(
                    'usage' => array(
                        array('title' => 'Icon',              'type' => 'image', 'widthpercent' => self::SMALL),
                        array('title' => 'Aplicatie',         'type' => 'text',  'widthpercent' => self::XLARGE),
                        array('title' => 'Tip Aplicatie',     'type' => 'text',  'widthpercent' => self::XLARGE),
                        array('title' => 'Utilizare %',       'type' => 'text',  'widthpercent' => self::MEDIUM),
                        array('title' => 'Durata',            'type' => 'text',  'widthpercent' => self::MEDIUM),
                    ),
                    'crons' => array(
                        array('title' => 'Icon',              'type' => 'image', 'widthpercent' => self::SMALL),
                        array('title' => 'Denumire Program',  'type' => 'text',  'widthpercent' => self::MEDIUM),
                        array('title' => 'Titlul ferestrei',  'type' => 'text',  'widthpercent' => self::HUGE),
                        array('title' => 'Calculator',        'type' => 'text',  'widthpercent' => self::MEDIUM),
                        array('title' => 'User',              'type' => 'text',  'widthpercent' => self::LARGE),
                        array('title' => 'Data/Ora Start',    'type' => 'text',  'widthpercent' => self::LARGE),
                        array('title' => 'Ora stop',          'type' => 'text',  'widthpercent' => self::MEDIUM),
                        array('title' => 'Durata',            'type' => 'text',  'widthpercent' => self::MEDIUM),
                    )
                );
                break;
            case 'files':
                $header = array(
                    'usage' => array(
                        array('title' => 'Calculator',   'type' => 'text', 'widthpercent' => self::MEDIUM),
                        array('title' => 'User',         'type' => 'text', 'widthpercent' => self::MEDIUM),
                        array('title' => 'Nume fisier',  'type' => 'text', 'widthpercent' => self::MEDIUM),
                        array('title' => 'Operatiune',   'type' => 'text', 'widthpercent' => self::MEDIUM),
                        array('title' => 'Data/Ora',     'type' => 'text', 'widthpercent' => self::MEDIUM),
                        array('title' => 'Cale citire',  'type' => 'text', 'widthpercent' => self::HUGE),
                        array('title' => 'Cale scriere', 'type' => 'text', 'widthpercent' => self::HUGE),
                    ),
                );
                break;
            case 'top':
                $header = array(
                    'usage' => array(
                        array('title' => 'Utilizator',             'type' => 'text', 'widthpercent' => self::LARGE),
                        array('title' => 'Nume',                   'type' => 'text', 'widthpercent' => self::MEDIUM),
                        array('title' => 'Timp total (h)',         'type' => 'text', 'widthpercent' => self::MEDIUM),
                        array('title' => 'Total pauze legale (h)', 'type' => 'text', 'widthpercent' => self::MEDIUM),
                        array('title' => 'Ore productive',         'type' => 'text', 'widthpercent' => self::MEDIUM),
                        array('title' => 'Ore neproductive',       'type' => 'text', 'widthpercent' => self::MEDIUM),
                        array('title' => '% Productivitate',       'type' => 'text', 'widthpercent' => self::MEDIUM),
                        array('title' => '% Neproductivitate',     'type' => 'text', 'widthpercent' => self::MEDIUM),
                    ),
                );
                break;
            case 'activity':
                $header = array(
                    'usage' => array(
                        array('title' => 'Utilizator',          'type' => 'text', 'widthpercent' => self::LARGE),
                        array('title' => 'Data',                'type' => 'text', 'widthpercent' => self::LARGE),
                        array('title' => 'Prima deschidere PC', 'type' => 'text', 'widthpercent' => self::MEDIUM),
                        array('title' => 'Ultima inchidere PC', 'type' => 'text', 'widthpercent' => self::MEDIUM),
                        array('title' => 'Timp pornit',          'type' => 'text', 'widthpercent' => self::MEDIUM),
                        array('title' => 'Timp activ',          'type' => 'text', 'widthpercent' => self::MEDIUM),
                        array('title' => 'Timp inactiv',        'type' => 'text', 'widthpercent' => self::MEDIUM),
                        array('title' => 'Procent TA/TT',       'type' => 'text', 'widthpercent' => self::MEDIUM),
                        array('title' => 'Lista pauze',         'type' => 'text', 'widthpercent' => self::XLARGE - 1),
                    ),
                );
                break;
            case 'roi':
                $header = array(
                    'usage' => array(
                        array('title' => 'Departament',            'type' => 'text', 'widthpercent' => self::HUGE),
                        array('title' => 'WinUser',                'type' => 'text', 'widthpercent' => self::LARGE),
                        array('title' => 'Nume',                   'type' => 'text', 'widthpercent' => self::LARGE),
                        array('title' => 'Cost/Ora',               'type' => 'text', 'widthpercent' => self::MEDIUM),
                        array('title' => 'Valuta',                 'type' => 'text', 'widthpercent' => self::MEDIUM),
                        array('title' => 'Ore cf. CM',             'type' => 'text', 'widthpercent' => self::MEDIUM),
                        array('title' => 'Ore productive',         'type' => 'text', 'widthpercent' => self::MEDIUM),
                        array('title' => 'Cost ore lucrate',       'type' => 'text', 'widthpercent' => self::MEDIUM),
                        array('title' => 'Valoare ore productive', 'type' => 'text', 'widthpercent' => self::MEDIUM),
                    ),
                );
                break;
            case 'performance':
                $header = array(
                    'usage' => array(
							array('title' => 'Raport de Performanta Individuala privind lucrul pe calculator',            'type' => 'text', 'widthpercent' => self::HUGE),
                       		array('title' => '',                													      'type' => 'text', 'widthpercent' => self::LARGE),	
                		),
                );	
                break;
            case 'timekeeping':
                $header = array(
                    'usage' => array(
                        array('title' => 'Departament',    'type' => 'text', 'widthpercent' => self::HUGE),
                        array('title' => 'WinUser',        'type' => 'text', 'widthpercent' => self::LARGE),
                        array('title' => 'Nume',           'type' => 'text', 'widthpercent' => self::LARGE),
                        array('title' => 'CNP',            'type' => 'text', 'widthpercent' => self::MEDIUM),
                        array('title' => '1',              'type' => 'text', 'widthpercent' => self::XSMALL),
                        array('title' => '2',              'type' => 'text', 'widthpercent' => self::XSMALL),
                        array('title' => '3',              'type' => 'text', 'widthpercent' => self::XSMALL),
                        array('title' => '4',              'type' => 'text', 'widthpercent' => self::XSMALL),
                        array('title' => '5',              'type' => 'text', 'widthpercent' => self::XSMALL),
                        array('title' => '6',              'type' => 'text', 'widthpercent' => self::XSMALL),
                        array('title' => '7',              'type' => 'text', 'widthpercent' => self::XSMALL),
                        array('title' => '8',              'type' => 'text', 'widthpercent' => self::XSMALL),
                        array('title' => '9',              'type' => 'text', 'widthpercent' => self::XSMALL),
                        array('title' => '10',             'type' => 'text', 'widthpercent' => self::XSMALL),
                        array('title' => '11',             'type' => 'text', 'widthpercent' => self::XSMALL),
                        array('title' => '12',             'type' => 'text', 'widthpercent' => self::XSMALL),
                        array('title' => '13',             'type' => 'text', 'widthpercent' => self::XSMALL),
                        array('title' => '14',             'type' => 'text', 'widthpercent' => self::XSMALL),
                        array('title' => '15',             'type' => 'text', 'widthpercent' => self::XSMALL),
                        array('title' => '16',             'type' => 'text', 'widthpercent' => self::XSMALL),
                        array('title' => '17',             'type' => 'text', 'widthpercent' => self::XSMALL),
                        array('title' => '18',             'type' => 'text', 'widthpercent' => self::XSMALL),
                        array('title' => '19',             'type' => 'text', 'widthpercent' => self::XSMALL),
                        array('title' => '20',             'type' => 'text', 'widthpercent' => self::XSMALL),
                        array('title' => '21',             'type' => 'text', 'widthpercent' => self::XSMALL),
                        array('title' => '22',             'type' => 'text', 'widthpercent' => self::XSMALL),
                        array('title' => '23',             'type' => 'text', 'widthpercent' => self::XSMALL),
                        array('title' => '24',             'type' => 'text', 'widthpercent' => self::XSMALL),
                        array('title' => '25',             'type' => 'text', 'widthpercent' => self::XSMALL),
                        array('title' => '26',             'type' => 'text', 'widthpercent' => self::XSMALL),
                        array('title' => '27',             'type' => 'text', 'widthpercent' => self::XSMALL),
                        array('title' => '28',             'type' => 'text', 'widthpercent' => self::XSMALL),
                        array('title' => '29',             'type' => 'text', 'widthpercent' => self::XSMALL),
                        array('title' => '30',             'type' => 'text', 'widthpercent' => self::XSMALL),
                        array('title' => '31',             'type' => 'text', 'widthpercent' => self::XSMALL),
                        array('title' => 'Total ore',      'type' => 'text', 'widthpercent' => self::MEDIUM),
                        array('title' => 'Zile lucrate',   'type' => 'text', 'widthpercent' => self::MEDIUM),
                        array('title' => 'Zile nelucrate', 'type' => 'text', 'widthpercent' => self::MEDIUM),
                    ),
                );
                break;
            default:
                break;
        }
        return $header;
    }
    
    protected function _getExportData($action, $reportFilters = array(),$page = 1,$numberPerPage = 10,$component = 'top')
    {
        $db = new Application_Model_Report();
       
        $reportData = array();
        switch ($action) {
            case 'documents':
                $reportData = $db->getDocumentsReportDataPage($reportFilters,0,0,$page,$component,$numberPerPage);
                break;
            case 'chat':
                $reportData = $db->getChatReportDataPage($reportFilters,0,0,$page,$component,$numberPerPage);
                break;
            case 'computer':
                $reportData = $db->getComputerReportData($reportFilters,0,0,$page,$numberPerPage,$component);
                break;
            case 'internet':
                $reportData = $db->getInternetReportDataPage($reportFilters,0,0,$page,$component,$numberPerPage);
                break;
            case 'applications':
                $reportData = $db->getApplicationsReportDataPage($reportFilters,0,0,$page,$component,$numberPerPage);       
                break;
            case 'files':
                $reportData = $db->getFilesReportDataPage($reportFilters,0,0,$page,$component,$numberPerPage);
                break;
            case 'top':
                $reportData = $db->getTopReportData($reportFilters,0,0,$page,'export',$numberPerPage);
                break;
            case 'activity':
                $reportData = $db->getActivityReportData($reportFilters,0,0,$page,$numberPerPage);
                break;
            case 'roi':
                $reportData = $db->getRoiReportData($reportFilters,0,0,$page,$component,$numberPerPage);
                break;
            case 'performance':
                $reportData = $db->getPerformanceReportData($reportFilters);
                break;
            case 'timekeeping':
                $reportData = $db->getPunchInReportData($reportFilters,0,0,$page,$numberPerPage);
                break;
            default:
                break;
        }
       
        return $reportData;
    }

    /***************************************
     * CSV Export Functionality
     ***************************************/
    
    protected function _exportCsvDocument($action, &$filters, $headers, $data, $component)
    {
    	$reportName = $this->getReportName($action);
    	
        // Create file name
        $currentTime = time();
        $timeAfterOneHour = $currentTime+60*60;
        
        $reportNameWithoutSpaces = str_replace(" ", "_", $reportName);
        $filename = ucfirst($reportNameWithoutSpaces) . '_export-' . date('Y_m_d-').date('H_i_s',$timeAfterOneHour) . '.csv';
            	
        //$filename = ucfirst($action) . '_export-' . date('Ymd-His') . '.csv';
        
        // Build output
        $export = array();
        array_push($export, "\"". $reportName ."\"");
        array_push($export, "\"Data Generare Raport  \" ".", \" ".date('Y-m-d H:i:s')."\"");
        
        $export = $this->_buildFiltersCSV($export, $filters,$action);
      
        if (!empty($filters)) {
            $this->_attachCsvFilters($action, $export, $filters, count($headers));
        }
        $i = 2;
        if (!empty($headers)) {
            $this->_attachCsvHeader($export, $headers[$component]);
        }
        
        while(!empty($data[$component]) )
		{
	        if (!empty($data[$component])) foreach ($data[$component] as $item) {
	        	if($action == 'computer')
	        	{
	        		$item['stopList'] = str_replace("</br>", " , ", $item['stopList']);
	        	}
	        	if($action == 'activity')
	        	{
	        		$item['idleList'] = str_replace("</br>", " , ", $item['idleList']);
	        	}	        	
	        	
	            $line = $this->_prepareLine($action, $component, $item,'csv');
	            $this->_attachCsvLine($export, $headers[$component], array_values($line));
	        }

	        $data = $this->_getExportData($action, $filters,$i,200,$component);
	        
	        if($action == 'performance')
	        {
	        	$data = array();
	        }
	        
	        $i++;
		}
        
         
        //print_r($export);die();
        $output = implode(PHP_EOL, $export);
         echo $output;
        // Generate export file
        header('Content-type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
    }
    
    protected function _attachCsvHeader(&$export, &$headers)
    {
        $attachment = array();
        foreach ($headers as $column) {
            $attachment[] = '"' . str_replace('"', '\"', $column['title']) . '"';
        }
        array_push($export, implode(',', $attachment));
    }
    
    protected function _prepareLine($action, $component, $item,$type = 'pdf')
    {
    	//echo "<pre>";print_r($item);echo "</pre>";die();
        switch ($action) {
            case 'documents':
                if ($component == 'usage') {
                    $line = array($item['Icon'], $item['documentName'], $item['documentUsageTime'], $item['documentUsagePercent']);
                } else if ($component == 'crons') {
                    $line = $item;
                }
                break;
            case 'chat':
                 if ($component == 'crons') {
                    $line = $item;           
                 }
                 else             	
                    $line = array($item['icon'],
                    			  $item['remoteUser'],
                    			  $item['selector_csv'], 
                    			  $item['usagePercent'],
                    			  $item['usageTime']
                    	    );  
            	 break;
            case 'computer':
                 $line = array( $item['user'],
                    		    $item['day'],
                    		    $item['start'], 
                    			$item['end'],
                    			$item['activ'],
                    			$item['inactiv'],
                    			$item['oprit'],
                    			$item['stopList']
                    	    );            	
            	break;
            case 'internet':
                 if ($component == 'usage') {
                    $line = array($item['icon'],
                    			  $item['site'],
                    			  $item['selector_csv'], 
                    			  $item['usageTime'],
                    			  $item['usagePercent']
                    	    );  
                } else if ($component == 'crons') {
                	
                	if($type == 'pdf'){
	                	if(strlen($item['title']) >50 )
	                	{
	                		$item['title'] = substr($item['title'],0,50)."...";
	                	}
	                    if(strlen($item['link']) >70 )
	                	{
	                		$item['link'] = substr($item['link'],0,70)."...";
	                	}    
                	}            	
                    $line = array($item['icon'],
                    			  $item['tStart'],
                    			  $item['hEnd'], 
                    			  $item['duration'],
                    			  $item['computer'],
                    			  $item['user'],
                    			  $item['process'],
                    			  $item['title'],
                    			  $item['link']
                    	    );
                }
                break;   
            case 'applications':
                 if ($component == 'crons') {
                    if(strlen($item['title']) >50 )
                	{
                		$item['title'] = substr($item['title'],0,50)."...";
                	}                 	
                    $line = array($item['Icon'],
                    			  $item['process'],
                    			  $item['title'],
                    			  $item['computer'],
                    			  $item['user'],
                    			  $item['dStart'],
                    			  $item['hEnd'],                    			  
                    			  $item['duration']
                    	    );          
                 }
                 else 	
                    $line = array($item['Icon'],
                    			  $item['application'],
                    			  $item['selector_csv'], 
                    			  $item['usagePercent'],
                    			  $item['usageTime']
                    	    );  
                   	break;          	
            case 'files':
            case 'top':
            case 'roi':
            case 'performance':
            case 'timekeeping':
            default:
                $line = $item;
                break;
        }
         //echo "<pre>";print_r($line);echo "</pre>";die();
        return $line;
    }

    protected function _attachCsvFilters($action, &$export, &$filters)
    {
        $filterOutput = array();
        switch ($action) {
            case 'documents':
            case 'chat':
            case 'computer':
            case 'internet':
            case 'applications':
            case 'files':
            case 'top':
            case 'activity':
                break;
            case 'roi':
                break;
            case 'performance':
                break;
            case 'timekeeping':
                break;
            default:
                break;
        }
        foreach ($filterOutput as $csvline) {
            // $export[] = $csvline;
        }
    }
    
    protected function _attachCsvLine(&$export, &$headers, $line)
    {
        $attachment = array();
        foreach ($headers as $index => $column) {
            switch ($column['type']) {
                case 'image':
                    $attachment[] = '-';
                    break;
                case 'number':
                    $attachment[] = $line[$index];
                    break;
                case 'text':
                default:
                    $attachment[] = '"' . str_replace('"', '\"', $line[$index]) . '"';
                    break;
            }
        }
        array_push($export, implode(',', $attachment));
    }
    
    /***************************************
     * PDF Export Functionality
     ***************************************/
    
    protected function _exportPdfDocument($action, &$filters, &$headers, &$data, $component)
    {
		$limit = array(
			'applications' => 5,
			'documents' => 5,
			'internet' => 4,
			'files' => 5,
			'chat' => 7
		);    	
		
		
        // Create file name
        $reportName = $this->getReportName($action);
        
        $currentTime = time();
        $timeAfterOneHour = $currentTime+60*60;
        
        $reportNameWithoutSpaces = str_replace(" ", "_", $reportName);
        $filename = ucfirst($reportNameWithoutSpaces) . '_export-' . date('Y_m_d-').date('H_i_s',$timeAfterOneHour) . '.pdf';
		
        $exportPath = BASE_PATH . "\\public\\temporary_exports\\";
        // Build output
        $output = '';
        $export = array();
        $cronLink = array();
        if (!empty($filters)) {
            $this->_attachCsvFilters($action, $output, $filters);
        }
        if (!empty($headers)) {
            $this->_attachPdfHeader($export, $headers[$component]);
        }
		$i = 2;
		
		//echo "<pre>";print_r($data);echo "<pre>";die();
		//echo "<pre>";print_r($export);echo "</pre>";die();
		if(!empty($limit[$action])){
			$max = $limit[$action];
		}
		else
			$max = 11;
		$k =1;
		//print_r($action);
		//echo "<pre>";print_r($data);echo "<pre>";die();
		while(!empty($data[$component]) )
		{
			//echo "<pre>";print_r($data);echo "<pre>";die();
			if($component == 'crons' || $action == 'files' || $action == 'internet')
				if($i % $max == 0 )
				{
					
			        $currentTime = time();
			        $timeAfterOneHour = $currentTime+60*60;	
			        				
			        $filename = ucfirst($reportNameWithoutSpaces) . '_export-' . date('Y_m_d-').date('H_i_s',$timeAfterOneHour)."_partea".$k. '.pdf';
        					
					$this->_exportOutput($filters, $action, $export, $reportName, $exportPath.$filename,"F");
					
			        // Build output
			        $output = '';
			        $export = array();
			        
					if (!empty($filters)) {
			            $this->_attachCsvFilters($action, $output, $filters);
			        }
			        if (!empty($headers)) {
			            $this->_attachPdfHeader($export, $headers[$component]);
			        }
					$k++;
					
					$cronLink[] = $filename;
				}
			$j = 0;
	        if (!empty($data[$component])) foreach ($data[$component] as $item) {
	            $line = $this->_prepareLine($action, $component, $item);
	            //echo "<pre>";print_r($line);echo "<pre>";die();
	            $this->_attachPdfLine($export, $headers[$component], array_values($line),$j);
	            $j++;
	        }
			

			$data = $this->_getExportData($action, $filters,$i,200,$component);
			//echo "<pre>";print_r($data);echo "<pre>";die();
			//echo "<pre>";print_r($export);echo "<pre>";die();
			$temp_data = array();
			if($action == 'activity')
			{
				$temp_data = $this->getPauseList($data);
				$data = $temp_data;
			}
		    if($action == 'computer')
	        {
				$temp_data = $this->getStopList($data,$filters);
				$data = $temp_data;
	        }			
			$i++;
	        if($action == 'performance')
	        {	
	        	$data = array();
	        }		
		}
		//print_r($i);die();
		
		//echo "<pre>";print_r($export);echo "<pre>";die();
		if($component == 'crons' || $action == 'files')
		{
	        $currentTime = time();
	        $timeAfterOneHour = $currentTime+60*60;			
			$filename = ucfirst($reportNameWithoutSpaces) . '_export-' . date('Y_m_d-').date('H_i_s',$timeAfterOneHour)."_partea".$k. '.pdf';
			
			$this->_exportOutput($filters, $action, $export, $reportName,  $exportPath.$filename,"F");
			$cronLink[] = $filename;
			
			$temp_output = '<h3>'.$reportName." - Export PDF </h3>";
			$temp_output .= '<p> Datorita numarului mare de date , exportul pdf a fost impartit in mai mult documente. Pe acestea le puteti descarca de la urmatoarele adrese: </p>';
			$temp_output .= '<ul>';
			foreach($cronLink as $cron){
				$temp_output .= "<li><a target='_blank' href ='".Zend_Controller_Front::getInstance()->getBaseUrl()."/temporary_exports/".$cron." '>".$cron."</a></li>";
			}
			$temp_output .= "</ul>";
			
			echo $temp_output;
			
		}
		else
			$this->_exportOutput($filters, $action, $export, $reportName, $filename,"D");

    }
    
    protected function _exportOutput($filters,$action,$export,$reportName,$filename,$type = "D")
    {
		$exportFilters = $this->_buildFilters($filters,$action);
		$output = '';
        $output .= '<h1 style="text-align:center" >'.$reportName.'</h1><h3>Data generare raport : '.date('Y/m/d - H:i:s').'</h3>'.$exportFilters.'<table style="width:100%;border:1px solid #000000;border-spacing:0px;border-collapse:colapse">' . implode('', $export) . '</table>';
		//echo "<pre>";print_r($output);echo "<pre>";die();
        // Generate export file
        require_once(realpath(dirname(dirname(APPLICATION_PATH)) . '/thirdparty/mpdf/mpdf.php'));
        $mpdf = new mPDF('en-GB-x','A4','','',10,10,10,10,6,3); 
 		$stylesheet = file_get_contents(BASE_PATH.'/public/css/general.css');
        
        $mpdf->debug=false;
		$mpdf->allow_charset_conversion=true;
		$mpdf->charset_in='windows-1252';
        //$mpdf->SetDisplayMode('fullpage');
        $mpdf->list_indent_first_level = 0;
        $mpdf->WriteHTML($stylesheet,1);
        $mpdf->WriteHTML($output);
        $mpdf->Output($filename, $type);    	
    }
    
    protected function _attachPdfFilters($action, &$output, &$filters)
    {
        $filterOutput = '';
        switch ($action) {
            case 'documents':
            case 'chat':
            case 'computer':
            case 'internet':
            case 'applications':
            case 'files':
            case 'top':
            case 'activity':
                break;
            case 'roi':
                break;
            case 'performance':
                break;
            case 'timekeeping':
                break;
            default:
                break;
        }
    }
    
    protected function _attachPdfHeader(&$export, &$headers)
    {
        $unit = $this->_calculateUnitSize($headers);
        $attachment = array();
        
        
        
        foreach ($headers as $column) {
            $attachment[] = '<th style="background-color:#CCCCCC;width:' . number_format($unit * floatval($column['widthpercent']), 2) . '%;">' . $column['title'] . '</th>';
        }
        array_push($export, '<tr>' . implode('', $attachment) . '</tr>');
    }
    
    protected function _attachPdfLine(&$export, &$headers, $line,$j = 0)
    {
        $attachment = array();

        foreach ($headers as $index => $column) {
            switch ($column['type']) {
                case 'image':
					//print_r("<img src='{$var}' />");die();
					
					/*$filePath = BASE_PATH . '\public\img\icons\iconita1.gif';
					$path = $this->getFrontController()->getBaseUrl().'/img/icons/iconita1.gif';
					  $success = true;
					  if (!file_exists($filePath)) {
					   $image = base64_decode($line[$index]);
					   $success = file_put_contents($filePath, $image);
					  }*/
					
                   // $attachment[] = "<img src='". $path ."' />";
                   $attachment[] = $line[$index];
                    break;
                case 'number':
                    $attachment[] = $line[$index];
                    break;
                case 'text':
                default:
                    $attachment[] = str_replace(PHP_EOL, '<br/>', $line[$index]);
                    break;
            }
        }
        if($j%2 == 0)
        	array_push($export, '<tr class="tableExportEven" ><td style="border:1px solid #000000;text-align:center;padding:5px 0 5px 0;">' . implode('</td><td style="border:1px solid #000000;text-align:center;padding:5px 0 5px 0;">', $attachment) . '</td></tr>');
       else
           array_push($export, '<tr class="tableExportOdd" ><td style="border:1px solid #000000;text-align:center;padding:5px 0 5px 0;">' . implode('</td><td style="border:1px solid #000000;text-align:center;padding:5px 0 5px 0;">', $attachment) . '</td></tr>'); 	
    }
    
    protected function _calculateUnitSize(&$headers)
    {
        $total = 0;
        foreach ($headers as $column) {
            $total += $column['widthpercent'];
        }
        return 100.0 / floatval($total);
    }
	
	protected function _buildFilters($options,$action = 'all')
	{
		$where = '';
		if($action != 'performance'){
			$where = '<div id="filtre" ><h3>Filtre </h3>';
			
			//echo "<pre>";print_r($options);echo "</pre>";die();
			
			if (!empty($options['users'])) {
				$where .= ' <p> Utilizatori : ( ';
				$first = true;
				foreach($options['users'] as $user){
					if($first == false){
						$where .= ",".$user;
					}else{
						$where .= $user;
						$first = false;
					} 
				}
				$where .= " ) </p>";
			}
			else
				$where .= ' <p> Utilizatori : Toata Organigrama ';
			
			if($action != 'timekeeping'){
				
				if (isset($options['dateIs'])) {
					
					$where .="<p> Data:{$options['dateIs']}</p>";
				}else if (isset($options['dateStart']) && isset($options['dateEnd'])) {
					$where .= "<p>Data de Inceput - Data de Sfarsit : {$options['dateStart']} - {$options['dateEnd']} </p>";
		            switch ($options['interval']) {
		                case 'specweek':
							$days = array();
		                    foreach ($options['days'] as $day) {
		                        $days[] = $day;
		                    }			
							$where .= "<p> Zile Speficifice : ( " . implode(',', $days) . " ) </p>";		
		                    break;
		                case 'workweek':
							$where .= "<p> Saptamana de lucru ( L - V) </p> ";
		                    break;
		                case 'endweek':
							$where .= "<p> Week-end ( S - D) </p> ";
		                    break;
		                case 'allweek':
							$where .= "<p> Intreaga Saptamana </p> ";
		                default:
		                    // Do not filter by days
		                    break;
		            }
		        }
				if($action != 'roi' ){
			   		switch($options['timeinterval']) {
			            case 'workinghours':
							$where .= "<p> Interval Orar : {$options['hArray']['startTime']} - {$options['hArray']['endTime']} </p> ";
								  			  
			                break;
			            case 'workallhours':
							$where .= "<p> Program cumulat </p>";
			            default:
			                // Do not filter by hours
			                break;
			        }
				}
			}
			else
			{
				$where = $where."<p>Luna : ".$options['month']."</p>";
				$where = $where."<p>Anul : ".$options['year']."</p>";
			}
		}
		
		
		return $where."</div>";
	}
	
	function getPauseList($data)
	{
        $temp_data = array();

        $db = new Application_Model_Report();
        	
        foreach($data['usage'] as $item)
        {
        		
        	$var = $db->getActivityReportDataPause($item['user'], $item['day']);
        		
        	$item['idleList'] = $var;
        		
        		
        	$temp_data['usage'][] = $item;
        	//print_r($item);die();
        }
		

        return $temp_data;
	}
	
	function getStopList($data,$filters)
	{
        $temp_data = array();

        $db = new Application_Model_Report();
        	
        foreach($data['usage'] as $item)
        {
        		
        	$var = $db->getComputerReportDataStop($filters,$item['user'], $item['day']);
        		
        	$item['stopList'] = $var;
        		
        		
        	$temp_data['usage'][] = $item;
        	//print_r($item);die();
        }
		

        return $temp_data;
	}

	protected function _buildFiltersCSV($output,$options,$action = 'all')
	{
		$where = '';
		if($action != 'performance'){
			array_push($output, "\"Filtre : \"");
			
			//echo "<pre>";print_r($output);echo "</pre>";die();
			
			if (!empty($options['users'])) {
				$where .= "\" Utilizatori : ( ";
				$first = true;
				foreach($options['users'] as $user){
					if($first == false){
						$where .= ",".$user;
					}else{
						$where .= $user;
						$first = false;
					} 
				}
				$where .= " ) \"";
				array_push($output, $where);
			}
			else
			{
				$where = "\"  Utilizatori : Toata Organigrama \"";
				array_push($output, $where);
			}
			
			if($action != 'timekeeping'){
				
				if (isset($options['dateIs'])) {
					
					$where = "\" Data:{$options['dateIs']}\"";
					array_push($output, $where);
				}else if (isset($options['dateStart']) && isset($options['dateEnd'])) {
					$where = "\"Data de Inceput - Data de Sfarsit : {$options['dateStart']} - {$options['dateEnd']} \"";
					array_push($output, $where);
		            switch ($options['interval']) {
		                case 'specweek':
							$days = array();
		                    foreach ($options['days'] as $day) {
		                        $days[] = $day;
		                    }			
							$where = "\" Zile Speficifice : ( " . implode(',', $days) . " ) \"";
							array_push($output, $where);		
		                    break;
		                case 'workweek':
							$where = "\"Saptamana de lucru ( L - V) \"";
							array_push($output, $where);	
		                    break;
		                case 'endweek':
							$where = "\" Week-end ( S - D) \"";
							array_push($output, $where);	
		                    break;
		                case 'allweek':
							$where = "\" Intreaga Saptamana \"";
							array_push($output, $where);	
		                default:
		                    // Do not filter by days
		                    break;
		            }
		        }
				if($action != 'roi' ){
			   		switch($options['timeinterval']) {
			            case 'workinghours':
							$where = "\" Interval Orar : {$options['hArray']['startTime']} - {$options['hArray']['endTime']} \" ";
							array_push($output, $where);		  			  
			                break;
			            case 'workallhours':
							$where .= "\" Program cumulat \"";
							array_push($output, $where);	
			            default:
			                // Do not filter by hours
			                break;
			        }
				}
			}
			else
			{
				$where = "\"Luna : ".$options['month']."\"";
				array_push($output, $where);	
				$where = "\"Anul : ".$options['year']."\"";
				array_push($output, $where);	
			}
		}
		
		//echo "<pre>";print_r($output);echo "</pre>";die();
		return $output;
	}

	public function getReportName($action)
	{
	    	switch ($action){
    		case "computer":
    			return "Utilizarea Calculatorului";
    		
    			break;
    		case "applications":
    			return "Monitorizare Aplicatii";
    			break;
    		case "documents":
    			return "Monitorizare Documente";
    			break;
    		case "internet":
    			return "Monitorizare Internet";
    			break;
    		case "chat":
    			return "Chat";
    			break;
    		case "files":
    			return "Monitorizare Fisiere";
    			break;
    		case "activity":
    			return "Activitate";
    			break;
     		case "timekeeping":
     			return "Fisa de Pontaj";
    			break;
     		case "top":
     			return "Top Angajati";
    			break;
    		case "roi":
    			return "ROI Angajati";
    			break;
    		case "performance":
    			return "Performanta Individuala";
    			break;  
    		default:
    			return "Fara Nume";	  			   			   			   			   			    			    			    			    			    			
    	}		
	}
	
	
}