<?php

class AjaxController extends Zend_Controller_Action
{
    public function init() 
    {
        if(!$this->_request->isXmlHttpRequest()) {
            throw new Exception('Not a valid JSON request');
        } else {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNeverRender();
        }
    }

    public function indexAction()
    {
        throw new Exception('Not a valid JSON request');
    }
    
    public function __call($method, $data)
    {
        // Ignore params in get
        $data = null;
        $method = substr($method, 0, -6);
        return $this->_fetchReport($method);
    }
    
    protected function _fetchReport($type, $reportTop = 0, $reportLimit = 0)
    {
        try {
            
			$numberPerPage = 100;
			$page = 1;
            $db = new Application_Model_Report();
            $reportFilters = $this->_request->getPost('filters');
			$page = $this->_request->getPost('page');
			$regenChart = $this->_request->getPost('type');
			$temp = $this->_request->getPost('numberPerPage');
			if(!empty($temp))
				$numberPerPage = $this->_request->getPost('numberPerPage');
				
			if( $type == 'computerstop' || $type == 'activitypause'){
				$user = $this->_request->getPost('user');	
				$day = $this->_request->getPost('day');
			}			
            $reportData = array();
            switch ($type) {
                
                // First wave
                case 'documents':
                    $reportData = $db->getDocumentsReportData($reportFilters, $reportTop, $reportLimit);
                    break;
				case 'documentpage':
					$reportData = $db->getDocumentsReportDataPage($reportFilters, $reportTop, $reportLimit,$page,$regenChart,$numberPerPage);
					break;
                case 'chat':
                    $reportData = $db->getChatReportData($reportFilters, $reportTop, $reportLimit);
                    break;
                case 'chatpage':
                    $reportData = $db->getChatReportDataPage($reportFilters, $reportTop, $reportLimit,$page,$regenChart,$numberPerPage);
                    break;					
                case 'computer':
                    $reportData = $db->getComputerReportData($reportFilters, $reportTop, $reportLimit,$page,$numberPerPage,$regenChart);
                    break;
                case 'computerstop':
                    $reportData = $db->getComputerReportDataStop($reportFilters,$user,$day);
                    break;					
                case 'internet':
                    $reportData = $db->getInternetReportData($reportFilters, $reportTop, $reportLimit);
                    break;
 				case 'internetpage':
                    $reportData = $db->getInternetReportDataPage($reportFilters, $reportTop, $reportLimit,$page,$regenChart,$numberPerPage);
                    break;					
                case 'applications':
                    $reportData = $db->getApplicationsReportData($reportFilters, $reportTop, $reportLimit);
                    break;
                case 'applicationspage':
                    $reportData = $db->getApplicationsReportDataPage($reportFilters, $reportTop, $reportLimit,$page,$regenChart,$numberPerPage);
                    break;
                case 'files':
                    $reportData = $db->getFilesReportData($reportFilters, $reportTop, $reportLimit);
                    break;
                case 'filespage':
                    $reportData = $db->getFilesReportDataPage($reportFilters, $reportTop, $reportLimit,$page,$regenChart,$numberPerPage);
                    break;            
					    
                // Second wave
                case 'top':
                    $reportData = $db->getTopReportData($reportFilters, $reportTop, $reportLimit,$page,'top',$numberPerPage);
                    break;
                case 'activity':
                    $reportData = $db->getActivityReportData($reportFilters, $reportTop, $reportLimit,$page,$numberPerPage);
                	break;
                case 'activitypause':
                    $reportData = $db->getActivityReportDataPause($user,$day);
                	break;					
                case 'roi':
                    $reportData = $db->getRoiReportData($reportFilters, $reportTop, $reportLimit,$page,'top',$numberPerPage);
                    break;
                case 'performance':
                    $reportData = $db->getPerformanceReportData($reportFilters, $reportTop, $reportLimit);
                    break;
                case 'timekeeping':
                    $reportData = $db->getPunchInReportData($reportFilters, $reportTop, $reportLimit,$page,$numberPerPage);
                    break;
                
                // Fail
                default:
                    // will return an empty array
                    break;
            }
			//echo "<pre>"; print_r($reportData);echo "</pre>";die();
            return $this->_helper->json(array(
                "data" => $reportData,
                "filters" => $reportFilters
            ));
            
        } catch(Exception $e) {
            echo $e->getMessage();
        }
    }
}

/* EOF */