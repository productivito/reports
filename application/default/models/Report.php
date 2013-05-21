<?php

class Application_Model_Report
{
    protected $_weekDays = array(
        'd' => 1,
        'l' => 2,
        'ma' => 3,
        'mi' => 4,
        'j' => 5,
        'v' => 6,
        's' => 7
    );

    protected $_idlePauseSeconds = 300;
    
    public function __construct()
    { /* Constructor directives */ }
    
    public function getDocumentsReportData($options, $top = 0, $limit = 0)
    {
        $db = new Application_Table_dbReports();
        $dbRemoteClient = new Application_Table_dbIdle();
        $response = $db->getDocumentsReportDataGrid($options, $top, $limit);
        if (empty($response)) { return array(); }
		//print_r($response);die();
        // Usage
        $result = array();
        $graphData = array();
		$cronProgress = array();
		
		foreach($response['response'] as $item)
		{
			$icon = '';
            $icon = $db->getDocumentsIcon($item['WindowName'],$options);
           // print_r($icon);die();
			$image = '';
			if(!empty($icon))
			{
				$filePath = BASE_PATH . "\\public\\img\\icons\\".$icon[0]['iconID'].".gif";
				$path = Zend_Controller_Front::getInstance()->getBaseUrl().'/img/icons/'.$icon[0]['iconID'].'.gif';
			    $success = true;
				if (!file_exists($filePath)) {
				   $image = base64_decode($icon[0]['Icon']);
				   $success = file_put_contents($filePath, $image);					
				}
				
				$image = "<img src='{$path}'/>";
			}	
				
			$result[$item['Row']]['Icon'] = $image;								
			$result[$item['Row']]['documentName'] = $item['WindowName'];
			$result[$item['Row']]['documentUsagePercent'] = number_format(($item['Timp']/$response['totalandcount'][0]['TimpTotal']) *100,2);				
			$result[$item['Row']]['documentUsageTime'] = $this->_sec2hms($item['Timp']);
		
			$graphData[md5($item['WindowName'])] = array(
                'Hash' => md5($item['WindowName']),
                'Document' => $result[$item['Row']]['documentName'],
                'Usage' => $result[$item['Row']]['documentUsagePercent'],				
			);
		
		}
		
		foreach($response['cron'] as $cronItem)
		{
			$image = '';
			if(!empty($cronItem['iconID']))
			{
				$filePath = BASE_PATH . "\\public\\img\\icons\\".$cronItem['iconID'].".gif";
				$path = Zend_Controller_Front::getInstance()->getBaseUrl().'/img/icons/'.$cronItem['iconID'].'.gif';
			    $success = true;
				if (!file_exists($filePath)) {
				   $image = base64_decode($cronItem['Icon']);
				   $success = file_put_contents($filePath, $image);					
				}
				
				$image = "<img src='{$path}'/>";
			}
			
			$cronProgress[] = array (
                'documentHash' => md5($cronItem['WindowName']),
                'icon' => $image,
                'documentName' => $cronItem['WindowName'],
                'computer' =>$cronItem['reDomain'],
                'user' => $cronItem['reUser'],
	            'date_HStart' => $cronItem['Date']." / ".$cronItem['StartHour'],
                'tend' => $cronItem['StopHour'],
                'documentUsageTime' => $this->_sec2hms($cronItem['Time']),				
			);
			
		}
		//echo "<pre>";print_r($result);echo "</pre>";die();
     
        // Return data
        return array(
            'usage' => $result,
            'crons' => $cronProgress,
            'graph' => $graphData
        );		
/*        $totalUsageTime = 0.0;
        foreach ($response as $document) {
            //if (empty($document['rieID'])) {
                $docName = md5($document['reWnd']);
                if (!isset($result[$docName])) {
                    $result[$docName] = array(
                        // 'documentIcon' => 'data:image/gif;base64,' . $document['piData'],
                        'documentIcon' => '',
                        'documentHash' => $docName,
                        'documentName' => $document['reWnd'],
                        'documentUsageTime' => 0.0,
                        'documentUsagePercent' => 0.0,
                    );
                }
                $start = $document['reStartDate']->getTimestamp();
                $end = $document['reEndDate']->getTimestamp();
                $result[$docName]['documentUsageTime'] += ($end - $start);
                $totalUsageTime += ($end - $start);
            }
        //}
        foreach ($result as $docName => $document) {
            $result[$docName]['documentUsagePercent'] = number_format(100 * $result[$docName]['documentUsageTime'] / $totalUsageTime, 2);
            $result[$docName]['documentUsageTime'] = $this->_sec2hms($result[$docName]['documentUsageTime']);
            $graphData[$docName] = array(
                'DocHash' => $result[$docName]['documentHash'],
                'Document' => $result[$docName]['documentName'],
                'Usage' => number_format($result[$docName]['documentUsagePercent'], 2),
            );
        }
        usort($result, array($this, '_topDesc2'));

        // Chronological
        $cronProgress = array();
        foreach ($response as $document) {
            $remotecomputer = $dbRemoteClient->getEmployeesFromServerByID($document['reComputerID']);
            $docName = md5($document['reWnd']);
            $start = $document['reStartDate']->getTimestamp();
            $end = $document['reEndDate']->getTimestamp();
            $cronProgress[] = array(
                'documentHash' => $docName,
                'documentName' => $document['reWnd'],
                'computer' => $remotecomputer['rcName'],
                'user' => $document['reUser'],
                'date' => date('d.m.Y', $document['reStartDate']->getTimestamp()),
                'tstart' => date('H:i:s', $document['reStartDate']->getTimestamp()),
                'tend' => date('H:i:s', $document['reEndDate']->getTimestamp()),
                'documentUsageTime' => $this->_sec2hms($end - $start),
            );
        }*/

    }

   public function getDocumentsReportDataPage($options, $top = 0, $limit = 0,$page,$type,$numberPerPage)
    {
    	//if export 	
        if($type == 'usage'){
    		$type = 'top';
    	}
    	if( $type == 'crons'){
    		$type = 'cron';
    	} 
    	 
        $db = new Application_Table_dbReports();
        
		//echo $page." ".$numberPerPage;die();
        $response = $db->getDocumentsReportDataGrid($options, $top, $limit,$page,$type,$numberPerPage);
        if (empty($response)) { return array(); }
		//print_r($response);die();
        // Usage
        $result = array();
        $graphData = array();
		$cronProgress = array();
		
		if($type == 'top' || $type == 'export' || $type == 'chart'){
			foreach($response['response'] as $item)
			{
				$icon = '';
	            $icon = $db->getDocumentsIcon($item['WindowName'],$options);
	           // print_r($icon);die();
				$image = '';
				if(!empty($icon))
				{
					$filePath = BASE_PATH . "\\public\\img\\icons\\".$icon[0]['iconID'].".gif";
					$path = Zend_Controller_Front::getInstance()->getBaseUrl().'/img/icons/'.$icon[0]['iconID'].'.gif';
				    $success = true;
					if (!file_exists($filePath)) {
					   $image = base64_decode($icon[0]['Icon']);
					   $success = file_put_contents($filePath, $image);					
					}
					
					$image = "<img src='{$path}'/>";
				}	
				$result[$item['Row']]['Icon'] = $image;								
				$result[$item['Row']]['documentName'] = $item['WindowName'];
				$result[$item['Row']]['documentUsagePercent'] = number_format(($item['Timp']/$response['totalandcount'][0]['TimpTotal']) *100,2);				
				$result[$item['Row']]['documentUsageTime'] = $this->_sec2hms($item['Timp']);
			
				$graphData[md5($item['WindowName'])] = array(
	                'Hash' => md5($item['WindowName']),
	                'Document' => $result[$item['Row']]['documentName'],
	                'Usage' => $result[$item['Row']]['documentUsagePercent'],				
				);
			
			}
		}
		
		if($type == 'export' || $type == 'cron'){
			foreach($response['cron'] as $cronItem)
			{
				$image = '';
				if(!empty($cronItem['iconID']))
				{
					$filePath = BASE_PATH . "\\public\\img\\icons\\".$cronItem['iconID'].".gif";
					$path = Zend_Controller_Front::getInstance()->getBaseUrl().'/img/icons/'.$cronItem['iconID'].'.gif';
				    $success = true;
					if (!file_exists($filePath)) {
					   $image = base64_decode($cronItem['Icon']);
					   $success = file_put_contents($filePath, $image);					
					}
					
					$image = "<img src='{$path}'/>";
				}
				
				$cronProgress[] = array (
	                'icon' => $image,
	                'documentName' => $cronItem['WindowName'],
	                'computer' =>$cronItem['reDomain'],
	                'user' => $cronItem['reUser'],
	                'date_HStart' => $cronItem['Date']." / ".$cronItem['StartHour'],
	                'tend' => $cronItem['StopHour'],
	                'documentUsageTime' => $this->_sec2hms($cronItem['Time']),				
				);
				
			}	
		}	
		
		//print_r($cronProgress);die();
        switch($type){
			case 'top':
		       return array(
		            'usage' => $result
		        );
				break;			
			case 'chart':	
				return array(
		            'graph' => $graphData
		        );	
				break;
			case 'export':
				return array(
					'usage' => $result,
		            'graph' => $graphData,
		            'crons' => $cronProgress
		        );		
				break;
			case 'cron':
		        return array(
		            'crons' => $cronProgress
	    	   ); 
			   break;			
			default:
				return array(
					'usage' => '',
		            'graph' => ''				
				);							
        }
    }
    
    public function getChatReportData($options, $top = 0, $limit = 0)
    {
        $db = new Application_Table_dbReports();
		$dbRemoteClient = new Application_Table_dbIdle();
		$ctg = new Application_Table_dbCategory();		
        $response = $db->getChatReportDataGrid($options, $top, $limit);
        if (empty($response)) { return array(); }
       // echo "<pre>";print_r($response);echo "</pre>";die();
        // Usage
        $result = array();
        $cronProgress = array();
        $graphData = array();
        $graphDataProd = array(
        	'productive' => 0,
        	'unproductive' => 0,
        	'unclassified' => 0
        );
        $totalUsageTime = 0.0;
		
		$remoteClientsTemp = $dbRemoteClient->getEmployeesFromServerRemote();
		
		$remoteClients = array();
		
		
		foreach($remoteClientsTemp as $remoteClient)
		{
			$remoteClients[$remoteClient['rcID']] = $remoteClient;
		}
		
    	$icon = '';
	    $icon = $db->getApplicationsIcon("Yahoo! Messenger",$options);
	    //print_r($icon);die();
		$image = '';
		if(!empty($icon))
		{
			$filePath = BASE_PATH . "\\public\\img\\icons\\".$icon["Yahoo! Messenger"]['iconID'].".gif";
			$path = Zend_Controller_Front::getInstance()->getBaseUrl().'/img/icons/'.$icon["Yahoo! Messenger"]['iconID'].'.gif';
		    $success = true;
			if (!file_exists($filePath)) {
			   $image = base64_decode($icon["Yahoo! Messenger"]['Icon']);
			   $success = file_put_contents($filePath, $image);					
			}
			
			$image = "<img src='{$path}'/>";
		}		
		
		
		foreach($response['response'] as $item)
		{
                $productive = "<option value=\"productive\">Productiv</option>";
                $unproductive = "<option value=\"unproductive\">Neproductiv</option>";
                $unclassified = "<option value=\"unclassified\">Neclasificat</option>";
				$typeCat = '';
                if (!empty($item['Tip'])) {
                    if ($item['Tip'] == 'productive')
					{
						$typeCat = 'Productiva';
                        $productive = "<option value=\"productive\" selected=\"selected\">Productiv</option>";
					}
                    if ($item['Tip'] == 'unproductive')
					{
						$typeCat = 'Neproductiva';
                        $unproductive = "<option value=\"unproductive\" selected=\"selected\">Neproductiv</option>";
					}
                    if ($item['Tip'] == 'unclassified')
					{
						$typeCat = 'Neclasificata';
                        $unclassified = "<option value=\"unclassified\" selected=\"selected\">Neclasificat</option>";
					}	
				}		
	        	$select = "<select id=\"{$item['WindowName']}\" class=\"prodcharddata\">" .  
	                    $unclassified .
	                    $productive .
	                    $unproductive .
	                    "</select>";
	                    	
						
				$result[$item['Row']]['remoteUser'] = $item['WindowName'];
				$result[$item['Row']]['selector'] = $select;
				$result[$item['Row']]['usageTime'] = $this->_sec2hms($item['Timp']);
				$result[$item['Row']]['usagePercent'] = number_format(($item['Timp']/$response['totalandcount'][0]['TimpTotal']) *100,2);
				$result[$item['Row']]['icon'] = $image;
			
				
				
			$graphData[md5($item['WindowName'])] = array(
                'Hash' => md5($item['WindowName']),
                'Item' => $result[$item['Row']]['remoteUser'],
                'Usage' => $result[$item['Row']]['usagePercent'],				
			);
			
			$graphDataProd[$item['Tip']]+= $result[$item['Row']]['usagePercent'];
			$totalUsageTime+= $result[$item['Row']]['usagePercent'];
		}
		

		foreach($response['cron'] as $cronItem)
		{
				$image = '';
				if(!empty($cronItem['iconID']))
				{
					$filePath = BASE_PATH . "\\public\\img\\icons\\".$cronItem['iconID'].".gif";
					$path = Zend_Controller_Front::getInstance()->getBaseUrl().'/img/icons/'.$cronItem['iconID'].'.gif';
				    $success = true;
					if (!file_exists($filePath)) {
					   $image = base64_decode($cronItem['Icon']);
					   $success = file_put_contents($filePath, $image);					
					}
					
					$image = "<img src='{$path}'/>";
				}
                $cronProgress[] = array(
                	'icon' => $image,
                    'computer' => $cronItem['reDomain'],
                    'user' => $cronItem['reUser'],
                    'remoteUser' => $cronItem['WindowName'],      // @TODO: change to method to get UserId
                    'tStart' => $cronItem['Date']." / ".$cronItem['StartHour'],
                    'hEnd' => $cronItem['StopHour'],
                    'duration' => $this->_sec2hms($cronItem['Time']),
                );			
		}		
		
		$graphDataProd['total'] = $totalUsageTime;
		
		//print_r($remoteClients);die();
		
        /*foreach ($response as $item) {
            if($item['reWnd'] != 'Yahoo! Messenger' && $item['reWnd'] != 'Contact Information Card'){

                $cat = $ctg->getCategoryByName($item['reWnd'],'chat');
                $productive = "<option value=\"productive\">Productiv</option>";
                $unproductive = "<option value=\"unproductive\">Neproductiv</option>";
                $unclassified = "<option value=\"unclassified\">Neclasificat</option>";
                if (!empty($cat)) {
                    if(isset($cat['type']) && $cat['type'] == 'productive') {
                        $productive = "<option value=\"productive\" selected=\"selected\">Productiv</option>";
                    } else if (isset($cat['type']) && $cat['type'] == 'unproductive') {
                        $unproductive = "<option value=\"unproductive\" selected=\"selected\">Neproductiv</option>";
                    } else if (isset($cat['type']) && $cat['type'] == 'unclassified') {
                        $unclassified = "<option value=\"unclassified\" selected=\"selected\">Neclasificat</option>";
                    }
                }
                $hash = md5($item['reWnd']);             // @TODO: change to method to get UserId
                if (!isset($result[$hash])) {
                    $result[$hash] = array(
                        'remoteUser' => $item['reWnd'],  // @TODO: change to method to get UserId
                        'selector' =>
                            "<select id=\"{$item['reWnd']}\" class=\"prodcharddata\">" .
                            $unclassified .
                            $productive .
                            $unproductive .
                            "</select>",
                        'usageTime' => 0.0,
                        'usagePercent' => 0.0
                    );
                }
                $start = $item['reStartDate']->getTimestamp();
                $end = $item['reEndDate']->getTimestamp();
                $result[$hash]['usageTime'] += ($end - $start);
                $totalUsageTime += ($end - $start);
                //$remotecomputer = $dbRemoteClient->getEmployeesFromServerByID($item['reComputerID']);
                $remotecomputer = $remoteClients[$item['reComputerID']];
                $cronProgress[] = array(
                    'computer' => $remotecomputer['rcName'],
                    'user' => $item['reUser'],
                    'remoteUser' => $item['reWnd'],      // @TODO: change to method to get UserId
                    'tStart' => date('d.m.Y / H:i', $start),
                    'hEnd' => date('H:i', $end),
                    'duration' => $this->_sec2hms($end - $start),
                );
            }
        }
        usort($result, array($this, '_topDesc'));
        
        foreach ($result as $key => $item) {
        	//print_r($item);die();
            $result[$key]['usagePercent'] = number_format((100.0 * $result[$key]['usageTime'] / $totalUsageTime), 2);
            $result[$key]['usageTime'] = $this->_sec2hms($result[$key]['usageTime']);
            $graphData[$key] = array(
                'Hash' => $key,
                'Item' => $item['remoteUser'],            // @TODO: change to method to get UserId
                'Usage' => $result[$key]['usagePercent'],
            );
        }
		//echo "<pre>";print_r($totalUsageTime);echo "</pre>";die();
        // Return data*/
        return array(
            'usage' => $result,
            'crons' => $cronProgress,
            'graph' => $graphData,
        	'graphProd' => $graphDataProd
        );
    }

    public function getChatReportDataPage($options, $top = 0, $limit = 0,$page,$type,$numberPerPage)
    {
    	
        if($type == 'usage'){
    		$type = 'top';
    	}
    	if( $type == 'crons'){
    		$type = 'cron';
    	}
		
        $db = new Application_Table_dbReports();
		$dbRemoteClient = new Application_Table_dbIdle();
			
        $response = $db->getChatReportDataGrid($options, $top, $limit,$page,$type,$numberPerPage);
        if (empty($response)) { return array(); }
       // echo "<pre>";print_r($response);echo "</pre>";die();
        // Usage
        $result = array();
        $cronProgress = array();
        $graphData = array();
		$graphDataProd = array(
        	'productive' => 0,
        	'unproductive' => 0,
        	'unclassified' => 0
        );
        $totalUsageTime = 0;
		$remoteClientsTemp = $dbRemoteClient->getEmployeesFromServerRemote();
		
		$remoteClients = array();
		
		
		foreach($remoteClientsTemp as $remoteClient)
		{
			$remoteClients[$remoteClient['rcID']] = $remoteClient;
		}
		
        $icon = '';
	    $icon = $db->getApplicationsIcon("Yahoo! Messenger",$options);
	    //print_r($icon);die();
		$image = '';
		if(!empty($icon))
		{
			$filePath = BASE_PATH . "\\public\\img\\icons\\".$icon["Yahoo! Messenger"]['iconID'].".gif";
			$path = Zend_Controller_Front::getInstance()->getBaseUrl().'/img/icons/'.$icon["Yahoo! Messenger"]['iconID'].'.gif';
		    $success = true;
			if (!file_exists($filePath)) {
			   $image = base64_decode($icon["Yahoo! Messenger"]['Icon']);
			   $success = file_put_contents($filePath, $image);					
			}
			
			$image = "<img src='{$path}'/>";
		}		
		
		
		if($type != 'cron'){		
			foreach($response['response'] as $item)
			{
	                $productive = "<option value=\"productive\">Productiv</option>";
	                $unproductive = "<option value=\"unproductive\">Neproductiv</option>";
	                $unclassified = "<option value=\"unclassified\">Neclasificat</option>";
					$typeCat = '';
	                if (!empty($item['Tip'])) {
	                    if ($item['Tip'] == 'productive')
						{
							$typeCat = 'Productiva';
	                        $productive = "<option value=\"productive\" selected=\"selected\">Productiv</option>";
						}
	                    if ($item['Tip'] == 'unproductive')
						{
							$typeCat = 'Neproductiva';
	                        $unproductive = "<option value=\"unproductive\" selected=\"selected\">Neproductiv</option>";
						}
	                    if ($item['Tip'] == 'unclassified')
						{
							$typeCat = 'Neclasificata';
	                        $unclassified = "<option value=\"unclassified\" selected=\"selected\">Neclasificat</option>";
						}	
					}		
		        	$select = "<select id=\"{$item['WindowName']}\" class=\"prodcharddata\">" .  
		                    $unclassified .
		                    $productive .
		                    $unproductive .
		                    "</select>";		
		
					$result[$item['Row']]['icon'] = $image;								
					$result[$item['Row']]['remoteUser'] = $item['WindowName'];
					$result[$item['Row']]['selector'] = $select;
					$result[$item['Row']]['usagePercent'] = number_format(($item['Timp']/$response['totalandcount'][0]['TimpTotal']) *100,2);				
					$result[$item['Row']]['usageTime'] = $this->_sec2hms($item['Timp']);
					$result[$item['Row']]['selector_csv'] = $typeCat;
					
					$graphData[md5($item['WindowName'])] = array(
		                'Hash' => md5($item['WindowName']),
		                'Item' => $result[$item['Row']]['remoteUser'],
		                'Usage' => $result[$item['Row']]['usagePercent'],				
					);
					
					$graphDataProd[$item['Tip']]+= $result[$item['Row']]['usagePercent'];
					$totalUsageTime+= $result[$item['Row']]['usagePercent'];
				
			}
		}
		if($type != 'top'){
			foreach($response['cron'] as $cronItem)
			{
				$image = '';
				if(!empty($cronItem['iconID']))
				{
					$filePath = BASE_PATH . "\\public\\img\\icons\\".$cronItem['iconID'].".gif";
					$path = Zend_Controller_Front::getInstance()->getBaseUrl().'/img/icons/'.$cronItem['iconID'].'.gif';
				    $success = true;
					if (!file_exists($filePath)) {
					   $image = base64_decode($cronItem['Icon']);
					   $success = file_put_contents($filePath, $image);					
					}
					
					$image = "<img src='{$path}'/>";
				}
                $cronProgress[] = array(
                	'icon' => $image,
                    'computer' => $cronItem['reDomain'],
                    'user' => $cronItem['reUser'],
                    'remoteUser' => $cronItem['WindowName'],      // @TODO: change to method to get UserId
                    'tStart' => $cronItem['Date']." / ".$cronItem['StartHour'],
                    'hEnd' => $cronItem['StopHour'],
                    'duration' => $this->_sec2hms($cronItem['Time']),
                );			
			}	
		}
		$graphDataProd['total'] = $totalUsageTime;
		//echo "<pre>";print_r($graphDataProd);echo "</pre>";die();
        // Return data*/
        switch($type){
			case 'top':
		       return array(
		            'usage' => $result,
		       		'graphProd' =>$graphDataProd
		        );
				break;
			case 'chart':	
				return array(
		            'graph' => $graphData
		        );	
				break;
			case 'export':
				return array(
					'usage' => $result,
		            'graph' => $graphData
		        );		
				break;
			case 'cron':
		        return array(
		            'crons' => $cronProgress
	    	   ); 
			   break;			
			default:
				return array(
					'usage' => '',
		            'graph' => ''				
				);							
        }        
					
    }

    
    public function getComputerReportData($options, $top = 0, $limit = 0,$page,$numberPerPage,$type)
    {

    	   	
        $db = new Application_Table_dbReports();
        $response = $db->getComputerReportDataGrid($options, $top, $limit,$page,$numberPerPage,$type);
        if (empty($response['response'])) {  return array(
											            'usage' => array(),
											            'graph' => array(),
											        	'graphUser' => array()
        ); }      
		$months = array(
			'1' =>  31,
			'2' =>  29,			
			'3' =>  31,	
			'4' =>  30,
			'5' =>  31,
			'6' =>  30,
			'7' =>  31,
			'8' =>  31,
			'9' =>  30,
			'10' =>  31,
			'11' =>  30,
			'12' =>  31,																				
		);        
		
		$result = array();
	
		//print_r($result);die();
		
		
		$graphData = array();
		$graphUser = array();
		
		foreach($response['response'] as $item){
			$result[] = array(
                'user' => $item['Utilizator'],	
                'day'  => $item['Data'],
                'start' => $item['First'],
                'end' => $item['Last'],
                'activ' => $this->_sec2hms($item['TimpTotal'] - $item['TimpInactiv']),
                'inactiv' => $this->_sec2hms( $item['TimpInactiv']),
                'oprit' => $this->_sec2hms($item['TimeStopped'])							
			);				
		} 
		
		//print_r($response);die();
		
		if($type == 'all'){
			$timpTotal = $response['graph'][0]['TimpOprit'] + $response['graph'][0]['TimpInactiv'] + ($response['graph'][0]['TimpTotal'] - $response['graph'][0]['TimpInactiv']) ;
			$timpTotal = $timpTotal/3600;
			
			$timpActiv = ($response['graph'][0]['TimpTotal'] - $response['graph'][0]['TimpInactiv'])/3600;
			//$timpActiv = number_format($timpActiv,2);
			
			$timpPauza = $response['graph'][0]['TimpInactiv']/3600;
			//$timpPauza = number_format($timpPauza,2);
			
			$timpOprit = $response['graph'][0]['TimpOprit']/3600;
			//$timpOprit = number_format($timpOprit,2);
			$graphData[] = array(
				'pause'  => intval($timpPauza),	
				'active' => intval($timpActiv),
				'stopped' => intval($timpOprit),
				'on' => intval($timpActiv + $timpPauza),
				'total' => intval($timpTotal)
			);
			
			foreach($response['graphUser'] as $item){
				$temp1 = floatval(($item['TimpTotal'] - $item['TimpInactiv'])/3600);
				$temp2 = floatval( $item['TimpInactiv']/3600);
				$temp3 = floatval($item['TimeStopped']/3600);
				$graphUser[] = array(
	                'activ' => number_format($temp1,2),
	                'inactiv' => number_format($temp2,2),
	                'oprit' => number_format($temp3,2)						
				);				
			}			
		}			
			

        // Return data
        return array(
            'usage' => $result,
            'graph' => $graphData,
        	'graphUser' => $graphUser
        );		
        
/*        $temp = $this->_parseComputerTimes($response);

        $result = array();
        $graphData = array();
        
        $dbRemoteClient =  new Application_Table_dbIdle();

		$remoteClientsTemp = $dbRemoteClient->getEmployeesFromServerRemote();		
		$remoteClients = array();	
		
		foreach($remoteClientsTemp as $remoteClient)
		{
			$remoteClients[$remoteClient['rcID']] = $remoteClient;
		}
		
		
        
        foreach ($temp as $computerIndex => $users) {
            //$remotecomputer = $dbRemoteClient->getEmployeesFromServerByID($computerIndex);
            $remotecomputer = $remoteClients[$computerIndex];
            $computer = empty($remotecomputer) ? 'Calculator ' . $computerIndex : $remotecomputer['rcName'];
            foreach ($users as $user => $sessions) {
                $usrSessions = array();
                foreach ($sessions as $sid => $session) {
                    $activeSessions = array();
                    $this->_parseUserActiveSessions($activeSessions, $computer, $user, $session);
                    if (count($activeSessions)) {
                        $lastSession = $activeSessions[count($activeSessions) - 1];
                        if (!empty($usrSessions)) {
                            array_push($usrSessions, array(
                                'computer' => $computer,
                                'user' => $user,
                                'start' => $usrSessions[count($usrSessions) - 1]['end'],
                                'end' => $lastSession['start'],
                                'duration' => $lastSession['start'] - $usrSessions[count($usrSessions) - 1]['end'],
                                'usage' => 'Oprit'
                            ));
                        }
                        if (!empty($activeSessions)) {
                            foreach ($activeSessions as $activeSession) {
                                array_push($usrSessions, $activeSession);
                            }
                        }
                    }
                }
                $result = array_merge($result, $usrSessions);
            }
        }
        
        foreach ($result as $idx => $session) {
            $result[$idx]['duration'] = $this->_sec2hms($result[$idx]['end'] - $result[$idx]['start']);
            $result[$idx]['tStart'] = date('d.m.Y / H:i', $result[$idx]['start']);
            $result[$idx]['tEnd'] = date('H:i', $result[$idx]['end']);
        }*/
        
    }

	public function getComputerReportDataStop($filters,$user,$day)
	{
		$db = new Application_Table_dbReports();
		$dbDepartments  = new Application_Table_dbDepartments();
		$departmentDetails = $dbDepartments->getDepartmentbyId("1");
		
		$ok = false;
		
		$tempDate = explode("/",$day);
	 	$currentDay = date('w',strtotime($tempDate[1]."/".$tempDate[0]."/".$tempDate[2]));
	 	
	 	if($currentDay == 0)
	 		$currentDay = 7;
	 	$start ='day'.$currentDay."_start";
	 	$stop ='day'.$currentDay."_stop";

	 	if(empty($departmentDetails[$start]) || empty($departmentDetails[$stop]))
	 	{
	 		$departmentDetails[$start] = '00:00:00';
	 		$departmentDetails[$stop] = '23:59:59';
	 	}		
		
		$hourStart = '';
		$hourEnd = '';
		$response = $db->getComputerReportDataGridEvents($user,$day);
		//echo count($response['stoppedTime']);die();
		if(count($response['stoppedTime']) % 2 != 0 || count($response['stoppedTime']) == 0 || $filters['timeinterval'] != 'workallhours')
		{
			$temp1 = array();
			
		   		switch($filters['timeinterval']) {
	            case 'workinghours':	
			 		$hourStart = $filters['hArray']['startTime'];
				 	$hourEnd = $filters['hArray']['endTime'];
				 	$ok = true;
	               break;
				case 'workhours':
			 		$hourStart = $departmentDetails[$start];
				 	$hourEnd = $departmentDetails[$stop];
				 	$ok = true;
					break;
				case 'workoutsidehours' :
			 		$hourStart = $departmentDetails[$start];
				 	$hourEnd = $departmentDetails[$stop];	
				 	$ok = false;	
					break;
	            case 'workallhours':
			 		$hourStart = '00:00:00';
				 	$hourEnd = '23:59:59';     
				 	$ok = true;      	
	            default:
	                // Do not filter by hours
	                break;
	        }		
		 	
			if(empty($response)) return '';
			
			$first = true;
			$results = array();
			$index = 0;
			foreach($response['event'] as $item)
			{
				if($first == true){
					$element = array();
					$element['start'] = $item['StartTime'];
					$element['end'] = $item['EndTime'];
					$results[$index] = $element;
					$first = false;
				}
				else
				{
					if(strtotime($item['StartTime']) >= strtotime($results[$index]['start']) &&
						strtotime($item['StartTime']) <= strtotime($results[$index]['end'])	 ){
						
						if( strtotime($results[$index]['end']) < strtotime($item['EndTime'])){
							$results[$index]['end'] = $item['EndTime'];
						}
					}
					else
					{
						$element = array();
						$element['start'] = $item['StartTime'];
						$element['end'] = $item['EndTime'];
						
						$index++;
						$results[$index] = $element;
					}
				}
			}
			//print_r($results); die();
			$results2 = array();
			if($ok)
			{
				$element = array();
				$element['start'] = $hourStart;
				$element['end'] = $hourStart;
				$index = 0;
				$results2[$index] = $element;
				foreach($results as $item)
				{
					if( strtotime($results2[$index]['end']) < strtotime($item['start'])){
					
						if( strtotime($hourEnd) <= strtotime($item['start']) )
						{
							if($results2[$index]['start'] == $hourEnd)
							{
								unset($results2[$index]);
								$index--;
							}
							else
							{
								$results2[$index]['end'] = $hourEnd;
							}
							break;
						}
						else
						{
							$results2[$index]['end'] = $item['start'];
							$element = array();
							$element['start'] = $item['end'];
							$element['end'] = $item['end'];
							$index++;
							$results2[$index] = $element;
						}
					}else{
						if( strtotime($results2[$index]['end']) < strtotime($item['end']) )
						{
							if( strtotime($hourEnd) < strtotime($item['end']) )
							{
								unset($results2[$index]);
								$index--;
								break;
							}
							else
							{
								$results2[$index]['start'] = $item['end'];
								$results2[$index]['end'] = $item['end'];
							}
						}
					}
				}
				
				if($index >= 0)
				{
					if($hourEnd == $results2[$index]['start'] ){
						unset($results2[$index]);
						$index--;
					}
					else
					{
						if(strtotime($results2[$index]['end']) < strtotime($hourEnd)){
							$results2[$index]['end'] = $hourEnd;
						}
					}
				}
			}
			else
			{
				$element = array();
				$element['start'] = '00:00:00';
				$element['end'] = '00:00:00';
				$index = 0;
				$results2[$index] = $element;
				foreach($results as $item)
				{
					if( strtotime($results2[$index]['end']) < strtotime($item['start'])){
					
						if( strtotime($hourStart) <= strtotime($item['start']) )
						{
							if($results2[$index]['start'] == $hourStart)
							{
								unset($results2[$index]);
								$index--;
							}
							else
							{
								$results2[$index]['end'] = $hourStart;
							}
							break;
						}
						else
						{
							$results2[$index]['end'] = $item['start'];
							$element = array();
							$element['start'] = $item['end'];
							$element['end'] = $item['end'];
							$index++;
							$results2[$index] = $element;
						}
					}else{
						if( strtotime($results2[$index]['end']) < strtotime($item['end']) )
						{
							if( strtotime($hourStart) < strtotime($item['end']) )
							{
								unset($results2[$index]);
								$index--;
								break;
							}
							else
							{
								$results2[$index]['start'] = $item['end'];
								$results2[$index]['end'] = $item['end'];
							}
						}
					}
				}
				
				if($index >= 0)
				{
					if($hourStart == $results2[$index]['start'] ){
						unset($results2[$index]);
						$index--;
					}
					else
					{
						if(strtotime($results2[$index]['end']) < strtotime($hourStart)){
							$results2[$index]['end'] = $hourStart;
						}
					}
				}
				
				$element = array();
				$element['start'] = $hourEnd;
				$element['end'] = $hourEnd;
				$index++;
				$results2[$index] = $element;
				foreach($results as $item)
				{
					if( strtotime($results2[$index]['end']) < strtotime($item['start'])){
					
						if( strtotime('23:59:59') <= strtotime($item['start']) )
						{
							if($results2[$index]['start'] == '23:59:59')
							{
								unset($results2[$index]);
								$index--;
							}
							else
							{
								$results2[$index]['end'] = '23:59:59';
							}
							break;
						}
						else
						{
							$results2[$index]['end'] = $item['start'];
							$element = array();
							$element['start'] = $item['end'];
							$element['end'] = $item['end'];
							$index++;
							$results2[$index] = $element;
						}
					}else{
						if( strtotime($results2[$index]['end']) < strtotime($item['end']) )
						{
							if( strtotime('23:59:59') < strtotime($item['end']) )
							{
								unset($results2[$index]);
								$index--;
								break;
							}
							else
							{
								$results2[$index]['start'] = $item['end'];
								$results2[$index]['end'] = $item['end'];
							}
						}
					}
				}
				
				if($index >= 0)
				{
					if('23:59:59' == $results2[$index]['start'] ){
						unset($results2[$index]);
						$index--;
					}
					else
					{
						if(strtotime($results2[$index]['end']) < strtotime('23:59:59')){
							$results2[$index]['end'] = '23:59:59';
						}
					}
				}
			}
			//print_r( $results ); die();
			$result = '</br>';
			foreach($results2 as $item)
			{
				$result = $result.$item['start']." - ".$item['end']." &nbsp;</br> ";
			}
			
			if(count($results2) == 0){
				$result = "Fara opriri";
			}
			
			return $result;
		}
		else {
			$temp_result = '';
			$first = true;
			//print_r($response['stoppedTime']);die();
			for($i =0;$i < count($response['stoppedTime']) - 1 ; $i++)
			{
				if($first == true)
				{
					$temp_result = "00:00:00"." - ".$response['stoppedTime'][$i]['Time']." &nbsp;</br>";
					$first = false;
				}
				else
				{
					$temp_result.= $response['stoppedTime'][$i]['Time']." - ".$response['stoppedTime'][$i+1]['Time']." &nbsp;</br>";
					$i++;
				}
				
			}
			//echo $i;die();
			if(count($response['stoppedTime']) == 1)
				$temp_result = "00:00:00"." - ".$response['stoppedTime'][0]['Time']." &nbsp;</br>";
			$temp_result.= $response['stoppedTime'][$i]['Time']." - 23:59:59 &nbsp;</br>";

			return $temp_result;	
			
		}
		

		
		//print_r($temp_result);die();

				
		//return $user." ".$day;
	}

    
    public function getInternetReportData($options, $top = 0, $limit = 0)
    {
        $db = new Application_Table_dbReports();
		$dbRemoteClient = new Application_Table_dbIdle();
		
        $response = $db->getInternetReportDataGrid($options, $top, $limit);
		
		$timeCheck = array();
        if (empty($response)) { return array(); }
   		//print_r($response);die();
       $result = array();
	   $graphData = array();
	   $cronProgress = array();
	   $graphDataProd = array(
        	'productive' => 0,
        	'unproductive' => 0,
        	'unclassified' => 0
        );
        $totalUsageTime = 0.0;
		foreach($response['response'] as $item)
		{
               $productive = "<option value=\"productive\">Productiv</option>";
                $unproductive = "<option value=\"unproductive\">Neproductiv</option>";
                $unclassified = "<option value=\"unclassified\">Neclasificat</option>";
				$typeCat = '';
                if (!empty($item['Tip'])) {
                    if ($item['Tip'] == 'productive')
					{
						$typeCat = 'Productiva';
                        $productive = "<option value=\"productive\" selected=\"selected\">Productiv</option>";
					}
                    if ($item['Tip'] == 'unproductive')
					{
						$typeCat = 'Neproductiva';
                        $unproductive = "<option value=\"unproductive\" selected=\"selected\">Neproductiv</option>";
					}
                    if ($item['Tip'] == 'unclassified')
					{
						$typeCat = 'Neclasificata';
                        $unclassified = "<option value=\"unclassified\" selected=\"selected\">Neclasificat</option>";
					}	
				}		
	        	$select = "<select id=\"{$item['Url']}\" class=\"prodcharddata\">" .  
	                    $unclassified .
	                    $productive .
	                    $unproductive .
	                    "</select>";	
			
			$response['totalandcount'][0]['TimpTotal'] = empty($response['totalandcount'][0]['TimpTotal']) ? 1 : $response['totalandcount'][0]['TimpTotal'];
			$icon = '';
			$icon = $db->getInternetIcon($item['Url'],$options);
			$image = '';
			//print_r($icon);die();
			if(!empty($icon))
			{
				$filePath = BASE_PATH . "\\public\\img\\icons\\".$icon[0]['iconID'].".gif";
				$path = Zend_Controller_Front::getInstance()->getBaseUrl().'/img/icons/'.$icon[0]['iconID'].'.gif';
			    $success = true;
				if (!file_exists($filePath)) {
				   $image = base64_decode($icon[0]['Icon']);
				   $success = file_put_contents($filePath, $image);					
				}
				
				$image = "<img src='{$path}'/>";
			}			

			$result[$item['Row']]['icon'] = $image;									
			$result[$item['Row']]['site'] = $item['Url'];
			$result[$item['Row']]['selector'] = $select;
			$result[$item['Row']]['usageTime'] = $this->_sec2hms($item['Timp']);
			$result[$item['Row']]['usagePercent'] = number_format(($item['Timp']/$response['totalandcount'][0]['TimpTotal']) *100,2);
			
			$graphData[md5($item['Url'])] = array(
                'Hash' => md5($item['Url']),
                'Item' => $result[$item['Row']]['site'],
                'Usage' => $result[$item['Row']]['usagePercent'],				
			);		

			$graphDataProd[$item['Tip']]+= $result[$item['Row']]['usagePercent'];
			$totalUsageTime+= $result[$item['Row']]['usagePercent'];
			
		}
		$graphDataProd['total'] = $totalUsageTime;
		
		foreach($response['cron'] as $cronItem)
		{
		
			$image = '';
			//print_r($response['cron']);die();
			if(!empty($cronItem['iconID']))
			{
				$filePath = BASE_PATH . "\\public\\img\\icons\\".$cronItem['iconID'].".gif";
				$path = Zend_Controller_Front::getInstance()->getBaseUrl().'/img/icons/'.$cronItem['iconID'].'.gif';
			    $success = true;
				if (!file_exists($filePath)) {
				   $image = base64_decode($cronItem['Icon']);
				   $success = file_put_contents($filePath, $image);					
				}
				
				$image = "<img src='{$path}'/>";
			}
						
			/*if(strlen($cronItem['Url']) >30 )
            {
                $cronItem['Url'] = substr($cronItem['Url'],0,30)."...";
            }*/		    
            $cronProgress[] = array(
               	'icon' => $image,
                'computer' => $cronItem['reDomain'],
                'user' => $cronItem['reUser'],
                'process' => $cronItem['reProcess'],
                'title' => (!empty($cronItem['reWnd']) ?$cronItem['reWnd'] : ''),
                'link' => $cronItem['Url'],
                'tStart' => $cronItem['Date']." / ".$cronItem['StartHour'],
                'hEnd' => $cronItem['StopHour'],
                'duration' => $this->_sec2hms($cronItem['Time']),
            );			
		}   
   		//print_r($result);die();
         // Return data
        return array(
            'usage' => $result,
            'crons' => $cronProgress,
            'graph' => $graphData,
        	'graphProd' => $graphDataProd
        );       
        /*$ctg = new Application_Table_dbCategory();
        $classification = $ctg->getCategoryBytypes('site');
        $categs = array();
        foreach ($classification as $item) {
            $categs['name'] = $item;
        }
        
        // Usage
        $result = array();
		$verifyTime = array();
        $cronProgress = array();
        $graphData = array();
        $totalUsageTime = 0.0;
		

		$remoteClientsTemp = $dbRemoteClient->getEmployeesFromServerRemote();		
		$remoteClients = array();	
		
		foreach($remoteClientsTemp as $remoteClient)
		{
			$remoteClients[$remoteClient['rcID']] = $remoteClient;
		}
		$i= 0;
        foreach ($response as $item) {
        	
        	//if($i == 1469)
				//{	print_r($item);die();}
			//$i++;
        	//print_r($item['reWnd']);
        	$httpData = parse_url($item['rhaeURL']);
        	if($item['reWnd'] != 'null' && !empty($httpData['host'])){
	            $httpData = parse_url($item['rhaeURL']);
	            $hash = md5($httpData['host']);
				
	            $cat = $ctg->getCategoryByName($httpData['host'],'site');
				
				
	            if (!empty($cat)) {
	                $productive = "<option value=\"productive\">Productiv</option>";
	                $unproductive = "<option value=\"unproductive\">Neproductiv</option>";
	                $unclassified = "<option value=\"unclassified\">Neclasificat</option>";
	                if($cat['type'] == 'productive')
	                    $productive = "<option value=\"productive\" selected=\"selected\">Productiv</option>";
	                if ($cat['type'] == 'unproductive')
	                    $unproductive = "<option value=\"unproductive\" selected=\"selected\">Neproductiv</option>";
	                if ($cat['type'] == 'unclassified')
	                    $unclassified = "<option value=\"unclassified\" selected=\"selected\">Neclasificat</option>";			
	            }
	            if (!isset($result[$hash])) {
	                $result[$hash] = array(
	                    'site' => $httpData['host'],
	                    'selector' =>
	                        "<select id=\"{$httpData['host']}\" class=\"prodcharddata\">" .
	                        $productive .
	                        $unproductive .
	                        $unclassified .
	                        "</select>",
	                    'usageTime' => 0.0,
	                    'usagePercent' => 0.0
	                );
	            }
	            $start = $item['reStartDate']->getTimestamp();
	            $end = $item['reEndDate']->getTimestamp();
				
				$startString = (string)$start;
				$endString = (string)$end;
				
				if(!empty($timeCheck[$startString])){
					if($timeCheck[$startString] != $endString)
					{
		            	$result[$hash]['usageTime'] += ($end - $start);
						$timeCheck[$startString] =$endString;
					}
				}
				else
				{
	            	$result[$hash]['usageTime'] += ($end - $start);
					$timeCheck[$startString] =$endString;					
				}
						
				
	            $totalUsageTime += ($end - $start);
				
	            //$remotecomputer = $dbRemoteClient->getEmployeesFromServerByID($item['reComputerID']);
				$remotecomputer = $remoteClients[$item['reComputerID']];
	            $cronProgress[] = array(
	                'computer' => $remotecomputer['rcName'],
	                'user' => $item['reUser'],
	                'process' => $item['reProcess'],
	                'title' => (!empty($item['reWnd']) ?$item['reWnd'] : ''),
	                'link' => $item['rhaeURL'],
	                'tStart' => date('d.m.Y / H:i', $start),
	                'hEnd' => date('H:i', $end),
	                'duration' => $this->_sec2hms($end - $start),
	            );
            }
			
        }
		
		//echo $totalUsageTime;die();
        usort($result, array($this, '_topDesc'));
        
        foreach ($result as $key => $item) {
            $result[$key]['usagePercent'] = number_format((100.0 * $result[$key]['usageTime'] / $totalUsageTime), 2);
            $result[$key]['usageTime'] = $this->_sec2hms($result[$key]['usageTime']);
            $graphData[$key] = array(
                'Hash' => $key,
                'Item' => $result[$key]['site'],
                'Usage' => $result[$key]['usagePercent'],
            );
        }
        // Return data
        return array(
            'usage' => $result,
            'crons' => $cronProgress,
            'graph' => $graphData
        );*/
    }

    public function getInternetReportDataPage($options, $top = 0, $limit = 0,$page,$type,$numberPerPage)
    {
    	
        if($type == 'usage'){
    		$type = 'top';
    	}
    	if( $type == 'crons'){
    		$type = 'cron';
    	}
    			
        $db = new Application_Table_dbReports();
		$dbRemoteClient = new Application_Table_dbIdle();
	
        $response = $db->getInternetReportDataGrid($options, $top, $limit,$page,$type,$numberPerPage);
		
		$timeCheck = array();
        if (empty($response)) { return array(); }
   		//print_r($response);die();
       $result = array();
	   $graphData = array();
	   $cronProgress = array();
	   $cronProgress = array();
	   $graphDataProd = array(
        	'productive' => 0,
        	'unproductive' => 0,
        	'unclassified' => 0
        );
        $totalUsageTime = 0.0;
	   
	   if($type != 'cron'){
			foreach($response['response'] as $item)
			{
	               $productive = "<option value=\"productive\">Productiv</option>";
	                $unproductive = "<option value=\"unproductive\">Neproductiv</option>";
	                $unclassified = "<option value=\"unclassified\">Neclasificat</option>";
					$typeCat = '';
	                if (!empty($item['Tip'])) {
	                    if ($item['Tip'] == 'productive')
						{
							$typeCat = 'Productiva';
	                        $productive = "<option value=\"productive\" selected=\"selected\">Productiv</option>";
						}
	                    if ($item['Tip'] == 'unproductive')
						{
							$typeCat = 'Neproductiva';
	                        $unproductive = "<option value=\"unproductive\" selected=\"selected\">Neproductiv</option>";
						}
	                    if ($item['Tip'] == 'unclassified')
						{
							$typeCat = 'Neclasificata';
	                        $unclassified = "<option value=\"unclassified\" selected=\"selected\">Neclasificat</option>";
						}	
					}		
		        	$select = "<select id=\"{$item['Url']}\" class=\"prodcharddata\">" .  
		                    $unclassified .
		                    $productive .
		                    $unproductive .
		                    "</select>";
				$response['totalandcount'][0]['TimpTotal'] = empty($response['totalandcount'][0]['TimpTotal']) ? 1 : $response['totalandcount'][0]['TimpTotal'];

				
						$icon = '';
				$icon = $db->getInternetIcon($item['Url'],$options);
				$image = '';
				//print_r($icon);die();
				if(!empty($icon))
				{
					$filePath = BASE_PATH . "\\public\\img\\icons\\".$icon[0]['iconID'].".gif";
					$path = Zend_Controller_Front::getInstance()->getBaseUrl().'/img/icons/'.$icon[0]['iconID'].'.gif';
				    $success = true;
					if (!file_exists($filePath)) {
					   $image = base64_decode($icon[0]['Icon']);
					   $success = file_put_contents($filePath, $image);					
					}
					
					$image = "<img src='{$path}'/>";
				}
				$result[$item['Row']]['icon'] = $image;										
				$result[$item['Row']]['site'] = $item['Url'];
				$result[$item['Row']]['selector'] = $select;
				$result[$item['Row']]['usageTime'] = $this->_sec2hms($item['Timp']);
				$result[$item['Row']]['usagePercent'] = number_format(($item['Timp']/$response['totalandcount'][0]['TimpTotal']) *100,2);
				$result[$item['Row']]['selector_csv'] = $typeCat;				
				
				$graphData[md5($item['Url'])] = array(
	                'Hash' => md5($item['Url']),
	                'Item' => $result[$item['Row']]['site'],
	                'Usage' => $result[$item['Row']]['usagePercent'],				
				);			
				$graphDataProd[$item['Tip']]+= $result[$item['Row']]['usagePercent'];
				$totalUsageTime+= $result[$item['Row']]['usagePercent'];
					
			} 
		}
		$graphDataProd['total'] = $totalUsageTime;
		
		//print_r($graphDataProd);die();
		if($type != 'top'){
			foreach($response['cron'] as $cronItem)
			{
				$image = '';
				//print_r($icon);die();
				if(!empty($cronItem['iconID']))
				{
					$filePath = BASE_PATH . "\\public\\img\\icons\\".$cronItem['iconID'].".gif";
					$path = Zend_Controller_Front::getInstance()->getBaseUrl().'/img/icons/'.$cronItem['iconID'].'.gif';
				    $success = true;
					if (!file_exists($filePath)) {
					   $image = base64_decode($cronItem['Icon']);
					   $success = file_put_contents($filePath, $image);					
					}
					
					$image = "<img src='{$path}'/>";
				}				

                $cronProgress[] = array(
                	'icon' => $image,
	                'tStart' => $cronItem['Date']." / ".$cronItem['StartHour'],
	                'hEnd' => $cronItem['StartHour'],
	                'duration' => $this->_sec2hms($cronItem['Time']),	                
	                'computer' => $cronItem['reDomain'],
	                'user' => $cronItem['reUser'],
	                'process' => $cronItem['reProcess'],
	                'title' => (!empty($cronItem['reWnd']) ?$cronItem['reWnd'] : ''),
	                'link' => $cronItem['Url'],
                );			
			} 
		}
  
   		//print_r($result);die();
         // Return data     
        switch($type){
			case 'top':
		       return array(
		            'usage' => $result,
		       		'graphProd'=>$graphDataProd
		        );
				break;
			case 'chart':	
				return array(
		            'graph' => $graphData
		        );	
				break;
			case 'cron':	
				return array(
		            'crons' => $cronProgress
		        );	
				break;				
			case 'export':
				return array(
					'usage' => $result,
		            'graph' => $graphData
		        );		
				break;
			default:
				return array(
					'usage' => '',
		            'graph' => '',
		            'cron' => ''				
				);							
        }   
    }
    
    public function getApplicationsReportData($options, $top = 0, $limit = 0)
    {
        $db = new Application_Table_dbReports();

		$dbCategories = new Application_Table_dbCategory();
		$dbRemoteClient = new Application_Table_dbIdle();
		
		$remoteClientsTemp = $dbRemoteClient->getEmployeesFromServerRemote();		
		$remoteClients = array();	
		
		foreach($remoteClientsTemp as $remoteClient)
		{
			$remoteClients[$remoteClient['rcID']] = $remoteClient;
		}


		$notSet = array(
			'Google Chrome' => 'Google Chrome',
			'Yahoo! Messenger'=> 'Yahoo! Messenger',
			'Internet Explorer' => 'Internet Explorer',
			'Opera' => 'Opera',
			'Safari' => 'Safari',
			'Firefox' => 'Firefox'
		
		);
		
		$response = $db->getApplicationsReportDataGrid($options, $top, $limit,1);
		//$temp_icons = $db->getIcons();

		//$icons = array();
		/*foreach($temp_icons as $temp)
		{
			$icons[$temp['reProcess']] = $temp;
		}*/
	
        if (empty($response)) { return array(); }
        
       	//print_r($response);die();
        
		//$end = microtime(true);
		//echo $end-$start;
		
        // Usage
        $result = array();
        $cronProgress = array();
        $graphData = array();
        $timelineData = array();
        $totalUsageTime = 0.0;
       
        $response['totalandcount'][0]['TimpTotal'] = ($response['totalandcount'][0]['TimpTotal'] == 0 ) ? 1 : $response['totalandcount'][0]['TimpTotal'];
		foreach($response['response'] as $item)
		{	
			
			
               $productive = "<option value=\"productive\">Productiv</option>";
                $unproductive = "<option value=\"unproductive\">Neproductiv</option>";
                $unclassified = "<option value=\"unclassified\">Neclasificat</option>";
				$typeCat = '';
                if (!empty($item['Tip'])) {
                    if ($item['Tip'] == 'productive')
					{
						$typeCat = 'Productiva';
                        $productive = "<option value=\"productive\" selected=\"selected\">Productiv</option>";
					}
                    if ($item['Tip'] == 'unproductive')
					{
						$typeCat = 'Neproductiva';
                        $unproductive = "<option value=\"unproductive\" selected=\"selected\">Neproductiv</option>";
					}
                    if ($item['Tip'] == 'unclassified')
					{
						$typeCat = 'Neclasificata';
                        $unclassified = "<option value=\"unclassified\" selected=\"selected\">Neclasificat</option>";
					}	
				}		
	        	$select = "<select id=\"{$item['Process']}\" class=\"prodcharddata\">" .  
	                    $unclassified .
	                    $productive .
	                    $unproductive .
	                    "</select>";	
	        if(!empty($notSet[$item['Process']])){
	        	  $select = $typeCat;           
			}
				$icon = '';
				$item['Process'] = str_replace("'", "_", $item['Process']);
	            $icon = $db->getApplicationsIcon($item['Process'],$options);
	            //print_r($icon);die();
				$image = '';
				if(!empty($icon[$item['Process']]))
				{
					$filePath = BASE_PATH . "\\public\\img\\icons\\".$icon[$item['Process']]['iconID'].".gif";
					
					$path = Zend_Controller_Front::getInstance()->getBaseUrl().'/img/icons/'.$icon[$item['Process']]['iconID'].'.gif';
				    $success = true;
					if (!file_exists($filePath)) {
					   $image = base64_decode($icon[$item['Process']]['Icon']);
					   $success = file_put_contents($filePath, $image);					
					}
					
					$image = "<img src='{$path}'/>";
				}
							
				$result[$item['Row']]['Icon'] = $image;				
				$result[$item['Row']]['application'] = $item['Process'];
				$result[$item['Row']]['selector'] = $select;
				$result[$item['Row']]['usagePercent'] = number_format(($item['Timp']/$response['totalandcount'][0]['TimpTotal']) *100,2);
				$result[$item['Row']]['usageTime'] = $this->_sec2hms($item['Timp']);				
			
			
			$graphData[md5($item['Process'])] = array(
                'Hash' => md5($item['Process']),
                'Item' => $result[$item['Row']]['application'],
                'Usage' => $result[$item['Row']]['usagePercent'],				
			);			
			
		}
		//print_r($response['cron']);die();
		foreach($response['cron'] as $cronItem)
		{
			$image = '';
			if(!empty($cronItem['iconID']))
			{
				$filePath = BASE_PATH . "\\public\\img\\icons\\".$cronItem['iconID'].".gif";
				$path = Zend_Controller_Front::getInstance()->getBaseUrl().'/img/icons/'.$cronItem['iconID'].'.gif';
			    $success = true;
				if (!file_exists($filePath)) {
				   $image = base64_decode($cronItem['Icon']);
				   $success = file_put_contents($filePath, $image);					
				}
				
				$image = "<img src='{$path}'/>";
			}			
			
		    $image = '<img src="data:image/gif;base64,' . $cronItem['Icon'] . '" />';
            $cronProgress[] = array(
               	'Icon' => $image,
                'title' => (!empty($cronItem['reWnd']) ? ' ' . $cronItem['reWnd'] : ''),
                'process' => $cronItem['Process'],
                'computer' => $cronItem['reDomain'],
                'user' => $cronItem['reUser'],
                'dStart' => $cronItem['Date']." / ".$cronItem['StartHour'],
                'hEnd' => $cronItem['StopHour'],
                'duration' => $this->_sec2hms($cronItem['Time']),
            );				
		}

		 //echo "<pre>";print_r($result);echo "</pre>";die();
		
			
        //usort($result, array($this, '_topDesc'));
        
       /* foreach ($result as $key => $item) {
            $result[$key]['usagePercent'] = number_format((100.0 * $result[$key]['usageTime'] / $totalUsageTime), 2);
            $result[$key]['usageTime'] = $this->_sec2hms($result[$key]['usageTime']);
            $graphData[$key] = array(
                'Hash' => $key,
                'Item' => $result[$key]['application'],
                'Usage' => $result[$key]['usagePercent'],
            );
        }*/
        
        // Return data
        return array(
            'usage' => $result,
            'crons' => $cronProgress,
            'graph' => $graphData,
        );
    }


	public function getApplicationsReportDataPage($options, $top = 0, $limit = 0,$page,$type,$numberPerPage)
    {
    	
    	if($type == 'usage'){
    		$type = 'top';
    	}
    	if( $type == 'crons'){
    		$type = 'cron';
    	}

        $db = new Application_Table_dbReports();
		$dbCategories = new Application_Table_dbCategory();
		$dbRemoteClient = new Application_Table_dbIdle();

		$remoteClientsTemp = $dbRemoteClient->getEmployeesFromServerRemote();		
		$remoteClients = array();	

		foreach($remoteClientsTemp as $remoteClient)
		{
			$remoteClients[$remoteClient['rcID']] = $remoteClient;
		}


		$notSet = array(
			'Google Chrome' => 'Google Chrome',
			'Yahoo! Messenger'=> 'Yahoo! Messenger',
			'Internet Explorer' => 'Internet Explorer',
			'Opera' => 'Opera',
			'Safari' => 'Safari',
			'Fireforx' => 'Firefox'
		
		);
		//echo $type;die();
		$response = $db->getApplicationsReportDataGrid($options, $top, $limit,$page,$type,$numberPerPage);
    	/*$temp_icons = $db->getIcons();

		$icons = array();
		foreach($temp_icons as $temp)
		{
			$icons[$temp['reProcess']] = $temp;
		}	*/	
		//echo "<pre>";print_r($response);echo "</pre>";die();
        if (empty($response)) { return array(); }
        
		//$end = microtime(true);
		//echo $end-$start;
		
        // Usage
        $result = array();
		$graphData = array();
		$cronProgress = array();
		$total = 1;
		if(!empty($response['totalandcount']))
			$total = ($response['totalandcount'][0]['TimpTotal'] == 0 ) ? 1 : $response['totalandcount'][0]['TimpTotal'];
		if($type != 'cron'){
			foreach($response['response'] as $item)
			{
	               $productive = "<option value=\"productive\">Productiv</option>";
	                $unproductive = "<option value=\"unproductive\">Neproductiv</option>";
	                $unclassified = "<option value=\"unclassified\">Neclasificat</option>";
					$typeCat = '';
	                if (!empty($item['Tip'])) {
	                    if ($item['Tip'] == 'productive')
						{
							$typeCat = 'Productiva';
	                        $productive = "<option value=\"productive\" selected=\"selected\">Productiv</option>";
						}
	                    if ($item['Tip'] == 'unproductive')
						{
							$typeCat = 'Neproductiva';
	                        $unproductive = "<option value=\"unproductive\" selected=\"selected\">Neproductiv</option>";
						}
	                    if ($item['Tip'] == 'unclassified')
						{
							$typeCat = 'Neclasificata';
	                        $unclassified = "<option value=\"unclassified\" selected=\"selected\">Neclasificat</option>";
						}	
					}		
		        	$select = "<select id=\"{$item['Process']}\" class=\"prodcharddata\">" .  
		                    $unclassified .
		                    $productive .
		                    $unproductive .
		                    "</select>";
		                    
				$item['Process'] = str_replace("'", "_", $item['Process']);
	            $icon = $db->getApplicationsIcon($item['Process'],$options);
	            
	            //print_r($icon);die();
				$image = '';
				if(!empty($icon[$item['Process']]))
				{
					$filePath = BASE_PATH . "\\public\\img\\icons\\".$icon[$item['Process']]['iconID'].".gif";
					
					$path = Zend_Controller_Front::getInstance()->getBaseUrl().'/img/icons/'.$icon[$item['Process']]['iconID'].'.gif';
				    $success = true;
					if (!file_exists($filePath)) {
					   $image = base64_decode($icon[$item['Process']]['Icon']);
					   $success = file_put_contents($filePath, $image);					
					}
					
					$image = "<img src='{$path}'/>";
				}
				$result[$item['Row']]['Icon'] = $image;												
				$result[$item['Row']]['application'] = $item['Process'];
				$result[$item['Row']]['selector'] = $select;
				$result[$item['Row']]['usagePercent'] = number_format(($item['Timp']/$total) *100,2);			
				$result[$item['Row']]['usageTime'] = $this->_sec2hms($item['Timp']);
				$result[$item['Row']]['selector_csv'] = $typeCat;
	
				$graphData[md5($item['Process'])] = array(
	                'Hash' => md5($item['Process']),
	                'Item' => $result[$item['Row']]['application'],
	                'Usage' => $result[$item['Row']]['usagePercent'],				
				);			
				
			}
		}
		
		if($type != 'top'){
			foreach($response['cron'] as $cronItem)
			{
				$image = '';
				if(!empty($cronItem['iconID']))
				{
					$filePath = BASE_PATH . "\\public\\img\\icons\\".$cronItem['iconID'].".gif";
					$path = Zend_Controller_Front::getInstance()->getBaseUrl().'/img/icons/'.$cronItem['iconID'].'.gif';
				    $success = true;
					if (!file_exists($filePath)) {
					   $image = base64_decode($cronItem['Icon']);
					   $success = file_put_contents($filePath, $image);					
					}
					
					$image = "<img src='{$path}'/>";
				}				

                $cronProgress[] = array(
                	'Icon' => $image,
                	'process' => $cronItem['Process'],
                    'title' => (!empty($cronItem['reWnd']) ? ' ' . $cronItem['reWnd'] : ''),
                    'computer' => $cronItem['reDomain'],
                    'user' => $cronItem['reUser'],
                    'dStart' => $cronItem['Date']." / ".$cronItem['StartHour'],
                    'hEnd' => $cronItem['StopHour'],
                    'duration' => $this->_sec2hms($cronItem['Time']),
                );			
			}
		}

		// echo "<pre>";print_r($cronProgress);echo "</pre>";die();
        
        // Return data
        
        switch($type){
			case 'top':
		       return array(
		            'usage' => $result
		        );
				break;
			case 'chart':	
				return array(
		            'graph' => $graphData
		        );	
				break;
			case 'cron':	
				return array(
		            'crons' => $cronProgress
		        );	
				break;				
			case 'export':
				return array(
					'top' => $result,
					'cron' =>$cronProgress,
		            'graph' => $graphData
		        );		
				break;
			default:
				return array(
					'usage' => '',
		            'graph' => '',
		            'cron' => ''				
				);							
        }
		
    }
        
    
    public function getFilesReportData($options, $top = 0, $limit = 0)
    {
        $db = new Application_Table_dbReports();
        $dbRemoteClient = new Application_Table_dbIdle();		
        $response = $db->getFilesReportDataGrid($options, $top, $limit);
        if (empty($response)) { return array(); }
		
        // Extensions
        
        /*$extensions = empty($options['extensions']) ? array() : explode(',', $options['extensions']);
		
        if (!empty($extensions)) {
            foreach ($extensions as $index => $ext) {
                $extensions[$index] = trim($ext);
            }
        }
		
		$remoteClientsTemp = $dbRemoteClient->getEmployeesFromServerRemote();		
		$remoteClients = array();	
		
		foreach($remoteClientsTemp as $remoteClient)
		{
			$remoteClients[$remoteClient['rcID']] = $remoteClient;
		}*/		
        
        // Usage
        $result = array();
        foreach ($response['response'] as $item) {
            $fileRead = '-';
            $fileWrite = '-';
            $fileName = '';
            $event = '';
            switch(trim($item['rfaeEventType'])) {
                case 'WriteFile':
                    $event = 'scriere';
                    $fileWrite = $item['rfaeFileName'];
                    $fileName = pathinfo($fileWrite, PATHINFO_BASENAME);
                    break;
                case 'DeleteFile':
                    $event = 'stergere';
                    $fileWrite = $item['rfaeFileName'];
                    $fileName = pathinfo($fileWrite, PATHINFO_BASENAME);
                    break;
                case 'CopyFile':
                    $event = 'copiere';
                    $fileRead = $item['rfaeFileName'];
                    $fileWrite = $item['rfaeDestName'];
                    $fileName = pathinfo($fileRead, PATHINFO_BASENAME);
                    break;
                case 'ReadFile':
                default;
                    $event = 'citire';
                    $fileRead = $item['rfaeFileName'];
                    $fileName = pathinfo($fileRead, PATHINFO_BASENAME);
                    break;
            }
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);

           // if (empty($extensions) || in_array($ext, $extensions)) {
            $result[] = array(
                'computer' => $item['reDomain'],
                'user' => $item['reUser'],
                'fileName' => $fileName,
                'operation' => $event,
                'tOperation' => $item['Date']." / ".$item['StartHour'],
                'readPath' => $fileRead,
                'writePath' => $fileWrite
            );
           // }
        }
        // Return data
        return array(
            'usage' => $result,
        );
    }

    public function getFilesReportDataPage($options, $top = 0, $limit = 0,$page,$type,$numberPerPage)
    {
    	
        if($type == 'usage'){
    		$type = 'top';
    	}
    	
    	    	
        $db = new Application_Table_dbReports();
        $dbRemoteClient = new Application_Table_dbIdle();		
        $response = $db->getFilesReportDataGrid($options, $top, $limit,$page,$type,$numberPerPage);
        if (empty($response)) { return array(); }
		
        // Extensions
        
        /*$extensions = empty($options['extensions']) ? array() : explode(',', $options['extensions']);
		
        if (!empty($extensions)) {
            foreach ($extensions as $index => $ext) {
                $extensions[$index] = trim($ext);
            }
        }
		
		*/		
        
        // Usage
        $result = array();
        foreach ($response['response'] as $item) {
            $fileRead = '-';
            $fileWrite = '-';
            $fileName = '';
            $event = '';
            switch(trim($item['rfaeEventType'])) {
                case 'WriteFile':
                    $event = 'scriere';
                    $fileWrite = $item['rfaeFileName'];
                    $fileName = pathinfo($fileWrite, PATHINFO_BASENAME);
                    break;
                case 'DeleteFile':
                    $event = 'stergere';
                    $fileWrite = $item['rfaeFileName'];
                    $fileName = pathinfo($fileWrite, PATHINFO_BASENAME);
                    break;
                case 'CopyFile':
                    $event = 'copiere';
                    $fileRead = $item['rfaeFileName'];
                    $fileWrite = $item['rfaeDestName'];
                    $fileName = pathinfo($fileRead, PATHINFO_BASENAME);
                    break;
                case 'ReadFile':
                default;
                    $event = 'citire';
                    $fileRead = $item['rfaeFileName'];
                    $fileName = pathinfo($fileRead, PATHINFO_BASENAME);
                    break;
            }
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);

           // if (empty($extensions) || in_array($ext, $extensions)) {
            $result[] = array(
                'computer' => $item['reDomain'],
                'user' => $item['reUser'],
                'fileName' => $fileName,
                'operation' => $event,
                'tOperation' => $item['Date']." / ".$item['StartHour'],
                'readPath' => $fileRead,
                'writePath' => $fileWrite
            );
           // }
        }
        // Return data
        return array(
            'usage' => $result,
        );
    }
    
    public function getTopReportData($options, $top = 0, $limit = 0,$page = 1,$type = 'all',$numberPerPage = 100)
    {
    	
		
        $db = new Application_Table_dbReports();
		$ud = new Application_Table_dbUserToDepartments();
		$dbCategories = new Application_Table_dbCategory();
		$dbDepartments = new Application_Table_dbDepartments();
        $response = $db->getTopReportDataGrid($options, $top, $limit,$page,$type,$numberPerPage);
        if (empty($response)) { return array(); }


		//print_r($response);die();
		$result = array();
		$graphData = array();
		$unproductive = array();
		$total = array();
		foreach($response['unproductive'] as $item){
			if($item['Timp']<0)
				$item['Timp']*=-1;
			$unproductive[$item['reUser']] = $item['Timp'];
		}
		
		foreach($response['total'] as $item){
			$total[$item['reUser']] = $item['Timp'];
		}	

		foreach($response['productive'] as $item){
			
			if(empty($options['dateIs']))
				$total_break = $this->_getNumberOfWorkingDays($item['reUser'],$options['dateStart'],$options['dateEnd']);
			else 
				$total_break = $this->_getNumberOfWorkingDays($item['reUser'],$options['dateIs'],$options['dateIs']);
			
			if(!empty($options['users']))
				if( !in_array($item['reUser'],$options['users']) ){
					continue;
				}
			if(empty($unproductive[$item['reUser']]))
				$unproductive[$item['reUser']] = 0;
			if(empty($total[$item['reUser']]))
				$total[$item['reUser']] = 0;
			if(empty($item['break_lenght']))
			{
				$department = $dbDepartments->getDepartmentbyId("1");
				$item['break_lenght'] = $department['break_lenght'];
			}
			$break = 0;
			$x = explode(":",$item['break_lenght']);
			if(!empty($x[0]) && !empty($x[1]) && !empty($x[2])){
	        	$break += intval($x[0])*3600 + intval($x[1]) *60 + intval($x[2]);
	        	$break = $break * $total_break;
			}
			
			$prod_hour = number_format(intval($item['Timp'])/3600,2);
			
			
			$prodPercent = ($item['Timp'] * 100)/((($total[$item['reUser']] - $break) == 0) ? 1 : ($total[$item['reUser']] - $break));
			$unprodPercent = ($unproductive[$item['reUser']] * 100)/((($total[$item['reUser']] - $break) == 0) ? 1 : ($total[$item['reUser']] - $break));
			if(empty($result[$item['reUser']]))
			{
				if($total[$item['reUser']] == 0){
					$unproductive[$item['reUser']] = 0;
					$unprodPercent = 0;
				}
				if($type != 'export'){
					$result[$item['reUser']] = array(
		                    'user' => $item['reUser'],
		                    'name' => $item['last_name']." ".$item['first_name'],
				            'total' => $total[$item['reUser']],        
		                    'total_table' => number_format(intval($total[$item['reUser']])/3600,2),
		                    'breaks' => $break,
		                    'breaks_table' => ($break != 0) ? number_format($break/3600,2) : 0,
		                    'prod' => $prod_hour,
		                    'idle' => number_format(intval($unproductive[$item['reUser']])/3600,2),
		                    'prod_sec' => $item['Timp'],
		                    'idle_sec' => $unproductive[$item['reUser']],
		                    'prodpercent' => number_format($prodPercent,2),
		                    'idlepercent' => number_format($unprodPercent,2)					
						
					);
				}
				else
				{
					$result[$item['reUser']] = array(
		                    'user' => $item['reUser'],
		                    'name' => $item['last_name']." ".$item['first_name'],  
		                    'total_table' => number_format(intval($total[$item['reUser']])/3600,2),
		                    'breaks_table' => ($break != 0) ? $item['break_lenght'] : 0,
		                    'prod' => $prod_hour,
		                    'idle' => number_format(intval($unproductive[$item['reUser']])/3600,2),
		                    'prodpercent' => number_format($prodPercent,2),
		                    'idlepercent' => number_format($unprodPercent,2)					
						
					);					
				}
			}
			
			if(empty($graphData[$item['reUser']]))
			{
				$graphData[$item['reUser']] = array(
	                    'user' => $item['reUser'],
	                    'total' => number_format($total[$item['reUser']]/3600,2),
	                    'breaks' => number_format($break/3600,2),
	                    'prod_sec' => number_format($item['Timp']/3600,2),
	                    'idle_sec' => number_format($unproductive[$item['reUser']]/3600,2)	,				
					
				);
			}			
			
		}
		
        // Return data
        return array(
            'usage' => $result,
            'graph' => $graphData,
        );		
		
		
		/*
        $temp = array();
        foreach ($response as $item) {
        	if(!empty($item['reProcess']))
        		$category = $dbCategories->getAllCategoriesByName($item['reProcess']);
			else
				$category = array(
					'type' => 'asd'
			);
        	//print_r($category);die();
            $tStart = $item['reStartDate']->getTimestamp();
            $tEnd = $item['reEndDate']->getTimestamp();
            $cDate = date('d.m.Y', $tStart);
            if (!isset($temp[$item['reUser']])) {
                $temp[$item['reUser']] = array();
            }
            if (!isset($temp[$item['reUser']][$cDate])) {
                $temp[$item['reUser']][$cDate] = array(
                    'total'    => 0.0,
                    'idle'     => 0.0,
                    'productive' => 0.0,
                    'unproductive' => 0.0,
                );
            }
			
			if(!empty($category))
			{
				if($category['type'] == 'productive')
					 $temp[$item['reUser']][$cDate]['productive'] += ($tEnd - $tStart);
				if($category['type'] == 'unproductive')
					 $temp[$item['reUser']][$cDate]['unproductive'] += ($tEnd - $tStart);	
			}
				 			 
            $temp[$item['reUser']][$cDate]['total'] += ($tEnd - $tStart);
            if (!empty($item['rieID'])) {
                $iStart = $item['rieStart']->getTimestamp();
                $iEnd = $item['rieEnd']->getTimestamp();
                $temp[$item['reUser']][$cDate]['idle'] += ($iEnd - $iStart);
            }
        }
		
		//print_r($temp);die();
		$allUsersTemp = $ud->getAllUsersFromDepartments();
		
		$allUsers = array();
		foreach($allUsersTemp as $localUser)
		{
			$allUsers[$localUser['name']] = $localUser;
		}        
		
		
		
        // Usage
        $result = array();
        $graphData = array();
        foreach ($temp as $name => $dateItems) {
			
        	//$user_exists = $ud->getUserFromDepartmentsByName($name);
        	
			//print_r($user_exists);die();
			if(!empty($allUsers[$name])){
	            if (!isset($result[$name])) {
					$temp_user = $allUsers[$name];
					//print_r($temp_user);die();
					$var = '';
					if(!empty($temp_user['last_name']))
						$var = $temp_user['last_name'].' ';
					if(!empty($temp_user['first_name']))
						$var = $var.$temp_user['first_name'];
						            	
	                $result[$name] = array(
	                    'user' => $name,
	                    'name' => $var,
	                    'total' => 0,
	                    'total_table' => 0,
	                    'breaks' => 0,
	                    'breaks_table' => 0,
	                    'prod' => 0.0,
	                    'idle' => 0.0,
	                    'prod_sec' => 0.0,
	                    'idle_sec' => 0.0,
	                    'prodpercent' => 0.0,
	                    'idlepercent' => 0.0
	                );
	            }
				
	            foreach ($dateItems as $day => $values) {
	            	//print_r($dateItems);die();
	                $dt = date_parse_from_format('d.m.Y', $day);
	                $dt = mktime(0, 0, 0, $dt['month'], $dt['day'], $dt['year']);
	                if (!in_array(date('w', $dt), array(0,6))) {

						//print_r($x);die();
	                    $result[$name]['total'] += $values['total'];
						$result[$name]['total_table'] = $this->_sec2hms($result[$name]['total']);
						
	                }
					
	                $result[$name]['prod'] += intval($values['productive']);
	                $result[$name]['idle'] += intval($values['unproductive']);
					
	            }

				$x = explode(":",$temp_user['break_lenght']);
				if(!empty($x[0]) && !empty($x[1]) && !empty($x[2]))
                	$result[$name]['breaks'] += intval($x[0])*3600 + intval($x[1]) *60 + intval($x[2]);
				else
					$result[$name]['breaks'] += 0;
					
				$result[$name]['breaks_table'] = $temp_user['break_lenght'];


				
				//print_r($result);die();
		  	 }

        }
		//print_r($result);die();
        foreach ($result as $name => $vals) {
        	
            $time = (intval($result[$name]['total']) - intval($result[$name]['breaks']));
			//print_r($time);die();
        	$prod = number_format(intval(($result[$name]['prod']) * 100 )/ $time, 2);
			//print_r($prod);die();
            $unprod = number_format((intval($result[$name]['idle']) * 100 )/ $time, 2);
			//print_r($time);die();
			//print_r(intval($result[$name]['prod'])*100/$time);die();
            $result[$name]['prod'] = number_format(intval($result[$name]['prod']) / 3600,3);
            $result[$name]['idle'] = number_format(intval($result[$name]['idle']) / 3600,3);
			$result[$name]['prod_sec'] = $result[$name]['prod']*3600;
			$result[$name]['idle_sec'] = $result[$name]['idle']*3600;
            $result[$name]['prodpercent'] = ($time != 0) ? intval($prod) : 0;
            $result[$name]['idlepercent'] = ($time != 0) ? intval($unprod) : 0;
			
			//print_r($result);die();
        }
		//print_r($result);die();
        $graphData = $result;
        */

    }
    
    public function getActivityReportData($options, $top = 0, $limit = 0,$page,$numberPerPage)
    {
    	    	
        $db = new Application_Table_dbReports();
		$response = array();
		
        $response = $db->getActivityReportDataGrid($options, $top, $limit,$page,$numberPerPage);
					
        if (empty($response)) { return array(
						            'usage' => array(),
						            'graph' => array()
						        ); }
		//print_r($response);die();
		$result = array();
		$graphData = array();
		$graphIntervals = array();
		$i = 1;
		foreach($response['response'] as $item){

			$temp_result = $this->getActivityReportDataActive($item['Utilizator'],$item['Data']);
			
			$graphIntervals[$item['Data']] = array();
			$temp_end = "00:00:00";
			$first = true;
			foreach($temp_result as $time)
			{
				if($first == true){
					if($time['start'] == '00:00:00'){
						$graphIntervals[$item['Data']][] = '0';
						$graphIntervals[$item['Data']][] = strtotime($time['end']) - strtotime($time['start']);
						$temp_end = $time['end'];
					}
					else 
					{
						//print_r(strtotime($temp_end)." - ".strtotime($time['start']) );die();
						$graphIntervals[$item['Data']][] = strtotime($time['start']) - strtotime($temp_end);
						$graphIntervals[$item['Data']][] = strtotime($time['end']) - strtotime($time['start']);
						$temp_end = $time['end'];
					}
				}
				else 
				{
						$graphIntervals[$item['Data']][] = strtotime($time['start']) - strtotime($temp_end);
						$graphIntervals[$item['Data']][] = strtotime($time['end']) - strtotime($time['start']);
						$temp_end = $time['end'];		
						$first = false;			
				}	
					
			}
			if($temp_end != '00:00:00')
				$graphIntervals[$item['Data']][] = strtotime('23:59:59') - strtotime($temp_end);
			
			//print_r($temp_result);
			//print_r($graphIntervals);die();
			
			$result[] = array(
                    'user' => $item['Utilizator'],
                    'day'  => $item['Data'],
                    'start' => $item['First'],
                    'end' => $item['Last'],
                    'total' => $this->_sec2hms($item['TimpTotal']),
                    'active' => $this->_sec2hms($item['TimpTotal'] - $item['TimpInactiv']),
                    'idle' => $this->_sec2hms($item['TimpInactiv']),
                    'percent' => number_format(($item['TimpTotal'] - $item['TimpInactiv']) * 100 / ($item['TimpTotal'] == 0 ? 1 : $item['TimpTotal']), 2),
                    'idleList' => ''	
				
			);
			
		}
		
		foreach($response['graph'] as $item){
			$graphData[] = array(
				'date'  => $item['Data'],	
				'active' => number_format(($item['TimpTotal'] - $item['TimpInactiv'])/3600,2),
				'idle' => number_format($item['TimpInactiv']/3600,2)
			);			
			
		}
		//print_r($graphIntervals);die();
        // Return data
        return array(
            'usage' => $result,
            'graph' => $graphData,
        	'graphIntervals' => $graphIntervals
        );		
		
        /*$temp = array();
        foreach ($response as $item) {
            $tStart = $item['reStartDate']->getTimestamp();
            $tEnd = $item['reEndDate']->getTimestamp();
            $cDate = date('d.m.Y', $tStart);
            if (!isset($temp[$item['reUser']])) {
                $temp[$item['reUser']] = array();
            }
            if (!isset($temp[$item['reUser']][$cDate])) {
                $temp[$item['reUser']][$cDate] = array(
                    'start'    => date('H:i', $tStart),
                    'end'      => date('H:i', $tEnd),
                    'total'    => 0.0,
                    'idle'     => 0.0,
                    'listIdle' => array()
                );
            }
            $temp[$item['reUser']][$cDate]['end'] = date('H:i', $tEnd);
            $temp[$item['reUser']][$cDate]['total'] += ($tEnd - $tStart);
            if (!empty($item['rieID'])) {
                $iStart = $item['rieStart']->getTimestamp();
                $iEnd = $item['rieEnd']->getTimestamp();
                $temp[$item['reUser']][$cDate]['idle'] += ($iEnd - $iStart);
                $temp[$item['reUser']][$cDate]['listIdle'][] = date('H:i:s', $iStart) . ' - ' . date('H:i:s', $iEnd);
            }
        }
        
        // Usage
        $result = array();
        $graphData = array();
        if (!empty($temp)) foreach ($temp as $user => $days) {
            if (!isset($graphData[$user])) {
                $graphData[$user] = array(
                    'name' => $user,
                    'dates' => array()
                );
            }
            if (!empty($temp)) foreach ($days as $day => $events) {
                $result[] = array(
                    'user' => $user,
                    'day'  => $day,
                    'start' => $events['start'],
                    'end' => $events['end'],
                    'total' => $this->_sec2hms($events['total']),
                    'active' => $this->_sec2hms($events['total'] - $events['idle']),
                    'idle' => $this->_sec2hms($events['idle']),
                    'percent' => number_format(($events['total'] - $events['idle']) * 100 / $events['total'], 2),
                    'idleList' => $events['listIdle']
                );
                if (!isset($graphData[$user]['dates'][$day])) {
                    $graphData[$user]['dates'][$day] = array(
                        'totalLabel' => $this->_sec2hms($events['total']),
                        'activeLabel' => $this->_sec2hms($events['idle']),
                        'idleLabel' => $this->_sec2hms($events['idle']),
                        'total' => $events['total'],
                        'active' => ($events['total'] - $events['idle']),
                        'idle' => $events['idle'],
                    );
                }
            }
        }*/
        
    }

	public function getActivityReportDataPause($user,$day)
	{
		
        $db = new Application_Table_dbReports();
		
		$response = array();
		$response2 = array();
		
        $response = $db->getActivityReportDataGridPause($user,$day);
		
		if(empty($response)) return '';
				
		
		//print_r($resultsActive);die();
		
		$first = true;
		$results = array();
		$index = 0;
		foreach($response as $item)
		{
			if($first == true){
				$element = array();
				$element['start'] = $item['StartTime'];
				$element['end'] = $item['EndTime'];
				
				$results[$index] = $element;
				$first = false;
			}
			else
			{
				if(strtotime($item['StartTime']) >= strtotime($results[$index]['start']) &&
					strtotime($item['StartTime']) <= strtotime($results[$index]['end'])	 ){
					
					if( strtotime($results[$index]['end']) < strtotime($item['EndTime'])){
						$results[$index]['end'] = $item['EndTime'];
					}
				}
				else
				{
					$element = array();
					$element['start'] = $item['StartTime'];
					$element['end'] = $item['EndTime'];
					
					$index++;
					$results[$index] = $element;
				}
			}
		}
		
		$result = '</br>';
		foreach($results as $item)
		{
			$result = $result.$item['start']." - ".$item['end']."</br>";
		}
		
		return $result;	
	}
	
	public function getActivityReportDataActive($user,$day)
	{
		
        $db = new Application_Table_dbReports();
		
		$response2 = array();
		
        $response2 = $db->getActivityReportDataGridUpTime($user,$day);	
		
		if(empty($response2)) return array();
		
		$first = true;
		$resultsActive = array();
		$index = 0;
		foreach($response2 as $item)
		{
			if($first == true){
				$element = array();
				$element['start'] = $item['StartTime'];
				$element['end'] = $item['EndTime'];
				
				$resultsActive[$index] = $element;
				$first = false;
			}
			else
			{
				if(strtotime($item['StartTime']) >= strtotime($resultsActive[$index]['start']) &&
					strtotime($item['StartTime']) <= strtotime($resultsActive[$index]['end'])	 ){
					
					if( strtotime($resultsActive[$index]['end']) < strtotime($item['EndTime'])){
						$resultsActive[$index]['end'] = $item['EndTime'];
					}
				}
				else
				{
					$element = array();
					$element['start'] = $item['StartTime'];
					$element['end'] = $item['EndTime'];
					
					$index++;
					$resultsActive[$index] = $element;
				}
			}
		}		
		//print_r($resultsActive);die();
		
		//print_r($result);
		return $resultsActive;	
	}	

    
    public function getRoiReportData($options, $top = 0, $limit = 0,$page,$type = 'top',$numberPerPage)
    {
    	//echo $page." ".$numberPerPage;die();
        $db = new Application_Table_dbReports();
		$dbCategories = new Application_Table_dbCategory();
		$ud = new Application_Table_dbUserToDepartments();
		
        $response = $db->getRoiReportDataGrid($options, $top, $limit,$page,$type = 'top',$numberPerPage);
		
        if (empty($response)) { return array();  }
		//print_r($response);die();
        $dbDepartments = new Application_Table_dbDepartments();
		
		$allDepartmentsTemp = $dbDepartments->getAllDepartments();
		
		$allDepartments = array();
		foreach($allDepartmentsTemp as $localDepartment)
		{
			$allDepartments[$localDepartment['id']] = $localDepartment;
		}

		
		$result = array();
		$graphData = array(
			'totalProd' => 0.0,
			'totalCfCM' => 0.0
		);
		
		//print_r($options);die();
		foreach($response['response'] as $item)
		{
			if(empty($options['dateIs']))
				$total_break = $this->_getNumberOfWorkingDays($item['reUser'],$options['dateStart'],$options['dateEnd']);
			else 
				$total_break = $this->_getNumberOfWorkingDays($item['reUser'],$options['dateIs'],$options['dateIs']);			
			
			$temp_user = $ud->getUserFromDepartmentsByName($item['reUser']);
			//print_r($temp_user);die();	
				
			if(empty($temp_user['break_lenght']))
			{
				$department = $dbDepartments->getDepartmentbyId("1");
				$temp_user['break_lenght'] = $department['break_lenght'];
			}
			$break = 0;
			$x = explode(":",$temp_user['break_lenght']);
			if(!empty($x[0]) && !empty($x[1]) && !empty($x[2])){
	        	$break += intval($x[0])*3600 + intval($x[1]) *60 + intval($x[2]);
	        	$break = $break * $total_break;
			}			
			
			if(empty($options['dateIs']))
				$hoursCfCM_temp = $this->_parseDaysRoiReport($item['reUser'],$options['dateStart'],$options['dateEnd']);
			else 
				$hoursCfCM_temp = $this->_parseDaysRoiReport($item['reUser'],$options['dateIs'],$options['dateIs']);
			
			$hoursCfCM = number_format(($hoursCfCM_temp['hoursCfCM']-$break)/3600,2);

							
			//echo $break;die();
			$prodHours = number_format($item['Timp']/3600,2);
			$cost = !empty($item['cost_per_hour']) ? $item['cost_per_hour'] : $hoursCfCM_temp['cost_per_hour'];
			$prodValue = number_format($cost*$prodHours,2);
			$workCost = number_format(intval($cost)*intval($hoursCfCM),2);
			
				$dept = $allDepartments[$item['department']];

				if(!empty($dept)){
                    $nameDepartment = $dept['name']."/";
                    while($dept['parent'] != 0) {
                        //$dept = $dbDepartments->getDepartmentbyId($dept['parent']);
                        $dept = $allDepartments[$dept['parent']];
                        $nameDepartment = $nameDepartment.$dept['name']."/";
                    }	
                    $nameDepartment[strlen($nameDepartment)-1] = '';
				}
				else
					$nameDepartment = '';			
			
			
			$result[$item['reUser']] = array(
	            'dept' => $nameDepartment,
	            'user' => $item['reUser'],
	            'name' => $item['last_name']." ".$item['first_name'],
	            'hourCost' => $cost, 
	            'curency' => $item['payment'],
				'hoursCfCM' =>  $hoursCfCM,
	            'prodHours' => $prodHours,
	            'workCost' => $workCost,
	            'prodValue' => $prodValue
			);
			

			//Graphic
			
            $graphData['totalProd'] += floatval($prodHours);
            $graphData['totalCfCM'] += floatval($hoursCfCM);			

				
		}
				
        // Return data
        return array(
            'usage' => $result,
            'graph' => $graphData,
        );		
		//print_r($result);die();
		
        /*$ud = new Application_Table_dbUserToDepartments();
        $users = $ud->getAllUsersFromDepartments();
        $userItems = array();
        foreach ($users as $item) {
            $userItems[$item['name']] = $item;
        }
       // print_r($userItems);die();
        $dp = new Application_Table_dbDepartments();
        $dpts = $dp->getAllDepartments();
        $departments = array();
        foreach ($dpts as $item) {
            $departments[$item['id']] = array(
                'name' => $item['name'],
                'parent' => $item['parent']
            );
        }
		
		$allUsersTemp = $ud->getAllUsersFromDepartments();
		
		$allUsers = array();
		foreach($allUsersTemp as $localUser)
		{
			$allUsers[$localUser['name']] = $localUser;
		}
		
		
		
        $temp = array();
        foreach ($response as $item) {
        	
        	if(!empty($item['reProcess']))
				$category = $dbCategories->getAllCategoriesByName($item['reProcess']);
			else
				$category = array(
					'type' => ' '
				
				);
            
            if (!isset($temp[$item['reUser']])) {
            	if(isset($userItems[$item['reUser']]))
            		$temp_dept = $userItems[$item['reUser']];   
				else
					$temp_dept = array(
						'department' => ' '
					);
				//print_r($item);die();            
                $userItems_local = isset($userItems[$item['reUser']])
                        ? $this->_parseUDData($userItems[$item['reUser']], $item['reUser'],$temp_dept['department'])
                        : $this->_parseUDData(null, $item['reUser'],$temp_dept['department']);
						
				
				//$temp_user = $ud->getUserFromDepartmentsByName($item['reUser']);
				$temp_user = $allUsers[$item['reUser']];
				$var = '';
				if(!empty($temp_user['last_name']))
					$var = $temp_user['last_name'].' ';
				if(!empty($temp_user['first_name']))
					$var = $var.$temp_user['first_name'];
							
										
				//print_r($userItems);die();
				$no_dept= $this->_deptParse($departments, $userItems_local['department']);
				if(empty($no_dept))
					$no_dept = '<span style="color:red">No department assigned</span>';
                $temp[$item['reUser']] = array(
                    'dept' => ($userItems_local['department'] == 'no_department')?'<span style="color:red">No data set for this user</span>':$no_dept,
                    'user' => $item['reUser'],
                    'name' => $var,
                    'hourCost' => $userItems_local['cost_per_hour'],
                    'curency' => $userItems_local['payment'],
                    'hoursCfCM' => $userItems_local['hoursCfCM'],
                    'prodHours' => 0.0,
                    'workCost' => $userItems_local['workCost'],
                    'prodValue' => 0.0,
                );
            }				
			//print_r($category)
			if(!empty($category['type'])){
				if($category['type'] == 'productive'){
		            $tStart = $item['reStartDate']->getTimestamp();
		            $tEnd = $item['reEndDate']->getTimestamp();
					$cDate = date('d.m.Y', $tStart);
					//print_r($item);die();
		            if (empty($item['rieID'])) {
		                $temp[$item['reUser']]['prodHours'] += ($tEnd - $tStart);
		                $temp[$item['reUser']]['prodValue'] += (($tEnd - $tStart) * $temp[$item['reUser']]['hourCost']);
		            }
		        }
	        }
        }
        
		
		//print_r($temp);die();
        // Usage
        $result = array();
        $graphData = array(
            'totalProd' => 0.0,
            'totalCfCM' => 0.0
        );
		
        if (!empty($temp)) foreach ($temp as $user => $item) {
            $item['prodHours'] = number_format($item['prodHours'] / 3600, 2);
            $item['prodValue'] = number_format($item['prodValue'] / 3600, 2);
            $result[$user] = $item;
            $graphData['totalProd'] += floatval($item['prodHours']);
            $graphData['totalCfCM'] += floatval($item['hoursCfCM']);
        }*/
        
    }
    
    public function getPerformanceReportData($options, $top = 0, $limit = 0)
    {
    	//print_r($options);die();
        $db = new Application_Table_dbReports();
        $response = $db->getPerformanceReportDataGrid($options, $top, $limit);
        if (empty($response)) { return array(); }
        
        $result = array();
		//print_r($response);die();
		
        foreach($response as $item)
        {
        	
        	if(empty($item['deviation']))
        		$item['number'] = $this->_sec2hms($item['number']);
        	$result[] = $item;
        }
		//print_r($result);die();
        
        
        // Return data
        return array(
            'usage' => $result,
        );
    }
    
    public function getPunchInReportData($options = array(), $top = 0, $limit = 0,$page = 1,$numberPerPage = 100)
    {
    	$result = array();
		//echo $page." ".$numberPerPage;die();
		$ok = 0;

		$ud = new Application_Table_dbUserToDepartments();
        $users = $ud->getAllUsersFromDepartments();
        $userItems = array();
        foreach ($users as $item) {
            $userItems[$item['name']] = $item;
        }

		$months = array(
			'01' =>  31,
			'02' =>  29,			
			'03' =>  31,	
			'04' =>  30,
			'05' =>  31,
			'06' =>  30,
			'07' =>  31,
			'08' =>  31,
			'09' =>  30,
			'10' =>  31,
			'11' =>  30,
			'12' =>  31,																				
		);
		
        $dates = array(
        	'department' => '',
        	'winName' => '',
        	'complete_name' => '',
        	'cnp' => '',
            'day01' => '0.0',
            'day02' => '0.0',
            'day03' => '0.0',
            'day04' => '0.0',
            'day05' => '0.0',
            'day06' => '0.0',
            'day07' => '0.0',
            'day08' => '0.0',
            'day09' => '0.0',
            'day10' => '0.0',
            'day11' => '0.0',
            'day12' => '0.0',
            'day13' => '0.0',
            'day14' => '0.0',
            'day15' => '0.0',
            'day16' => '0.0',
            'day17' => '0.0',
            'day18' => '0.0',
            'day19' => '0.0',
            'day20' => '0.0',
            'day21' => '0.0',
            'day22' => '0.0',
            'day23' => '0.0',
            'day24' => '0.0',
            'day25' => '0.0',
            'day26' => '0.0',
            'day27' => '0.0',
            'day28' => '0.0',
            'day29' => '0.0',
            'day30' => '0.0',
            'day31' => '0.0',
            'total_hours' => "00:00:00",        
            'zilelucrate' => 0,
            'zilenelucrate' => $months[$options['month']]
        );
        $db = new Application_Table_dbReports();
        $dbUsers = new Application_Table_dbUserToDepartments();
        $dbDepartments = new Application_Table_dbDepartments();
		
		$allDepartmentsTemp = $dbDepartments->getAllDepartments();
		
		$allDepartments = array();
		foreach($allDepartmentsTemp as $localDepartment)
		{
			$allDepartments[$localDepartment['id']] = $localDepartment;
		}  		
		
	//	print_r($allDepartments);die();
        $response = $db->getPunchInReportDataGrid($options, $top, $limit);
		
		//print_r($response);die();
		
        if (empty($response)) { return array(); }
		
        $temp = array();
		$result = array();

		foreach($response['usage'] as $item)
		{
			if(empty($result[$item['UserPc']]))
				$result[$item['UserPc']] = $dates;
			
			if (intval($item['Data'],10) < 10)
				$item['Data'] = "0".$item['Data'];

				$dept = $allDepartments[$item['department']];

				if(!empty($dept)){
                    $nameDepartment = $dept['name']."/";
                    while($dept['parent'] != 0) {
                        //$dept = $dbDepartments->getDepartmentbyId($dept['parent']);
                        $dept = $allDepartments[$dept['parent']];
                        $nameDepartment = $nameDepartment.$dept['name']."/";
                    }	
                    $nameDepartment[strlen($nameDepartment)-1] = '';
				}
				else
					$nameDepartment = '';

		    
			$result[$item['UserPc']]['day'.$item['Data']] = $this->_sec2hms($item['Timp']);
			$result[$item['UserPc']]['cnp'] = $item['cnp']; 
			$result[$item['UserPc']]['complete_name'] = $item['last_name']." ".$item['first_name'];			
			$result[$item['UserPc']]['department'] = $nameDepartment;
			$result[$item['UserPc']]['winName'] = $item['UserPc'];

            $temp = explode(':', $result[$item['UserPc']]['total_hours']);
            $seconds = $temp[0] * 3600 + $temp[1] * 60 + $temp[2];

            $result[$item['UserPc']]['zilelucrate'] = $result[$item['UserPc']]['zilelucrate'] + 1;
            $result[$item['UserPc']]['total_hours'] = $this->_sec2hms($seconds + intval($item['Timp'],10));
            $result[$item['UserPc']]['zilenelucrate'] = $result[$item['UserPc']]['zilenelucrate'] - 1;

            
            
		}

		$result_2 = array();
		
		$start = ($page-1)*$numberPerPage + 1;
		$end = ($page-1)*$numberPerPage + $numberPerPage;
		$i = 1;	
		
		
		//echo $start." ".$end;die();
		$itm = array();
		
		foreach($result as $item)
		{
			if($i >= $start && $i <= $end)
			{
				$indice = $months[$options['month']];
				$indice++;
				for($i = $indice;$i<=31;$i++)
					$item['day'.$indice] = '';
				
				$result_2[] = $item;
			}
			$i++;
			
			//insert the result in the ITM table
			if($page == 1)
			{
				$temp_i = 0;
				$wh = '0.0';
				if(!empty($userItems[$item['winName']]['working_hours']))
					$wh = $userItems[$item['winName']]['working_hours'];
				else
					if(!empty($allDepartments['1']['working_hours']))
						$wh = $allDepartments['1']['working_hours'];
				$count = $this->getNumberOfWorkingDays($item,$wh);	
				if(empty($userItems[$item['winName']]['first_name']))
					$userItems[$item['winName']]['first_name'] = '';
				if(empty($userItems[$item['winName']]['last_name']))
					$userItems[$item['winName']]['last_name'] = '';				
				$itm[] = array(
		        	'department' => $item['department'],
		        	'first_name' => $userItems[$item['winName']]['first_name'],
		        	'last_name' => $userItems[$item['winName']]['last_name'],
		        	'cnp' => $item['cnp'],
		            'day01' => $item['day01'] != '0.0' ? $wh : '0.0',
		            'day02' => $item['day02'] != '0.0' ? $wh : '0.0',
		            'day03' => $item['day03'] != '0.0' ? $wh : '0.0',
		            'day04' => $item['day04'] != '0.0' ? $wh : '0.0',
		            'day05' => $item['day05'] != '0.0' ? $wh : '0.0',
		            'day06' => $item['day06'] != '0.0' ? $wh : '0.0',
		            'day07' => $item['day07'] != '0.0' ? $wh : '0.0',
		            'day08' => $item['day08'] != '0.0' ? $wh : '0.0',
		            'day09' => $item['day09'] != '0.0' ? $wh : '0.0',
		            'day10' => $item['day10'] != '0.0' ? $wh : '0.0',
		            'day11' => $item['day11'] != '0.0' ? $wh : '0.0',
		            'day12' => $item['day12'] != '0.0' ? $wh : '0.0',
		            'day13' => $item['day13'] != '0.0' ? $wh : '0.0',
		            'day14' => $item['day14'] != '0.0' ? $wh : '0.0',
		            'day15' => $item['day15'] != '0.0' ? $wh : '0.0',
		            'day16' => $item['day16'] != '0.0' ? $wh : '0.0',
		            'day17' => $item['day17'] != '0.0' ? $wh : '0.0',
		            'day18' => $item['day18'] != '0.0' ? $wh : '0.0',
		            'day19' => $item['day19'] != '0.0' ? $wh : '0.0',
		            'day20' => $item['day20'] != '0.0' ? $wh : '0.0',
		            'day21' => $item['day21'] != '0.0' ? $wh : '0.0',
		            'day22' => $item['day22'] != '0.0' ? $wh : '0.0',
		            'day23' => $item['day23'] != '0.0' ? $wh : '0.0',
		            'day24' => $item['day24'] != '0.0' ? $wh : '0.0',
		            'day25' => $item['day25'] != '0.0' ? $wh : '0.0',
		            'day26' => $item['day26'] != '0.0' ? $wh : '0.0',
		            'day27' => $item['day27'] != '0.0' ? $wh : '0.0',
		            'day28' => $item['day28'] != '0.0' ? $wh : '0.0',
		            'day29' => $item['day29'] != '0.0' ? $wh : '0.0',
		            'day30' => $item['day30'] != '0.0' ? $wh : '0.0',
		            'day31' => $item['day31'] != '0.0' ? $wh : '0.0',
		            'total_hours' => $this->_sec2hms($count),        			
				
				);
			}
		}
		//print_r($itm);die();
		$db->insertInItm($itm);
		return array( 
					'usage' => $result_2
			  );
		//print_r($result);die();
		
        /*foreach ($response as $item) {
            $tStart = $item['reStartDate']->getTimestamp();
            $tEnd = $item['reEndDate']->getTimestamp();
            $cDate = date('d.m.Y', $tStart);
            if (!isset($temp[$item['reUser']])) {
                $temp[$item['reUser']] = array();
                $temp[$item['reUser']]['id'] = $item['reUser'];
            }
            if (!isset($temp[$item['reUser']][$cDate])) {
                $temp[$item['reUser']][$cDate] = 0.0; 
            }
            $temp[$item['reUser']][$cDate] += ($tEnd - $tStart);
            if (!empty($item['rieID'])) {
                $iStart = $item['rieStart']->getTimestamp();
                $iEnd = $item['rieEnd']->getTimestamp();
                // $temp[$item['reUser']][$cDate]['idle'] += ($iEnd - $iStart);
            }
        }
		
		
		$allUsersTemp = $dbUsers->getAllUsersFromDepartments();
		
		$allUsers = array();
		foreach($allUsersTemp as $localUser)
		{
			$allUsers[$localUser['name']] = $localUser;
		}   


		$allDepartmentsTemp = $dbDepartments->getAllDepartments();
		
		$allDepartments = array();
		foreach($allDepartmentsTemp as $localDepartment)
		{
			$allDepartments[$localDepartment['id']] = $localDepartment;
		}   
		

		//print_r($allUsers);die();
        foreach($temp as $key=>$value) {
			$temp_name = $value['id'];
			
			//$userDetails_temp = $dbUsers->getUserFromDepartmentsByName($temp_name);
			if(!empty($allUsers[$temp_name])){
	            $result[$key] = array();
	            $result[$key] = $dates;
	            $totalore = 0;
	            $zilelucrate = 0;
	
	            foreach($value as $key2=>$values2) {
	
	                $zilelucrate = $zilelucrate + 1;
	                $totalore = $totalore + intval($values2);
					
					//print_r($value);die();
	                if($key2 != 'id') {

	                    list($day, $month, $year) = explode(".", $key2);
	                    list($hours, $minutes, $seconds) = explode(":", $this->_sec2hms($values2));
	                    $result[$key]['day'.$day] = $hours.".".$minutes;
						
						//print_r($hours.".".$minutes);die();
						
	                } else {
	                	
	                    $result[$key][$key2] = $values2;
						
	                    //$userDetails = $dbUsers->getUserFromDepartmentsByName($values2);

						//echo $userDetails['name']."  ";print_r($userDetails['department']);echo "</br>";
						foreach($allUsers[$values2] as $key_temp=>$value_temp)
						{
							if ($key_temp == 'department')
								$department = $value_temp;
							if ($key_temp == 'cnp')
								$cnp = $value_temp;
							if ($key_temp == 'last_name')
								$last_name =$value_temp;
							if ($key_temp == 'first_name')
								$first_name = $value_temp;
								
						}
						
						//$department = $userDetails['department'];
						
	                   // $dept = $dbDepartments->getDepartmentbyId($department);
						$dept = $allDepartments[$department];

						if(!empty($dept)){
		                    $nameDepartment = $dept['name']."/";
		                    while($dept['parent'] != 0) {
		                        //$dept = $dbDepartments->getDepartmentbyId($dept['parent']);
		                        $dept = $allDepartments[$dept['parent']];
		                        $nameDepartment = $nameDepartment.$dept['name']."/";
		                    }	
		                    $nameDepartment[strlen($nameDepartment)-1] = '';
						}
						else
							$nameDepartment = '';
							
	                    $result[$key]['cnp'] = $cnp;
	                    $result[$key]['complete_name'] = $last_name." ".$first_name;
						$result[$key]['first_name'] = $first_name;
						$result[$key]['last_name'] = $last_name;
	                    $result[$key]['winName'] = $key;
	                    $result[$key]['department'] = $nameDepartment;
						$cnp = '';$last_name = '';$first_name = '';$department='';
	                }
	
	            }
				
	            list($hours,$minutes,$seconds) = explode(":",$this->_sec2hms($totalore));
	            $result[$key]['zilelucrate'] = $zilelucrate - 1;
	            $result[$key]['total_hours'] = $hours.".".$minutes;
	            $result[$key]['zilenelucrate'] = 31 - $zilelucrate + 1;
           	}
        }
		
		
		$itm = array();
		
		foreach($result as $users)
		{
			$itm[$users['id']] = array();
			$i = $users['id'];
			
			//$inf = $dbUsers->getUserFromDepartmentsByName($i);
			$inf = $allUsers[$i];
			if(strpos($users['department'],'/') !== false)
			{	$departmentsSplit = explode("/",$users['department']);
				$users['department'] = $departmentsSplit[0];
			}
			foreach($users as $key=>$value)
			{
				
				if($key != 'id' && $key != 'complete_name'&& $key != 'winName'&& $key != 'zilelucrate'&& $key != 'zilenelucrate')
				{

					if(substr($key,0,3) == 'day')
					{
						if($value != '0.0')
						{
							$itm[$i][$key] = $inf['working_hours'];
						}
						else
						{
							$itm[$i][$key] = '0';
						}
					}
					else
					{
						$itm[$i][$key] = $value;
					}
				}
			}
			//print_r($itm);die();
		}
		//print_r($itm);die();
		$db->insertInItm($itm);
		
        return $result;*/
    }
    
    public function getNumberOfWorkingDays($item,$working_hours)
    {
    	$i = 1;
    	$count = 0;
    	$time = 0;
    	for($i = 1;$i <=31; $i++)
    	{
    		if($i < 10)
    		{
    			if($item['day0'.$i] != '0.0')
    				$count++;
    		}
    		else
    		  	if($item['day'.$i] != '0.0')
    				$count++;
    	}
    	
    	if( $working_hours != '0.0' )
    	{
    		$result = explode(":",$working_hours);
    		$time += $result[0] * 3600 + $result[1] * 60 + $result[2];
    	}
    	
    	return $count * $time;
    }
    
    public function getAlertsData($options, $top = 0, $limit = 0)
    { return array(); }
    
    protected function _sec2hms($sec, $padHours = true) 
    {
        $hms = "";
        $hours = intval(intval($sec) / 3600); 
        $hms .= ($padHours) ? str_pad($hours, 2, "0", STR_PAD_LEFT). ":" : $hours. ":";
        $minutes = intval(($sec / 60) % 60); 
        $hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT). ":";
        $seconds = intval($sec % 60); 
        $hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);
        return $hms;
    }
    
    protected function _topDesc($a, $b)
    {
        if ($a['usageTime'] == $b['usageTime'])  return 0;
        return ($a['usageTime'] < $b['usageTime']) ? 1 : -1;
    }

    protected function _topDesc2($a, $b)
    {
        if ($a['documentUsageTime'] == $b['documentUsageTime'])  return 0;
        return ($a['documentUsageTime'] < $b['documentUsageTime']) ? 1 : -1;
    }
    
    protected function _parseUDData($user = null, $username = '',$dept = '1') {
		//print_r($user);die();

		
		$db = new Application_Table_dbDepartments();
        if (!empty($user)) {
            
            $days = array();

            $interval = 0.0;
            $breaks = 0.0;
			
			if(empty($user['working_hours']) || empty($user['cost_per_hour']) || empty($user['break_lenght']))
			{

				if($user['department'] == '0')
				{
					//print_r($user);die();
		            $return = array(
		                'department' => 'no_department',
		                'full_name' => '-',
		                'cost_per_hour' => '<span style="color:red">No data set for this user</span>',
		                'days' => array(),
		                'hoursCfCM' => '<span style="color:red">No data set for this user</span>',
		                'break_length' => '<span style="color:red">No data set for this user</span>',
		                'payment' => '<span style="color:red">No data set for this user</span>',
		                'workCost' => '<span style="color:red">No data set for this user</span>'
		            );	
					
					return $return;				
				}	

				$department = $db->getDepartmentbyId($user['department']);
				
				$parent = '';
				foreach($department as $key=>$value)
				{
					if($key == 'parent')
					{
						$parent = $value;
					}
				}
				
				$department['department'] = $parent;
				//print_r($department);die();
				$result = $this->_parseUDData($department,$username,$dept);
				
				return $result;
			}

            if (!empty($user['break_length'])) {
                $user["break_length"] = explode(':', $user["break_length"]);
                $user["break_length"] = $user["break_length"][0] * 3600 + $user["break_length"][1] * 60 + $user["break_length"][2];
            }
            
            for ($i = 1; $i <= 5; ++$i) {
                if (!empty($user["day{$i}_start"]) && !empty($user["day{$i}_stop"])) {
                    $user["day{$i}_start"] = explode(':', $user["day{$i}_start"]);
                    $user["day{$i}_start"] = $user["day{$i}_start"][0] * 3600 + $user["day{$i}_start"][1] * 60 + $user["day{$i}_start"][2];
                    $user["day{$i}_stop"] = explode(':', $user["day{$i}_stop"]);
                    $user["day{$i}_stop"] = $user["day{$i}_stop"][0] * 3600 + $user["day{$i}_stop"][1] * 60 + $user["day{$i}_stop"][2];
                    $interval += ($user["day{$i}_stop"] - $user["day{$i}_start"]);
                    $breaks += empty($user['break_length']) ? 0 : $user['break_length'];
                    $days[] = $i;
                }
            }
			$name = '';
            if(isset($user['first_name']))
				$name = $user['first_name'];
            if(isset($user['last_name']))
				$name =$name.' '.$user['last_name'];		
			//print_r($user['cost_per_hour'])	;die()
            $return = array(
                'department' => $dept,
                'full_name' => $name,
                'cost_per_hour' => intval($user['cost_per_hour']),
                'days' => $days,
                'hoursCfCM' => number_format($interval / 3600, 2),
                'break_length' => number_format($breaks / 3600, 2),
                'payment' => $user['payment'],
                'workCost' => number_format(intval($user['cost_per_hour']) * $interval / 3600, 2)
            );
            
        } else {
            
			$department = $db->getDepartmentbyId('1');
            $return = array(
                'department' => '-',
                'full_name' => '-',
                'cost_per_hour' => '-',
                'days' => array(),
                'hoursCfCM' => '-',
                'break_length' => '-',
                'payment' => '-',
                'workCost' => '-'
            );
            
        }
        return $return;
    }
    
    protected function _parseDayRoiReport($user = null, $date) {
    	
		$db = new Application_Table_dbDepartments();
        $dbUser = new Application_Table_dbUserToDepartments();  

        $interval = 0.0;        
        
        $user = $dbUser->getUserFromDepartmentsByName($user);
        if(empty($user['cost_per_hour']))
		{
			$user = $db->getDepartmentbyId("1");
		} 

		$currentDay = date("m/d/Y",strtotime($date));
		
    	$day = date("w",strtotime($currentDay));
			
		if( $day == "0")
			$day = 7;
		if(!empty($user["day{$day}_start"]) && $user["day{$day}_stop"]){
			$local1 = explode(':', $user["day{$day}_start"]);
           	$local_start1 = $local1[0] * 3600 + $local1[1] * 60 + $local1[2];
			$local2 = explode(':', $user["day{$day}_stop"]);
           	$local_start2 = $local2[0] * 3600 + $local2[1] * 60 + $local2[2];
            $interval += $local_start2 - $local_start1;			
		}		
        
    }
 
    protected function _parseDaysRoiReport($user = null, $startDate,$endDate) {
		//print_r($user);die();

		$db = new Application_Table_dbDepartments();
        $dbUser = new Application_Table_dbUserToDepartments();    
        $days = array();

        $interval = 0.0;
        $breaks = 0.0;
		$dept = array();
		$dept = $db->getDepartmentbyId("1");
		
		$user = $dbUser->getUserFromDepartmentsByName($user);
		
		if(empty($user['cost_per_hour']))
		{

			/*if($user['department'] == '0')
			{
				//print_r($user);die();
	            $return = array(
	                'department' => 'no_department',
	                'full_name' => '-',
	                'cost_per_hour' => '<span style="color:red">No data set for this user</span>',
	                'days' => array(),
	                'hoursCfCM' => '<span style="color:red">No data set for this user</span>',
	                'break_length' => '<span style="color:red">No data set for this user</span>',
	                'payment' => '<span style="color:red">No data set for this user</span>',
	                'workCost' => '<span style="color:red">No data set for this user</span>'
	            );	
				
				return $return;				
			}*/	
			if(!empty($dept['cost_per_hour'])) 
				$user['cost_per_hour'] = $dept['cost_per_hour'];	
			else
				$user['cost_per_hour'] = '<span style="color:red">Nu a fost setat</span>';	

		}
		
		$start = strtotime($startDate);
		$end = strtotime($endDate);
		$days = ($end - $start)/(60*60*24) + 1;
		$currentDay = date("m/d/Y",strtotime($startDate));
		
		//print_r($user);die();
		
		for($i = 1; $i<=$days;$i++)
		{
			//get day code ( 0 =  Sunday ... 6 = Saturday)
			$day = date("w",strtotime($currentDay));	
			
			if( $day == "0")
				$day = 7;
			if(!empty($user["day{$day}_start"]) && !empty($user["day{$day}_stop"])){
				$local1 = explode(':', $user["day{$day}_start"]);
	           	$local_start1 = $local1[0] * 3600 + $local1[1] * 60 + $local1[2];
				$local2 = explode(':', $user["day{$day}_stop"]);
	           	$local_start2 = $local2[0] * 3600 + $local2[1] * 60 + $local2[2];
	            $interval += $local_start2 - $local_start1;			
			}
			else 
			{
				if(!empty($dept["day{$day}_start"]) && !empty($dept["day{$day}_stop"])){
					$local1 = explode(':', $dept["day{$day}_start"]);
		           	$local_start1 = $local1[0] * 3600 + $local1[1] * 60 + $local1[2];
					$local2 = explode(':', $dept["day{$day}_stop"]);
		           	$local_start2 = $local2[0] * 3600 + $local2[1] * 60 + $local2[2];
		            $interval += $local_start2 - $local_start1;		
				}			
			}
			// get next day
			$temp = strtotime($currentDay) + 86400;
			$currentDay = date("m/d/Y",$temp);
			
		}   
  
        return array(
			"hoursCfCM" => $interval,
			"cost_per_hour" => $user['cost_per_hour']
		);
    } 
    
 protected function _getNumberOfWorkingDays($user = null, $startDate,$endDate) {
		//print_r($user);die();

		$db = new Application_Table_dbDepartments();  
		$dbUser = new Application_Table_dbUserToDepartments(); 
        $days = array();

        $interval = 0.0;
        $breaks = 0;
		$dept = array();
		$dept = $db->getDepartmentbyId("1");
		
		$user = $dbUser->getUserFromDepartmentsByName($user);

		
		$start = strtotime($startDate);
		$end = strtotime($endDate);
		$days = ($end - $start)/(60*60*24) + 1;
		$currentDay = date("m/d/Y",strtotime($startDate));
		
		//print_r($user);die();
		
		for($i = 1; $i<=$days;$i++)
		{
			//get day code ( 0 =  Sunday ... 6 = Saturday)
			$day = date("w",strtotime($currentDay));	
			
			if( $day == "0")
				$day = 7;
			if(!empty($user["day{$day}_start"]) && !empty($user["day{$day}_stop"])){
						$breaks++;
			}
			else 
			{
				if(!empty($dept["day{$day}_start"]) && !empty($dept["day{$day}_stop"])){
					$breaks++;	
				}			
			}
			// get next day
			$temp = strtotime($currentDay) + 86400;
			$currentDay = date("m/d/Y",$temp);
			
		}   
  
        return $breaks;
    }
 
    
    protected function _deptParse(&$departments, $deptId) {
        if ($deptId == 0) return '';
        $parsed = array();
        do {
            array_push($parsed, $departments[$deptId]['name']);
            $deptId = isset($departments[$deptId]['parent']) ? $departments[$deptId]['parent'] : 0;
        } while (!empty($deptId) && $deptId > 0);
        return implode(' / ', array_reverse($parsed));
    }
	
    /* protected function _parseCmpEvents(&$item) {
        $dayEvents = array();
        $started = false;
        $date = $item[0][1]['reStartDate']->getTimestamp();
        if ($item[0][0] == 'off') {
            $started = true;
        }
        $stt = mktime(0, 0, 0, date('n', $date), date('j', $date), date('Y', $date));
        $end = mktime(23, 59, 59, date('n', $date), date('j', $date), date('Y', $date));
        
        $s = false;
        $ev = null;
        foreach ($item as $idx => $event) {
            $prevTime = isset($item[$idx - 1]) ? $item[$idx - 1][1]['reStartDate']->getTimestamp() : $stt;
            $dayEvents[] = array(
                'usage' => $started ? 'Calculator oprit' : 'Calculator pornit',
                'computer' => $event[1]['reComputerID'],
                'user' => $event[1]['reUser'],
                'tStart' => $prevTime,
                'tEnd' => $event[1]['reStartDate']->getTimestamp(),
                'duration' => $event[1]['reStartDate']->getTimestamp() - $prevTime
            );
            $started = !$started;
        }
        $dayEvents[] = array(
            'usage' => $started ? 'Calculator oprit' : 'Calculator pornit',
            'computer' => $item[count($event) - 1][1]['reComputerID'],
            'user' => $item[count($event) - 1][1]['reUser'],
            'tStart' => $item[count($event) - 1][1]['reStartDate']->getTimestamp(),
            'tEnd' => $end,
            'duration' => $end - $item[count($event) - 1][1]['reStartDate']->getTimestamp()
        );
        return $dayEvents;
    } */
    
    /* protected function _parseComputerTimes(&$response) {
        $plugEvents = array(1,2);
        $sessions = array();
        foreach ($response as $item) {
            $date = $item['reStartDate']->getTimestamp();
            $stt = mktime(0, 0, 0, date('n', $date), date('j', $date), date('Y', $date));
            $end = mktime(23, 59, 59, date('n', $date), date('j', $date), date('Y', $date));
            if (!isset($sessions[$item['reComputerID']])) {
                $sessions[$item['reComputerID']] = array();
                $sessionIndex = 0;
            }
            if (!isset($sessions[$item['reComputerID']][$item['reUser']])) {
                $sessions[$item['reComputerID']][$item['reUser']] = array();
                $sessionIndex = 0;
            }
            if (!in_array($item['reType'], $plugEvents)) {
                if (empty($sessions[$item['reComputerID']][$item['reUser']][$sessionIndex])) {
                    $sessions[$item['reComputerID']][$item['reUser']][$sessionIndex] = array(
                        'start' => 0,
                        'end' => 0,
                        'events' => array()
                    );
                }
                if ($sessions[$item['reComputerID']][$item['reUser']][$sessionIndex]['start'] == 0) {
                    $sessions[$item['reComputerID']][$item['reUser']][$sessionIndex]['start'] = $stt;
                }
                if ($sessions[$item['reComputerID']][$item['reUser']][$sessionIndex]['end'] == 0) {
                    $sessions[$item['reComputerID']][$item['reUser']][$sessionIndex]['end'] = $end;
                }
                $sessions[$item['reComputerID']][$item['reUser']][$sessionIndex]['events'][] = $item;
            } else if ($item['reType'] == 1) {
                if (empty($sessions[$item['reComputerID']][$item['reUser']][$sessionIndex])) {
                    $sessions[$item['reComputerID']][$item['reUser']][$sessionIndex] = array(
                        'start' => $stt,
                        'end' => 0,
                        'events' => array()
                    );
                }
            } else if ($item['reType'] == 2) {
                if (!empty($sessions[$item['reComputerID']][$item['reUser']][$sessionIndex])) {
                    $sessions[$item['reComputerID']][$item['reUser']][$sessionIndex]['end'] = $end;
                    ++$sessionIndex;
                }
            }
        }
        return $sessions;
    } */

    protected function _parseComputerTimes(&$response) {
        $temp = array();
        $results = array();
        foreach ($response as $item) {
            if (!isset($temp[$item['reComputerID']])) {
                $temp[$item['reComputerID']] = array();
            }
            if (!isset($temp[$item['reComputerID']][$item['reUser']])) {
                $temp[$item['reComputerID']][$item['reUser']] = array();
            }
            $temp[$item['reComputerID']][$item['reUser']][] = $item;
        }
        foreach ($temp as $computerId => $users) {
            if (!isset($results[$computerId])) {
                $results[$computerId] = array();
            }
            foreach ($users as $userId => $eventList) {
                $sIndex = -1;
                $sessions = array();
                foreach ($eventList as $event) {
                    if (empty($sessions[$sIndex]) || $event['reType'] == 1) {
                        $sessions[++$sIndex] = array();
                    }
                    $sessions[$sIndex][] = $event;
                }
                foreach ($sessions as $idx => $sess) {
                    if (empty($sess)) {
                        unset($sessions[$idx]);
                    }
                }
                $results[$computerId][$userId] = $sessions;
            }
        }
		
		//print_r($results);die();
        return $results;
    }
    
    protected function _parseUserActiveSessions(&$activeSessions, $computer, $user, $session) {
        foreach ($session as &$event) {
            $event['idlType'] = empty($event['rieID']) ? 'active' : 'idle';
        }
        $activeSession = array(
            'computer' => $computer,
            'user' => $user,
            'start' => 0,
            'end' => 0,
            'duration' => 0,
            'usage' => 'Activ'
        );
        $pasiveSession = array(
            'computer' => $computer,
            'user' => $user,
            'start' => 0,
            'end' => 0,
            'duration' => 0,
            'usage' => 'Pauza'
        );
        for ($i = 0; $i < count($session); ++$i) {
            $event = $session[$i];
            $stt = $event['reStartDate']->getTimestamp();
            $end = $event['reEndDate']->getTimestamp();
            $duration = $end - $stt;
            if ($i != 0 && $session[$i - 1]['idlType'] == 'idle' && $session[$i]['idlType'] == 'active') {
                if ($pasiveSession['duration'] > $this->_idlePauseSeconds) {
                    if ($activeSession['duration'] > 0) {
                        array_push($activeSessions, $activeSession);
                    }
                    if ($pasiveSession['duration'] > 0) {
                        array_push($activeSessions, $pasiveSession);
                    }
                } else {
                    // Add passive events to the current active session
                    $activeSession['end'] = $pasiveSession['end'];
                    $activeSession['duration'] += $pasiveSession['duration'];
                    if ($activeSession['duration'] > 0) {
                        array_push($activeSessions, $activeSession);
                    }
                    $activeSession['start'] = 0;
                    $activeSession['end'] = 0;
                    $activeSession['duration'] = 0;
                    $pasiveSession['start'] = 0;
                    $pasiveSession['end'] = 0;
                    $pasiveSession['duration'] = 0;
                }
            }
            if ($session[$i]['idlType'] == 'active') {
                if ($activeSession['start'] == 0) {
                    $activeSession['start'] = $stt;
                }
                $activeSession['end'] = $end;
                $activeSession['duration'] += $duration;
            } else {
                if ($pasiveSession['start'] == 0) {
                    $pasiveSession['start'] = $stt;
                }
                $pasiveSession['end'] = $end;
                $pasiveSession['duration'] += $duration;
            }
        }
    }
}