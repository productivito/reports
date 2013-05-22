<?php

class Application_Table_dbReports extends Zend_Db_Table_Abstract
{
    protected $_db;
    protected $_name = "";
    protected $_primary = "";
    
    protected $_weekDays = array(
        'd' => 1,
        'l' => 2,
        'ma' => 3,
        'mi' => 4,
        'j' => 5,
        'v' => 6,
        's' => 7
    );
    
    public function __construct()
    {
        set_time_limit(0);
        $this->_db = Zend_Registry::get('db');
    }

    public function getDocumentsReportDataGrid($options, $limit = 0, $offset = 0,$page = 1,$type = 'top',$numberPerPage = 100)
    {
    	
   		$where = '';
		$query = '';
		$hourStart = "'00:00:00'";
    	$hourEnd = "'23:59:59'";
		$months = '';
		$response = array();
		$response2 = array();
		$response3 = array();
		$timeIntervals = "-1";
		$typeProgram = "true";
		$dbDepartments = new Application_Table_dbDepartments();
		//echo $numberPerPage;die();
		if (!empty($options['users'])) {
			$where .= ' e.reUser IN ( ';
			$first = true;
			foreach($options['users'] as $user){
				if($first == false){
					$where .= ",'".$user."'";
				}else{
					$where .= "'".$user."'";
					$first = false;
				} 
			}
			$where .= ") AND ";
		}
		
		if (isset($options['dateIs'])) {
			
			$where .="((convert(nvarchar(25),e.reStartDate,101)  >=  '{$options['dateIs']}'  AND
					  convert(nvarchar(25),e.reStartDate,101)  <=  '{$options['dateIs']}')  OR
					  (convert(nvarchar(25),e.reEndDate,101)  >=  '{$options['dateIs']}'  AND
					  convert(nvarchar(25),e.reEndDate,101)  <=  '{$options['dateIs']}')) 
					  ";
					  
			$dates = array(
				"result" => $this->_buildDateForQuerySingle($options['dateIs']),
				"number" => 1
			);		
		}else if (isset($options['dateStart']) && isset($options['dateEnd'])) {
			$dates = $this->_buildDateForQueryMultiple($options['dateStart'],$options['dateEnd']);
			$where .= "((convert(nvarchar(25),e.reStartDate,101)  >=  '{$options['dateStart']}'  AND
					  convert(nvarchar(25),e.reStartDate,101)  <=  '{$options['dateEnd']}')  OR
					  (convert(nvarchar(25),e.reEndDate,101)  >=  '{$options['dateStart']}'  AND
					  convert(nvarchar(25),e.reEndDate,101)  <=  '{$options['dateEnd']}')) 
					  ";
		   switch ($options['interval']) {
            	
                case 'specweek':
					$days = array();
                    foreach ($options['days'] as $day) {
                        $days[] = $this->_weekDays[$day];
                    }					
					$where .= ' AND (DATEPART(weekday, e.reStartDate) IN (' . implode(',', $days) . ') OR 
					            DATEPART(weekday, e.reEndDate) IN (' . implode(',', $days) . '))';
                    break;
                case 'workweek':
                    $where .= ' AND (DATEPART(weekday, e.reStartDate) IN (2,3,4,5,6) OR 
                                DATEPART(weekday, e.reEndDate) IN (2,3,4,5,6) )';
                    break;
                case 'endweek':
                    $where .= ' AND (DATEPART(weekday, e.reStartDate) IN (1,7) OR
                    			DATEPART(weekday, e.reEndDate) IN (1,7) ) ';
                    break;
                case 'allweek':
                default:
                    // Do not filter by days
                    break;
            }		  
			/*if($options['timeinterval'] == 'workhours'){
				$department = $dbDepartments->getDepartmentbyId("1");
				$typeProgram = 'true';				
	            switch ($options['interval']) {
	            	
	                case 'specweek':
						$days = array();
	                    foreach ($options['days'] as $day) {
	                        $days[] = $this->_weekDays[$day];
	                    }
						$i = 0;
	                    $where .= ' AND (';
	                    foreach($days as $day){				
						$where .= " ((DATEPART(weekday, e.reStartDate) = ".$day." OR ".
						           " DATEPART(weekday, e.reEndDate) = " . $day . ") AND".
 						            " ((convert(nvarchar(25),e.reStartDate,8)  >=  convert(nvarchar(8), '".$department['day'.$day."_start"]."', 8)  AND ".
									" convert(nvarchar(25),e.reStartDate,8)  <=  convert(nvarchar(8), '".$department['day'.$day."_stop"]."', 8))  OR".
									  " (convert(nvarchar(25),e.reEndDate,8)  >=  convert(nvarchar(8), '".$department['day'.$day."_start"]."', 8)  AND ".
								  	" convert(nvarchar(25),e.reEndDate,8)  <=  convert(nvarchar(8), '".$department['day'.$day."_stop"]."', 8)))) ";
							$i++;
							if(count($days) > $i )	
								$where .= " OR ";
						}
						$where .= " ) ";
	                    break;
	                case 'workweek':
	                    $where .= ' AND (DATEPART(weekday, e.reStartDate) IN (2,3,4,5,6) OR 
	                                DATEPART(weekday, e.reEndDate) IN (2,3,4,5,6) )';
	                    break;
	                case 'endweek':
	                    $where .= ' AND (DATEPART(weekday, e.reStartDate) IN (1,7) OR
	                    			DATEPART(weekday, e.reEndDate) IN (1,7) ) ';
	                    break;
	                case 'allweek':
	                default:
	                    // Do not filter by days
	                    break;
	            }
	        }*/
        }
   		switch($options['timeinterval']) {
            case 'workinghours':
				$where .= "  AND ((convert(nvarchar(25),e.reStartDate,8)  >=  convert(nvarchar(8), '{$options['hArray']['startTime']}', 8)  AND
					  convert(nvarchar(25),e.reStartDate,8)  <=  convert(nvarchar(8), '{$options['hArray']['endTime']}', 8))  OR
					  (convert(nvarchar(25),e.reEndDate,8)  >=  convert(nvarchar(8), '{$options['hArray']['startTime']}', 8)  AND
					  convert(nvarchar(25),e.reEndDate,8)  <=  convert(nvarchar(8), '{$options['hArray']['endTime']}', 8))) 
					  ";
				$hourStart = "'".$options['hArray']['startTime']."'";
				$hourEnd = "'".$options['hArray']['endTime']."'";					  
                break;
			case 'workhours':
				$department = $dbDepartments->getDepartmentbyId("1");
				$typeProgram = 'true';
				break;
			case 'workoutsidehours' :
				$department = $dbDepartments->getDepartmentbyId("1");
				$typeProgram = 'false';				
				break;
            case 'workallhours':
            default:
                // Do not filter by hours
                break;
        }
		//echo $where;die();
		if(!empty($department))
		{
			$timeIntervals = '';
			if(!empty($department['day1_start']))
				$timeIntervals .= $department['day1_start'].",".$department['day1_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";
			if(!empty($department['day2_start']))
				$timeIntervals .= $department['day2_start'].",".$department['day2_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day3_start']))
				$timeIntervals .= $department['day3_start'].",".$department['day3_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day4_start']))
				$timeIntervals .= $department['day4_start'].",".$department['day4_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day5_start']))
				$timeIntervals .= $department['day5_start'].",".$department['day5_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day6_start']))
				$timeIntervals .= $department['day6_start'].",".$department['day6_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day7_start']))
				$timeIntervals .= $department['day7_start'].",".$department['day7_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
		}
		//echo $where;die();
		
		try {
		    $stmt = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetDocumentsReport ?,?,?,?,?,?,?,?,?");
			$stmt2 = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetDocumentsReportTotalAndCount ?,?,?,?,?,?,?,?,?");  
			$stmt3 = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetDocumentsReportCron ?,?,?,?,?,?,?,?,?");
			if($type == 'top' || $type == 'export' || $type == 'cron'){
				$start = ($page-1)*$numberPerPage + 1;
				$end = ($page-1)*$numberPerPage + $numberPerPage;
			}
			if($type == 'chart'){
				$start = 0;
				$end = $page;
			}
			$params = array(":start"=> $start,
							":end"=> $end,
							":where"=>$where,
							":hourStart"=>$hourStart,
							":hourEnd"=>$hourEnd,
							":dates" => $dates['result'],
							":number" => intval($dates['number']),
							":timeIntervals" => $timeIntervals,
							":type" => $typeProgram							
							);

			if($type != 'cron'){			
			    $stmt->execute($params);
			    $response = $stmt->fetchAll();
				
				$stmt2->execute($params);  
				$response2 = $stmt2->fetchAll();
			}		
	
			$stmt3->execute($params);  
			$response3 = $stmt3->fetchAll();	
			
			//print_r($response2);die();				
		
		  }
		  catch (Zend_Db_Adapter_Exception $e) {
		    print $e->__toString();
		  }
		  catch (PDOException $e) {
		    print $e->__toString();
		  }
		  catch (Zend_Exception $e) {
		    print $e->__toString();
		}
		
		return array(
					"response" => $response,
					"totalandcount" => $response2,
					"cron" => $response3
					);			
        /*$this->_name = "Event";
        $this->_primary = "reID";
        
        $query = $this->select();
        $query
            ->setIntegrityCheck(false)
            ->from(array('event' => "Event"))
            //->joinLeft(array('rIdle' => "IdleEvent"), "event.reID=rIdle.rieRawEventID")
			 ->where("event.reID NOT IN (SELECT rieRawEventID FROM IdleEvent)");
        
        // Specific filters - DOC, DOCX, XLS, XLSX, PPT, PPTX, PDF, RTF, ODT, SXW
        $docListFilters = array(
            "event.reWnd LIKE '%.doc%'",
            "event.reWnd LIKE '%.docx%'",
            "event.reWnd LIKE '%.xls%'",
            "event.reWnd LIKE '%.xlsx%'",
            "event.reWnd LIKE '%.ppt%'",
            "event.reWnd LIKE '%.pptx%'",
            "event.reWnd LIKE '%.pdf%'",
            "event.reWnd LIKE '%.rtf%'",
            "event.reWnd LIKE '%.odt%'",
            "event.reWnd LIKE '%.sxw%'",
            "event.reProcess LIKE 'Microsoft Word'",
            "event.reProcess LIKE 'Microsoft Office Word'",
            "event.reProcess LIKE 'Microsoft Excel'",
            "event.reProcess LIKE 'Microsoft Office Excel'",
        );
        $query->where('(' . implode(' OR ', $docListFilters) . ')');
        
        // User filters
        if (!empty($options['users'])) {
            if (is_string($options['users'])) $options['users'] = array($options['users']);
            $query->where('event.reUser IN ( ? )', $options['users']);
        }
        
        // Date filters
        if (isset($options['dateIs'])) {
            $query->where('convert(nvarchar(10), event.reStartDate, 101) = ?', $options['dateIs']);
        } else if (isset($options['dateStart']) && isset($options['dateEnd'])) {
            $query
                ->where('convert(nvarchar(10), event.reStartDate, 101) >= ?', $options['dateStart'])
                ->where('convert(nvarchar(10), event.reEndDate, 101) <= ?', $options['dateEnd']);
            switch ($options['interval']) {
                case 'specweek':
                    $days = array();
                    foreach ($options['days'] as $day) {
                        $days[] = $this->_weekDays[$day];
                    }
                    $query->where('DATEPART(weekday, event.reStartDate) IN (' . implode(',', $days) . ')');
                    break;
                case 'workweek':
                    $query->where('DATEPART(weekday, event.reStartDate) IN (2,3,4,5,6)');
                    break;
                case 'endweek':
                    $query->where('DATEPART(weekday, event.reStartDate) IN (1,7)');
                    break;
                case 'allweek':
                default:
                    // Do not filter by days
                    break;
            }
        }
        
        // Time filters
        switch($options['timeinterval']) {
            case 'workinghours':
                $query
                    ->where('convert(nvarchar(8), event.reStartDate, 8) > convert(nvarchar(8), ?, 8)', $options['hArray']['startTime'])
                    ->where('convert(nvarchar(8), event.reEndDate, 8) <= convert(nvarchar(8), ?, 8)', $options['hArray']['endTime']);
                break;
            case 'workhours':
                $query
                    ->where('convert(nvarchar(8), event.reStartDate, 8) > convert(nvarchar(8), ?, 8)', "09:00:00")
                    ->where('convert(nvarchar(8), event.reEndDate, 8) <= convert(nvarchar(8), ?, 8)', "18:00:00");
                break;
            case 'workoutsidehours':
                $query->where('((convert(nvarchar(8), event.reStartDate, 8) <= ?) OR ' .
                        '(convert(nvarchar(8), event.reEndDate, 8) > ?))', '09:00:00', '18:00:00');
                break;
            case 'workallhours':
            default:
                // Do not filter by hours
                break;
        }
        
        // Order
        $query->order(array(
                        'event.reStartDate ASC'
                    ));
        
        // Limit
        if ($limit > 0) {
            if ($offset > 0) {
                $query->limit($limit, $offset);
            } else {
                $query->limit($limit);
            }
        }
        
        $result = $this->fetchAll($query);
        return empty($result) ? array() : $result->toArray();*/
    }
    
    public function getChatReportDataGrid($options, $limit = 0, $offset = 0,$page = 1,$type = 'all',$numberPerPage = 100)
    {
    	$where = '';
		$query = '';
		$hourStart = "'00:00:00'";
    	$hourEnd = "'23:59:59'";
		$months = '';
		$typeProgram = 'true';
		$timeIntervals = '-1';
		$dbDepartments = new Application_Table_dbDepartments();
		$response = array();
		$response2 = array();
		$response3 = array();
		//echo $numberPerPage;die();
		if (!empty($options['users'])) {
			$where .= ' e.reUser IN ( ';
			$first = true;
			foreach($options['users'] as $user){
				if($first == false){
					$where .= ",'".$user."'";
				}else{
					$where .= "'".$user."'";
					$first = false;
				} 
			}
			$where .= ") AND ";
		}
		
		if (isset($options['dateIs'])) {
			
			$where .="((convert(nvarchar(25),e.reStartDate,101)  >=  '{$options['dateIs']}'  AND
					  convert(nvarchar(25),e.reStartDate,101)  <=  '{$options['dateIs']}')  OR
					  (convert(nvarchar(25),e.reEndDate,101)  >=  '{$options['dateIs']}'  AND
					  convert(nvarchar(25),e.reEndDate,101)  <=  '{$options['dateIs']}')) 
					  ";
					  
			$dates = array(
				"result" => $this->_buildDateForQuerySingle($options['dateIs']),
				"number" => 1
			);		
		}else if (isset($options['dateStart']) && isset($options['dateEnd'])) {
			$dates = $this->_buildDateForQueryMultiple($options['dateStart'],$options['dateEnd']);
			$where .= "((convert(nvarchar(25),e.reStartDate,101)  >=  '{$options['dateStart']}'  AND
					  convert(nvarchar(25),e.reStartDate,101)  <=  '{$options['dateEnd']}')  OR
					  (convert(nvarchar(25),e.reEndDate,101)  >=  '{$options['dateStart']}'  AND
					  convert(nvarchar(25),e.reEndDate,101)  <=  '{$options['dateEnd']}')) 
					  ";
            switch ($options['interval']) {
            	
                case 'specweek':
					$days = array();
                    foreach ($options['days'] as $day) {
                        $days[] = $this->_weekDays[$day];
                    }					
					$where .= ' AND (DATEPART(weekday, e.reStartDate) IN (' . implode(',', $days) . ') OR 
					            DATEPART(weekday, e.reEndDate) IN (' . implode(',', $days) . '))';
                    break;
                case 'workweek':
                    $where .= ' AND (DATEPART(weekday, e.reStartDate) IN (2,3,4,5,6) OR 
                                DATEPART(weekday, e.reEndDate) IN (2,3,4,5,6) )';
                    break;
                case 'endweek':
                    $where .= ' AND (DATEPART(weekday, e.reStartDate) IN (1,7) OR
                    			DATEPART(weekday, e.reEndDate) IN (1,7) ) ';
                    break;
                case 'allweek':
                default:
                    // Do not filter by days
                    break;
            }
        }

   		switch($options['timeinterval']) {
            case 'workinghours':
				$where .= "  AND ((convert(nvarchar(25),e.reStartDate,8)  >=  convert(nvarchar(8), '{$options['hArray']['startTime']}', 8)  AND
					  convert(nvarchar(25),e.reStartDate,8)  <=  convert(nvarchar(8), '{$options['hArray']['endTime']}', 8))  OR
					  (convert(nvarchar(25),e.reEndDate,8)  >=  convert(nvarchar(8), '{$options['hArray']['startTime']}', 8)  AND
					  convert(nvarchar(25),e.reEndDate,8)  <=  convert(nvarchar(8), '{$options['hArray']['endTime']}', 8))) 
					  ";
				$hourStart = "'".$options['hArray']['startTime']."'";
				$hourEnd = "'".$options['hArray']['endTime']."'";					  
                break;
               break;
			case 'workhours':
				$department = $dbDepartments->getDepartmentbyId("1");
				$typeProgram = 'true';
				break;
			case 'workoutsidehours' :
				$department = $dbDepartments->getDepartmentbyId("1");
				$typeProgram = 'false';				
				break;
            case 'workallhours':
            default:
                // Do not filter by hours
                break;
        }
		//echo $where;die();
		if(!empty($department))
		{
			$timeIntervals = '';
			if(!empty($department['day1_start']))
				$timeIntervals .= $department['day1_start'].",".$department['day1_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";
			if(!empty($department['day2_start']))
				$timeIntervals .= $department['day2_start'].",".$department['day2_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day3_start']))
				$timeIntervals .= $department['day3_start'].",".$department['day3_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day4_start']))
				$timeIntervals .= $department['day4_start'].",".$department['day4_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day5_start']))
				$timeIntervals .= $department['day5_start'].",".$department['day5_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day6_start']))
				$timeIntervals .= $department['day6_start'].",".$department['day6_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day7_start']))
				$timeIntervals .= $department['day7_start'].",".$department['day7_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
		}
		
		try {
		    $stmt = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetChatReport ?,?,?,?,?,?,?,?,?,?");  
			if($type == 'top' || $type == 'export' || $type == 'cron' || $type == 'all'){
				$start = ($page-1)*$numberPerPage + 1;
				$end = ($page-1)*$numberPerPage + $numberPerPage;
			}
			if($type == 'chart'){
				$start = 0;
				$end = $page;
			}
			$params = array(":start"=> $start,
							":end"=> $end,
							":where"=>$where,
							":hourStart"=>$hourStart,
							":hourEnd"=>$hourEnd,
							":process"=>"'%Yahoo! Messenger%'",
							":dates" => $dates['result'],
							":number" => intval($dates['number']),
							":timeIntervals" => $timeIntervals,
							":type" => $typeProgram															
							);		
							
			if($type != 'cron'){			
			    $stmt->execute($params);
			    $response = $stmt->fetchAll();
				
				$stmt2 = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetChatReportTotalAndCount ?,?,?,?,?,?,?,?,?,?");
				$stmt2->execute($params);  
				$response2 = $stmt2->fetchAll();	
			}	
			if($type != 'top'){			
			    $stmt3 = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetChatReportCron ?,?,?,?,?,?,?,?,?,?");
				$stmt3->execute($params);
				$response3 = $stmt3->fetchAll();				
			}			
		  }
		  catch (Zend_Db_Adapter_Exception $e) {
		    print $e->__toString();
		  }
		  catch (PDOException $e) {
		    print $e->__toString();
		  }
		  catch (Zend_Exception $e) {
		    print $e->__toString();
		}
		
		return  array(
					"response" => $response,
					"cron" => $response3,
					"totalandcount" => $response2
					);	
    	
/*		
        $this->_name = "Event";
        $this->_primary = "reID";
        
        $query = $this->select();
        $query
            ->setIntegrityCheck(false)
            ->from(array('event' => "Event"));

        // Specific filters
        $query->where("event.reProcess LIKE '%Yahoo! Messenger%'");
        
        // User filters
        if (!empty($options['users'])) {
            if (is_string($options['users'])) $options['users'] = array($options['users']);
            $query->where('event.reUser IN ( ? )', $options['users']);
        }
        
        // Date filters
        if (isset($options['dateIs'])) {
            $query->where('convert(nvarchar(10), event.reStartDate, 101) = ?', $options['dateIs']);
        } else if (isset($options['dateStart']) && isset($options['dateEnd'])) {
            $query
                ->where('convert(nvarchar(10), event.reStartDate, 101) >= ?', $options['dateStart'])
                ->where('convert(nvarchar(10), event.reEndDate, 101) < ?', $options['dateEnd']);
            switch ($options['interval']) {
                case 'specweek':
                    $days = array();
                    foreach ($options['days'] as $day) {
                        $days[] = $this->_weekDays[$day];
                    }
                    $query->where('DATEPART(weekday, event.reStartDate) IN (' . implode(',', $days) . ')');
                    break;
                case 'workweek':
                    $query->where('DATEPART(weekday, event.reStartDate) IN (2,3,4,5,6)');
                    break;
                case 'endweek':
                    $query->where('DATEPART(weekday, event.reStartDate) IN (1,7)');
                    break;
                case 'allweek':
                default:
                    // Do not filter by days
                    break;
            }
        }
        
        // Time filters
        switch($options['timeinterval']) {
            case 'workinghours':
                $query
                    ->where('convert(nvarchar(8), event.reStartDate, 8) > convert(nvarchar(8), ?, 8)', $options['hArray']['startTime'])
                    ->where('convert(nvarchar(8), event.reEndDate, 8) <= convert(nvarchar(8), ?, 8)', $options['hArray']['endTime']);
                break;
            case 'workhours':
                $query
                    ->where('convert(nvarchar(8), event.reStartDate, 8) > convert(nvarchar(8), ?, 8)', "09:00:00")
                    ->where('convert(nvarchar(8), event.reEndDate, 8) <= convert(nvarchar(8), ?, 8)', "18:00:00");
                break;
            case 'workoutsidehours':
                $query->where('((convert(nvarchar(8), event.reStartDate, 8) <= ?) OR ' .
                        '(convert(nvarchar(8), event.reEndDate, 8) > ?))', '09:00:00', '18:00:00');
                break;
            case 'workallhours':
            default:
                // Do not filter by hours
                break;
        }
        
        // Order
        $query->order(array(
                        'event.reStartDate ASC'
                    ));
        
        // Limit
        if ($limit > 0) {
            if ($offset > 0) {
                $query->limit($limit, $offset);
            } else {
                $query->limit($limit);
            }
        }

        $result = $this->fetchAll($query);
        return empty($result) ? array() : $result->toArray();
 	 */
    }
    
    // @TODO: Implement this
    public function getComputerReportDataGrid($options, $limit = 0, $offset = 0,$page = 1,$numberPerPage = 10,$type = 'all')
    {

   		$where = '';
		$query = '';
		$hourStart = "'00:00:00'";
    	$hourEnd = "'23:59:59'";
		$months = '';
		$response = array();
		$response2 = array();
		$response3 = array();
		$response4 = array();
		
		$typeProgram = 'true';
		$timeIntervals = '-1';
		$dbDepartments = new Application_Table_dbDepartments();			
		//echo $numberPerPage;die();
		if (!empty($options['users'])) {
			$where .= ' e.reUser IN ( ';
			$first = true;
			foreach($options['users'] as $user){
				if($first == false){
					$where .= ",'".$user."'";
				}else{
					$where .= "'".$user."'";
					$first = false;
				} 
			}
			$where .= ") AND ";
		}
		
		if (isset($options['dateIs'])) {
			
			$where .="((convert(nvarchar(25),e.reStartDate,101)  >=  '{$options['dateIs']}'  AND
					  convert(nvarchar(25),e.reStartDate,101)  <=  '{$options['dateIs']}')  OR
					  (convert(nvarchar(25),e.reEndDate,101)  >=  '{$options['dateIs']}'  AND
					  convert(nvarchar(25),e.reEndDate,101)  <=  '{$options['dateIs']}')) 
					  ";
					  
			$dates = array(
				"result" => $this->_buildDateForQuerySingle($options['dateIs']),
				"number" => 1
			);		
		}else if (isset($options['dateStart']) && isset($options['dateEnd'])) {
			$dates = $this->_buildDateForQueryMultiple($options['dateStart'],$options['dateEnd']);
			$where .= "((convert(nvarchar(25),e.reStartDate,101)  >=  '{$options['dateStart']}'  AND
					  convert(nvarchar(25),e.reStartDate,101)  <=  '{$options['dateEnd']}')  OR
					  (convert(nvarchar(25),e.reEndDate,101)  >=  '{$options['dateStart']}'  AND
					  convert(nvarchar(25),e.reEndDate,101)  <=  '{$options['dateEnd']}')) 
					  ";
            switch ($options['interval']) {
            	
                case 'specweek':
					$days = array();
                    foreach ($options['days'] as $day) {
                        $days[] = $this->_weekDays[$day];
                    }					
					$where .= ' AND (DATEPART(weekday, e.reStartDate) IN (' . implode(',', $days) . ') OR 
					            DATEPART(weekday, e.reEndDate) IN (' . implode(',', $days) . '))';
                    break;
                case 'workweek':
                    $where .= ' AND (DATEPART(weekday, e.reStartDate) IN (2,3,4,5,6) OR 
                                DATEPART(weekday, e.reEndDate) IN (2,3,4,5,6) )';
                    break;
                case 'endweek':
                    $where .= ' AND (DATEPART(weekday, e.reStartDate) IN (1,7) OR
                    			DATEPART(weekday, e.reEndDate) IN (1,7) ) ';
                    break;
                case 'allweek':
                default:
                    // Do not filter by days
                    break;
            }
        }

   		switch($options['timeinterval']) {
            case 'workinghours':
				$where .= "  AND ((convert(nvarchar(25),e.reStartDate,8)  >=  convert(nvarchar(8), '{$options['hArray']['startTime']}', 8)  AND
					  convert(nvarchar(25),e.reStartDate,8)  <=  convert(nvarchar(8), '{$options['hArray']['endTime']}', 8))  OR
					  (convert(nvarchar(25),e.reEndDate,8)  >=  convert(nvarchar(8), '{$options['hArray']['startTime']}', 8)  AND
					  convert(nvarchar(25),e.reEndDate,8)  <=  convert(nvarchar(8), '{$options['hArray']['endTime']}', 8))) 
					  ";
				$hourStart = "'".$options['hArray']['startTime']."'";
				$hourEnd = "'".$options['hArray']['endTime']."'";					  
               break;
			case 'workhours':
				$department = $dbDepartments->getDepartmentbyId("1");
				$typeProgram = 'true';
				break;
			case 'workoutsidehours' :
				$department = $dbDepartments->getDepartmentbyId("1");
				$typeProgram = 'false';				
				break;
            case 'workallhours':
            default:
                // Do not filter by hours
                break;
        }
		
		if(!empty($department))
		{
			$timeIntervals = '';
			if(!empty($department['day1_start']))
				$timeIntervals .= $department['day1_start'].",".$department['day1_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";
			if(!empty($department['day2_start']))
				$timeIntervals .= $department['day2_start'].",".$department['day2_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day3_start']))
				$timeIntervals .= $department['day3_start'].",".$department['day3_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day4_start']))
				$timeIntervals .= $department['day4_start'].",".$department['day4_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day5_start']))
				$timeIntervals .= $department['day5_start'].",".$department['day5_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day6_start']))
				$timeIntervals .= $department['day6_start'].",".$department['day6_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day7_start']))
				$timeIntervals .= $department['day7_start'].",".$department['day7_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
		}
		
		//print_r($where);die();
		try {
		    $stmt = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetComputerReport ?,?,?,?,?,?,?,?,?");  
			$stmt2 = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetComputerReportCount ?,?,?,?,?,?,?,?,?");
			$stmt3 = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetComputerReportGraph ?,?,?,?,?,?,?,?,?");
			$start = ($page-1)*$numberPerPage + 1;
			$end = ($page-1)*$numberPerPage + $numberPerPage;

			//echo $start." ".$end;die();

			$params = array(":start"=> $start,
							":end"=> $end,
							":where"=>$where,
							":hourStart"=>$hourStart,
							":hourEnd"=>$hourEnd,
							":dates" => $dates['result'],
							":number" => intval($dates['number']),
							":timeIntervals" => $timeIntervals,
							":type" => $typeProgram							
							);
			$params2 = array(":start"=> 1,
							":end"=> 1000,
							":where"=>$where,
							":hourStart"=>$hourStart,
							":hourEnd"=>$hourEnd,
							":dates" => $dates['result'],
							":number" => intval($dates['number']),
							":timeIntervals" => $timeIntervals,
							":type" => $typeProgram							
							);							
									
		    $stmt->execute($params);
		    $response = $stmt->fetchAll();

		    $stmt2->execute($params);
		    $response2 = $stmt2->fetchAll();
		    
		    if($type == 'all'){
			    $stmt3->execute($params);
			    $response3 = $stmt3->fetchAll();
				
			    if(!empty($options['users']))
			    {
			    	if(count($options['users']) == 1)
			    	{
					    $stmt->execute($params2);
					    $response4 = $stmt->fetchAll();					    		
			    	}
			    }
		    }	    
			//print_r($response4);die();
		  }
		  catch (Zend_Db_Adapter_Exception $e) {
		    print $e->__toString();
		  }
		  catch (PDOException $e) {
		    print $e->__toString();
		  }
		  catch (Zend_Exception $e) {
		    print $e->__toString();
		}
		
		return array(
					"response" => $response,
					"total" => $response2,
					'graph' => $response3,
					"graphUser" => $response4
					);    	
		
        /* $this->_name = "Event";
        $this->_primary = "reID";
        
        $query = $this->select();
        $query
            ->setIntegrityCheck(false)
            ->from(array('event' => "Event"))
            ->joinLeft(array('rIdle' => "IdleEvent"), "event.reID=rIdle.rieRawEventID");

        // Specific filters
        // $query->where("reType IN (1, 2) OR reID IN (SELECT reID - 1 FROM [ProductivitoMonitor].[dbo].[Event] WHERE reType IN (1, 2))");
        
        // User filters
        if (!empty($options['users'])) {
            if (is_string($options['users'])) $options['users'] = array($options['users']);
            $query->where('event.reUser IN ( ? )', $options['users']);
        }	
        
        // Date filters
        if (isset($options['dateIs'])) {
            $query->where('convert(nvarchar(10), event.reStartDate, 101) = ?', $options['dateIs']);
        } else if (isset($options['dateStart']) && isset($options['dateEnd'])) {
            $query
                ->where('convert(nvarchar(10), event.reStartDate, 101) >= ?', $options['dateStart'])
                ->where('convert(nvarchar(10), event.reEndDate, 101) <= ?', $options['dateEnd']);
            switch ($options['interval']) {
                case 'specweek':
                    $days = array();
                    foreach ($options['days'] as $day) {
                        $days[] = $this->_weekDays[$day];
                    }
                    $query->where('DATEPART(weekday, event.reStartDate) IN (' . implode(',', $days) . ')');
                    break;
                case 'workweek':
                    $query->where('DATEPART(weekday, event.reStartDate) IN (2,3,4,5,6)');
                    break;
                case 'endweek':
                    $query->where('DATEPART(weekday, event.reStartDate) IN (1,7)');
                    break;
                case 'allweek':
                default:
                    // Do not filter by days
                    break;
            }
        }
        
        // Time filters
        switch($options['timeinterval']) {
            case 'workinghours':
                $query
                    ->where('convert(nvarchar(8), event.reStartDate, 8) > convert(nvarchar(8), ?, 8)', $options['hArray']['startTime'])
                    ->where('convert(nvarchar(8), event.reEndDate, 8) <= convert(nvarchar(8), ?, 8)', $options['hArray']['endTime']);
                break;
            case 'workhours':
                $query
                    ->where('convert(nvarchar(8), event.reStartDate, 8) > convert(nvarchar(8), ?, 8)', "09:00:00")
                    ->where('convert(nvarchar(8), event.reEndDate, 8) <= convert(nvarchar(8), ?, 8)', "18:00:00");
                break;
            case 'workoutsidehours':
                $query->where('((convert(nvarchar(8), event.reStartDate, 8) <= ?) OR ' .
                        '(convert(nvarchar(8), event.reEndDate, 8) > ?))', '09:00:00', '18:00:00');
                break;
            case 'workallhours':
            default:
                // Do not filter by hours
                break;
        }
        
        // Order
        $query->order(array(
                        'event.recomputerID ASC',
                        'event.reUser ASC',
                        'event.reStartDate ASC'
                    ));
        
        // Limit
        if ($limit > 0) {
            if ($offset > 0) {
                $query->limit($limit, $offset);
            } else {
                $query->limit($limit);
            }
        }

        $result = $this->fetchAll($query);
        
		
		
        return empty($result) ? array() : $result->toArray();*/
    }


	public function getComputerReportDataGridEvents($user, $day)
	{
		
		$month = substr($day,3,2);
		$year = substr ($day,6,4);
		
		$table = "_".$year."_".$month;
		$response = array();
		$response2 = array();
		$response3 = array();
		try {
		    $stmt = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetActivityReportPause ?,?,?");  
			$stmt2 = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetComputerEvents ?,?,?");
			$stmt3 = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetComputerStoppedTime ?,?,?");
			$params = array(":user"=> $user,
							":day"=> $day,
							":table"=>$table,						
							);

			$params2 = array(":user"=> "'".$user."'",
							":day"=> "'".$day."'",
							":table"=>$table,						
							);							
		    $stmt->execute($params);
		    $response = $stmt->fetchAll();

		    $stmt2->execute($params);
		    $response2 = $stmt2->fetchAll();
		    
		    $stmt3->execute($params2);
		    $response3 = $stmt3->fetchAll();		    

			//print_r($response3);die();
		  }
		  catch (Zend_Db_Adapter_Exception $e) {
		    print $e->__toString();
		  }
		  catch (PDOException $e) {
		    print $e->__toString();
		  }
		  catch (Zend_Exception $e) {
		    print $e->__toString();
		}		
		  
		 return array(
		 	'pause' => $response,
		 	'event' => $response2,
		 	'stoppedTime' => $response3
		 );
		
	}

    public function getInternetReportDataGrid($options, $limit = 0, $offset = 0,$page = 1,$type = 'all',$numberPerPage = 100)
    {
    	
    	$where = '';
		$query = '';
		$hourStart = "'00:00:00'";
    	$hourEnd = "'23:59:59'";
		$typeProgram = 'true';
		$timeIntervals = '-1';
		$dbDepartments = new Application_Table_dbDepartments();
		$response = array();
		$response2 = array();
		$response3 = array();		
		//echo $numberPerPage;die();
		if (!empty($options['users'])) {
			$where .= ' e.reUser IN ( ';
			$first = true;
			foreach($options['users'] as $user){
				if($first == false){
					$where .= ",'".$user."'";
				}else{
					$where .= "'".$user."'";
					$first = false;
				} 
			}
			$where .= ") AND ";
		}
		
		if (isset($options['dateIs'])) {
			
			$where .="((convert(nvarchar(25),e.reStartDate,101)  >=  '{$options['dateIs']}'  AND
					  convert(nvarchar(25),e.reStartDate,101)  <=  '{$options['dateIs']}')  OR
					  (convert(nvarchar(25),e.reEndDate,101)  >=  '{$options['dateIs']}'  AND
					  convert(nvarchar(25),e.reEndDate,101)  <=  '{$options['dateIs']}')) 
					  ";
			$dates = array(
				"result" => $this->_buildDateForQuerySingle($options['dateIs']),
				"number" => 1
			);		
		}else if (isset($options['dateStart']) && isset($options['dateEnd'])) {
			$dates = $this->_buildDateForQueryMultiple($options['dateStart'],$options['dateEnd']);
			$where .= "((convert(nvarchar(25),e.reStartDate,101)  >=  '{$options['dateStart']}'  AND
					  convert(nvarchar(25),e.reStartDate,101)  <=  '{$options['dateEnd']}')  OR
					  (convert(nvarchar(25),e.reEndDate,101)  >=  '{$options['dateStart']}'  AND
					  convert(nvarchar(25),e.reEndDate,101)  <=  '{$options['dateEnd']}')) 
					  ";
            switch ($options['interval']) {
                case 'specweek':
					$days = array();
                    foreach ($options['days'] as $day) {
                        $days[] = $this->_weekDays[$day];
                    }					
					$where .= ' AND (DATEPART(weekday, e.reStartDate) IN (' . implode(',', $days) . ') OR 
					            DATEPART(weekday, e.reEndDate) IN (' . implode(',', $days) . '))';
                    break;
                case 'workweek':
                    $where .= ' AND (DATEPART(weekday, e.reStartDate) IN (2,3,4,5,6) OR 
                                DATEPART(weekday, e.reEndDate) IN (2,3,4,5,6) )';
                    break;
                case 'endweek':
                    $where .= ' AND (DATEPART(weekday, e.reStartDate) IN (1,7) OR
                    			DATEPART(weekday, e.reEndDate) IN (1,7) ) ';
                    break;
                case 'allweek':
                default:
                    // Do not filter by days
                    break;
            }
        }

   		switch($options['timeinterval']) {
            case 'workinghours':
				$where .= "  AND ((convert(nvarchar(25),e.reStartDate,8)  >=  convert(nvarchar(8), '{$options['hArray']['startTime']}', 8)  AND
					  convert(nvarchar(25),e.reStartDate,8)  <=  convert(nvarchar(8), '{$options['hArray']['endTime']}', 8))  OR
					  (convert(nvarchar(25),e.reEndDate,8)  >=  convert(nvarchar(8), '{$options['hArray']['startTime']}', 8)  AND
					  convert(nvarchar(25),e.reEndDate,8)  <=  convert(nvarchar(8), '{$options['hArray']['endTime']}', 8))) 
					  ";
				$hourStart = "'".$options['hArray']['startTime']."'";
				$hourEnd = "'".$options['hArray']['endTime']."'";					  
                break;
			case 'workhours':
				$department = $dbDepartments->getDepartmentbyId("1");
				$typeProgram = 'true';
				break;
			case 'workoutsidehours' :
				$department = $dbDepartments->getDepartmentbyId("1");
				$typeProgram = 'false';				
				break;
            case 'workallhours':
            default:
                // Do not filter by hours
                break;
        }
		//echo $where;die();
		if(!empty($department))
		{
			$timeIntervals = '';
			if(!empty($department['day1_start']))
				$timeIntervals .= $department['day1_start'].",".$department['day1_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";
			if(!empty($department['day2_start']))
				$timeIntervals .= $department['day2_start'].",".$department['day2_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day3_start']))
				$timeIntervals .= $department['day3_start'].",".$department['day3_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day4_start']))
				$timeIntervals .= $department['day4_start'].",".$department['day4_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day5_start']))
				$timeIntervals .= $department['day5_start'].",".$department['day5_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day6_start']))
				$timeIntervals .= $department['day6_start'].",".$department['day6_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day7_start']))
				$timeIntervals .= $department['day7_start'].",".$department['day7_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
		}
		try {
		    $stmt = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetInternetReport ?,?,?,?,?,?,?,?,?");  
			if($type == 'top' || $type == 'export' || $type == 'cron' || $type == 'all'){
				$start = ($page-1)*$numberPerPage + 1;
				$end = ($page-1)*$numberPerPage + $numberPerPage;
			}
			if($type == 'chart'){
				$start = 0;
				$end = $page;
			}
			$params = array(":start"=> $start,
							":end"=> $end,
							":where"=>$where,
							":hourStart"=>$hourStart,
							":hourEnd"=>$hourEnd,
							":dates" => $dates['result'],
							":number" => intval($dates['number']),
							":timeIntervals" => $timeIntervals,
							":type" => $typeProgram															
							);
			if($type != 'cron'){
			    $stmt->execute($params);
			    $response = $stmt->fetchAll();
			    
				$stmt2 = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetInternetReportTotalAndCount ?,?,?,?,?,?,?,?,?");		    
			    $stmt2->execute($params);
			    $response2 = $stmt2->fetchAll();
			}
			if($type != 'top'){
			    $stmt3 = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetInternetReportCron ?,?,?,?,?,?,?,?,?");
				$stmt3->execute($params);
				$response3 = $stmt3->fetchAll();	
			}			

			//echo "<pre>";print_r($response);echo "</pre>";die();
		  }
		  catch (Zend_Db_Adapter_Exception $e) {
		    print $e->__toString();
		  }
		  catch (PDOException $e) {
		    print $e->__toString();
		  }
		  catch (Zend_Exception $e) {
		    print $e->__toString();
		}
		
		return  array(
					"response" => $response,
					"cron" => $response3,
					"totalandcount" => $response2
					);		

        /*$this->_name = "HttpAccessEvent";
        $this->_primary = "rhaeID";
        
        $query = $this->select();
        $query
            ->setIntegrityCheck(false)
            ->from(array('http' => "HttpAccessEvent"))
            ->join(array('event' => "Event"), "http.rhaeRawEventID=event.reID")
			->where("event.reID NOT IN (SELECT rieRawEventID FROM IdleEvent)");
        
        // User filters
        if (!empty($options['users'])) {
            if (is_string($options['users'])) $options['users'] = array($options['users']);
            $query->where('event.reUser IN ( ? )', $options['users']);
        }
        
        // Date filters
        if (isset($options['dateIs'])) {
            $query->where('convert(nvarchar(10), event.reStartDate, 101) = ?', $options['dateIs']);
        } else if (isset($options['dateStart']) && isset($options['dateEnd'])) {
            $query
                ->where('convert(nvarchar(10), event.reStartDate, 101) >= ?', $options['dateStart'])
                ->where('convert(nvarchar(10), event.reEndDate, 101) <= ?', $options['dateEnd']);
            switch ($options['interval']) {
                case 'specweek':
                    $days = array();
                    foreach ($options['days'] as $day) {
                        $days[] = $this->_weekDays[$day];
                    }
                    $query->where('DATEPART(weekday, event.reStartDate) IN (' . implode(',', $days) . ')');
                    break;
                case 'workweek':
                    $query->where('DATEPART(weekday, event.reStartDate) IN (2,3,4,5,6)');
                    break;
                case 'endweek':
                    $query->where('DATEPART(weekday, event.reStartDate) IN (1,7)');
                    break;
                case 'allweek':
                default:
                    // Do not filter by days
                    break;
            }
        }
        
        // Time filters
        switch($options['timeinterval']) {
            case 'workinghours':
                $query
                    ->where('convert(nvarchar(8), event.reStartDate, 8) > convert(nvarchar(8), ?, 8)', $options['hArray']['startTime'])
                    ->where('convert(nvarchar(8), event.reEndDate, 8) <= convert(nvarchar(8), ?, 8)', $options['hArray']['endTime']);
                break;
            case 'workhours':
                $query
                    ->where('convert(nvarchar(8), event.reStartDate, 8) > convert(nvarchar(8), ?, 8)', "09:00:00")
                    ->where('convert(nvarchar(8), event.reEndDate, 8) <= convert(nvarchar(8), ?, 8)', "18:00:00");
                break;
            case 'workoutsidehours':
                $query->where('((convert(nvarchar(8), event.reStartDate, 8) <= ?) OR ' .
                        '(convert(nvarchar(8), event.reEndDate, 8) > ?))', '09:00:00', '18:00:00');
                break;
            case 'workallhours':
            default:
                // Do not filter by hours
                break;
        }
        
        // Order
        $query->order(array(
                        'event.reStartDate ASC'
                    ));
        
        // Limit
        if ($limit > 0) {
            if ($offset > 0) {
                $query->limit($limit, $offset);
            } else {
                $query->limit($limit);
            }
        }

        $result = $this->fetchAll($query);
		
		//print_r($result);die();
		
        return empty($result) ? array() : $result->toArray();*/
    }

    public function getApplicationsReportDataGrid($options, $limit = 0, $offset = 0,$page = -1,$type = 'all',$numberPerPage = 100)
    {
		
    	$where = '';
		$query = '';
		$dates = '';
		$hourStart = "'00:00:00'";
    	$hourEnd = "'23:59:59'";
		$typeProgram = 'true';
		$timeIntervals = '-1';
		$dbDepartments = new Application_Table_dbDepartments();
		$response = array();
		$response2 = array();
		$response3 = array();
		$response4 = array();
		$icons = array();
		
		//echo $numberPerPage;die();
		
		if (!empty($options['users'])) {
			$where .= ' e.reUser IN ( ';
			$first = true;
			foreach($options['users'] as $user){
				if($first == false){
					$where .= ",'".$user."'";
				}else{
					$where .= "'".$user."'";
					$first = false;
				} 
			}
			$where .= ") AND ";
		}
		
		if (isset($options['dateIs'])) {
			$dates = array(
				"result" => $this->_buildDateForQuerySingle($options['dateIs']),
				"number" => 1
			);
			$where .="((convert(nvarchar(25),e.reStartDate,101)  >=  '{$options['dateIs']}'  AND
					  convert(nvarchar(25),e.reStartDate,101)  <=  '{$options['dateIs']}')  OR
					  (convert(nvarchar(25),e.reEndDate,101)  >=  '{$options['dateIs']}'  AND
					  convert(nvarchar(25),e.reEndDate,101)  <=  '{$options['dateIs']}')) 
					  ";
		}else if (isset($options['dateStart']) && isset($options['dateEnd'])) {
			$dates = $this->_buildDateForQueryMultiple($options['dateStart'],$options['dateEnd']);
			$where .= "((convert(nvarchar(25),e.reStartDate,101)  >=  '{$options['dateStart']}'  AND
					  convert(nvarchar(25),e.reStartDate,101)  <=  '{$options['dateEnd']}')  OR
					  (convert(nvarchar(25),e.reEndDate,101)  >=  '{$options['dateStart']}'  AND
					  convert(nvarchar(25),e.reEndDate,101)  <=  '{$options['dateEnd']}')) 
					  ";
            switch ($options['interval']) {
                case 'specweek':
					$days = array();
                    foreach ($options['days'] as $day) {
                        $days[] = $this->_weekDays[$day];
                    }					
					$where .= ' AND (DATEPART(weekday, e.reStartDate) IN (' . implode(',', $days) . ') OR 
					            DATEPART(weekday, e.reEndDate) IN (' . implode(',', $days) . '))';
                    break;
                case 'workweek':
                    $where .= ' AND (DATEPART(weekday, e.reStartDate) IN (2,3,4,5,6) OR 
                                DATEPART(weekday, e.reEndDate) IN (2,3,4,5,6) )';
                    break;
                case 'endweek':
                    $where .= ' AND (DATEPART(weekday, e.reStartDate) IN (1,7) OR
                    			DATEPART(weekday, e.reEndDate) IN (1,7) ) ';
                    break;
                case 'allweek':
                default:
                    // Do not filter by days
                    break;
            }
        }

   		switch($options['timeinterval']) {
            case 'workinghours':
				$where .= "  AND ((convert(nvarchar(25),e.reStartDate,8)  >=  convert(nvarchar(8), '{$options['hArray']['startTime']}', 8)  AND
					  convert(nvarchar(25),e.reStartDate,8)  <=  convert(nvarchar(8), '{$options['hArray']['endTime']}', 8))  OR
					  (convert(nvarchar(25),e.reEndDate,8)  >=  convert(nvarchar(8), '{$options['hArray']['startTime']}', 8)  AND
					  convert(nvarchar(25),e.reEndDate,8)  <=  convert(nvarchar(8), '{$options['hArray']['endTime']}', 8))) 
					  ";
				$hourStart = "'".$options['hArray']['startTime']."'";
				$hourEnd = "'".$options['hArray']['endTime']."'";	
                break;
			case 'workhours':
				$department = $dbDepartments->getDepartmentbyId("1");
				$typeProgram = 'true';
				break;
			case 'workoutsidehours' :
				$department = $dbDepartments->getDepartmentbyId("1");
				$typeProgram = 'false';				
				break;
            case 'workallhours':
            default:
                // Do not filter by hours
                break;
        }
		//print_r($where);die();
		if(!empty($department))
		{
			$timeIntervals = '';
			if(!empty($department['day1_start']))
				$timeIntervals .= $department['day1_start'].",".$department['day1_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";
			if(!empty($department['day2_start']))
				$timeIntervals .= $department['day2_start'].",".$department['day2_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day3_start']))
				$timeIntervals .= $department['day3_start'].",".$department['day3_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day4_start']))
				$timeIntervals .= $department['day4_start'].",".$department['day4_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day5_start']))
				$timeIntervals .= $department['day5_start'].",".$department['day5_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day6_start']))
				$timeIntervals .= $department['day6_start'].",".$department['day6_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day7_start']))
				$timeIntervals .= $department['day7_start'].",".$department['day7_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
		}
		try {
		    $stmt = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetApplicationReport ?,?,?,?,?,?,?,?,?");   
		    //$stmt = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetApplicationReport_SimpleFilters ?,?,?,?,?,?,?,?,?");
			if($type == 'top' || $type == 'export' || $type == 'cron' || $type == 'all'){
				$start = ($page-1)*$numberPerPage + 1;
				$end = ($page-1)*$numberPerPage + $numberPerPage;
				
				
			}
			if($type == 'chart'){
				$start = 0;
				$end = $page;
			}
			$params = array(":start" => $start,
							":end" => $end,
							":where" => $where,
							":hourStart" => $hourStart,
							":hourEnd" => $hourEnd,
							":dates" => $dates['result'],
							":number" => intval($dates['number']),	
							":timeIntervals" => $timeIntervals,
							":type" => $typeProgram
							);
							
					
			 //echo "<pre>";print_r($params);echo "</pre>";die();
			if($type != 'cron'){
			    $stmt->execute($params);
			    $response = $stmt->fetchAll();
			   	
			    $stmt2 = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetApplicationReportSumAndCount ?,?,?,?,?,?,?,?,?");
			    //$stmt2 = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetApplicationReportSumAndCount_SimpleFilters ?,?,?,?,?,?,?,?,?");
				$stmt2->execute($params);
				$response2 = $stmt2->fetchAll();
				
			}
			if($type != 'top'){
			    $stmt3 = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetApplicationReportCron ?,?,?,?,?,?,?,?,?");
			    //$stmt3 = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetApplicationReportCron_SimpleFilters ?,?,?,?,?,?,?,?,?");
				$stmt3->execute($params);
				$response3 = $stmt3->fetchAll();
			}
			//echo "<pre>";print_r($response3);echo "</pre>";die();
		  }
		  catch (Zend_Db_Adapter_Exception $e) {
		    print $e->__toString();
		  }
		  catch (PDOException $e) {
		    print $e->__toString();
		  }
		  catch (Zend_Exception $e) {
		    print $e->__toString();
		}
		
		return array(
					"response" => $response,
					"cron" => $response3,
					"totalandcount" => $response2
					);
		
		//print_r($response);die();
		
        /*$this->_name = "Event";
        $this->_primary = "reID";
        
        $query = $this->select();
        $query
            ->setIntegrityCheck(false)
            ->from(array('event' => "Event"))
            //->joinLeft(array('rIdle' => "IdleEvent"), "event.reID=rIdle.rieRawEventID")
			 ->where("event.reID NOT IN (SELECT rieRawEventID FROM IdleEvent)");
        
        // User filters
        if (!empty($options['users'])) {
            if (is_string($options['users'])) $options['users'] = array($options['users']);
            $query->where('event.reUser IN ( ? )', $options['users']);
        }
       // echo "<pre>";print_r($options);echo "</pre>";die();
        // Date filters
        if (isset($options['dateIs'])) {
            $query->where('convert(nvarchar(10), reStartDate, 101) = ?', $options['dateIs']);
        } else if (isset($options['dateStart']) && isset($options['dateEnd'])) {
            $query
                ->where('convert(nvarchar(10), reStartDate, 101) >= ?', $options['dateStart'])
                ->where('convert(nvarchar(10), reEndDate, 101) <= ?', $options['dateEnd']);
            switch ($options['interval']) {
                case 'specweek':
                    $days = array();
                    foreach ($options['days'] as $day) {
                        $days[] = $this->_weekDays[$day];
                    }
                    $query->where('DATEPART(weekday, reStartDate) IN (' . implode(',', $days) . ')');
                    break;
                case 'workweek':
                    $query->where('DATEPART(weekday, reStartDate) IN (2,3,4,5,6)');
                    break;
                case 'endweek':
                    $query->where('DATEPART(weekday, reStartDate) IN (1,7)');
                    break;
                case 'allweek':
                default:
                    // Do not filter by days
                    break;
            }
        }
        
        // Time filters
        switch($options['timeinterval']) {
            case 'workinghours':
                $query
                    ->where('convert(nvarchar(8), reStartDate, 8) > convert(nvarchar(8), ?, 8)', $options['hArray']['startTime'])
                    ->where('convert(nvarchar(8), reEndDate, 8) <= convert(nvarchar(8), ?, 8)', $options['hArray']['endTime']);
                break;
            case 'workhours':
                $query
                    ->where('convert(nvarchar(8), reStartDate, 8) > convert(nvarchar(8), ?, 8)', "09:00:00")
                    ->where('convert(nvarchar(8), reEndDate, 8) <= convert(nvarchar(8), ?, 8)', "18:00:00");
                break;
            case 'workoutsidehours':
                $query->where('((convert(nvarchar(8), reStartDate, 8) <= ?) OR ' .
                        '(convert(nvarchar(8), reEndDate, 8) > ?))', '09:00:00', '18:00:00');
                break;
            case 'workallhours':
            default:
                // Do not filter by hours
                break;
        }
        
        // Order
        $query->order(array(
                        'event.reStartDate ASC'
                    ));
        
        // Limit
        if ($limit > 0) {
            if ($offset > 0) {
                $query->limit($limit, $offset);
            } else {
                $query->limit($limit);
            }
        }

        $result = $this->fetchAll($query);
        return empty($result) ? array() : $result->toArray();*/
    }
    
    
    public function getFilesReportDataGrid($options, $limit = 0, $offset = 0,$page = 1,$type = 'top',$numberPerPage = 100)
    {
    	
    	$where = '';
		$query = '';
		$hourStart = "'00:00:00'";
    	$hourEnd = "'23:59:59'";
		$months = '';
		$response2 = array();
		$typeProgram = 'true';
		$timeIntervals = '-1';
		$dbDepartments = new Application_Table_dbDepartments();		
		//echo $numberPerPage;die();
		if (!empty($options['users'])) {
			$where .= ' e.reUser IN ( ';
			$first = true;
			foreach($options['users'] as $user){
				if($first == false){
					$where .= ",'".$user."'";
				}else{
					$where .= "'".$user."'";
					$first = false;
				} 
			}
			$where .= ") AND ";
		}
		
		if (isset($options['dateIs'])) {
			
			$where .="((convert(nvarchar(25),e.reStartDate,101)  >=  '{$options['dateIs']}'  AND
					  convert(nvarchar(25),e.reStartDate,101)  <=  '{$options['dateIs']}')  OR
					  (convert(nvarchar(25),e.reEndDate,101)  >=  '{$options['dateIs']}'  AND
					  convert(nvarchar(25),e.reEndDate,101)  <=  '{$options['dateIs']}')) 
					  ";
					  
			$dates = array(
				"result" => $this->_buildDateForQuerySingle($options['dateIs']),
				"number" => 1
			);		
		}else if (isset($options['dateStart']) && isset($options['dateEnd'])) {
			$dates = $this->_buildDateForQueryMultiple($options['dateStart'],$options['dateEnd']);
			$where .= "((convert(nvarchar(25),e.reStartDate,101)  >=  '{$options['dateStart']}'  AND
					  convert(nvarchar(25),e.reStartDate,101)  <=  '{$options['dateEnd']}')  OR
					  (convert(nvarchar(25),e.reEndDate,101)  >=  '{$options['dateStart']}'  AND
					  convert(nvarchar(25),e.reEndDate,101)  <=  '{$options['dateEnd']}')) 
					  ";
            switch ($options['interval']) {
            	
                case 'specweek':
					$days = array();
                    foreach ($options['days'] as $day) {
                        $days[] = $this->_weekDays[$day];
                    }					
					$where .= ' AND (DATEPART(weekday, e.reStartDate) IN (' . implode(',', $days) . ') OR 
					            DATEPART(weekday, e.reEndDate) IN (' . implode(',', $days) . '))';
                    break;
                case 'workweek':
                    $where .= ' AND (DATEPART(weekday, e.reStartDate) IN (2,3,4,5,6) OR 
                                DATEPART(weekday, e.reEndDate) IN (2,3,4,5,6) )';
                    break;
                case 'endweek':
                    $where .= ' AND (DATEPART(weekday, e.reStartDate) IN (1,7) OR
                    			DATEPART(weekday, e.reEndDate) IN (1,7) ) ';
                    break;
                case 'allweek':
                default:
                    // Do not filter by days
                    break;
            }
        }

   		switch($options['timeinterval']) {
            case 'workinghours':
				$where .= "  AND ((convert(nvarchar(25),e.reStartDate,8)  >=  convert(nvarchar(8), '{$options['hArray']['startTime']}', 8)  AND
					  convert(nvarchar(25),e.reStartDate,8)  <=  convert(nvarchar(8), '{$options['hArray']['endTime']}', 8))  OR
					  (convert(nvarchar(25),e.reEndDate,8)  >=  convert(nvarchar(8), '{$options['hArray']['startTime']}', 8)  AND
					  convert(nvarchar(25),e.reEndDate,8)  <=  convert(nvarchar(8), '{$options['hArray']['endTime']}', 8))) 
					  ";
				$hourStart = "'".$options['hArray']['startTime']."'";
				$hourEnd = "'".$options['hArray']['endTime']."'";					  
                break;
			case 'workhours':
				$department = $dbDepartments->getDepartmentbyId("1");
				$typeProgram = 'true';
				break;
			case 'workoutsidehours' :
				$department = $dbDepartments->getDepartmentbyId("1");
				$typeProgram = 'false';				
				break;
            case 'workallhours':
            default:
                // Do not filter by hours
                break;
        }
		
		if(!empty($department))
		{
			$timeIntervals = '';
			if(!empty($department['day1_start']))
				$timeIntervals .= $department['day1_start'].",".$department['day1_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";
			if(!empty($department['day2_start']))
				$timeIntervals .= $department['day2_start'].",".$department['day2_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day3_start']))
				$timeIntervals .= $department['day3_start'].",".$department['day3_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day4_start']))
				$timeIntervals .= $department['day4_start'].",".$department['day4_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day5_start']))
				$timeIntervals .= $department['day5_start'].",".$department['day5_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day6_start']))
				$timeIntervals .= $department['day6_start'].",".$department['day6_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day7_start']))
				$timeIntervals .= $department['day7_start'].",".$department['day7_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
		}
		
		$extensions = empty($options['extensions']) ? array() : explode(',', $options['extensions']);
		
        if (!empty($extensions)) {
        	$where .= " AND ( ";
            foreach ($extensions as $index => $ext) {
               	$where .= " f.rfaeFileName LIKE '%{$ext}'";
               	if(count($extensions) -1 != $index )
               		$where .= " OR ";
            }
            $where .= "  ) ";
            
        }
		//print_r($where);die();
		try {
		    $stmt = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetFileReport ?,?,?,?,?,?,?,?,?");  
			if($type == 'top' || $type == 'export'){
				$start = ($page-1)*$numberPerPage + 1;
				$end = ($page-1)*$numberPerPage + $numberPerPage;
			}
			if($type == 'chart'){
				$start = 0;
				$end = $page;
			}
			$params = array(":start"=> $start,
							":end"=> $end,
							":where"=>$where,
							":hourStart"=>$hourStart,
							":hourEnd"=>$hourEnd,
							":dates" => $dates['result'],
							":number" => intval($dates['number']),
							":timeIntervals" => $timeIntervals,
							":type" => $typeProgram							
							);
			
		    $stmt->execute($params);
		    $response = $stmt->fetchAll();
		    
		    //print_r($response);die();
			
			//$stmt2 = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetChatReportTotalAndCount ?,?,?,?,?,?,?,?");
			//$stmt2->execute($params);  
			//$response2 = $stmt2->fetchAll();			
		
		  }
		  catch (Zend_Db_Adapter_Exception $e) {
		    print $e->__toString();
		  }
		  catch (PDOException $e) {
		    print $e->__toString();
		  }
		  catch (Zend_Exception $e) {
		    print $e->__toString();
		}
		
		return empty($response) ? array() : array(
											"response" => $response,
											"totalandcount" => $response2
											);		
        
        /*$this->_name = "FileAccessEvent";
        $this->_primary = "rfaeID";
        
		
        $query = $this->select();
        $query
            ->setIntegrityCheck(false)
            ->from(array('rfile' => "FileAccessEvent"))
            ->join(array('event' => "Event"), "rfile.rfaeRawEventID=event.reID");
        
        // User filters
        if (!empty($options['users'])) {
            if (is_string($options['users'])) $options['users'] = array($options['users']);
            $query->where('event.reUser IN ( ? )', $options['users']);
        }
        
        // Date filters
        if (isset($options['dateIs'])) {
            $query->where('convert(nvarchar(10), reStartDate, 101) = ?', $options['dateIs']);
        } else if (isset($options['dateStart']) && isset($options['dateEnd'])) {
            $query
                ->where('convert(nvarchar(10), reStartDate, 101) >= ?', $options['dateStart'])
                ->where('convert(nvarchar(10), reEndDate, 101) <= ?', $options['dateEnd']);
            switch ($options['interval']) {
                case 'specweek':
                    $days = array();
                    foreach ($options['days'] as $day) {
                        $days[] = $this->_weekDays[$day];
                    }
                    $query->where('DATEPART(weekday, reStartDate) IN (' . implode(',', $days) . ')');
                    break;
                case 'workweek':
                    $query->where('DATEPART(weekday, reStartDate) IN (2,3,4,5,6)');
                    break;
                case 'endweek':
                    $query->where('DATEPART(weekday, reStartDate) IN (1,7)');
                    break;
                case 'allweek':
                default:
                    // Do not filter by days
                    break;
            }
        }
        
        // Time filters
        switch($options['timeinterval']) {
            case 'workinghours':
                $query
                    ->where('convert(nvarchar(8), reStartDate, 8) > convert(nvarchar(8), ?, 8)', $options['hArray']['startTime'])
                    ->where('convert(nvarchar(8), reEndDate, 8) <= convert(nvarchar(8), ?, 8)', $options['hArray']['endTime']);
                break;
            case 'workhours':
                $query
                    ->where('convert(nvarchar(8), reStartDate, 8) > convert(nvarchar(8), ?, 8)', "09:00:00")
                    ->where('convert(nvarchar(8), reEndDate, 8) <= convert(nvarchar(8), ?, 8)', "18:00:00");
                break;
            case 'workoutsidehours':
                $query->where('((convert(nvarchar(8), reStartDate, 8) <= ?) OR ' .
                        '(convert(nvarchar(8), reEndDate, 8) > ?))', '09:00:00', '18:00:00');
                break;
            case 'workallhours':
            default:
                // Do not filter by hours
                break;
        }
        
        // Order
        $query->order(array(
                        'event.reStartDate ASC'
                    ));
        
        // Limit
        if ($limit > 0) {
            if ($offset > 0) {
                $query->limit($limit, $offset);
            } else {
                $query->limit($limit);
            }
        }

        $result = $this->fetchAll($query);
        return empty($result) ? array() : $result->toArray();*/
    }

    public function getTopReportDataGrid($options, $limit = 0, $offset = 0,$page = 1,$type = 'top',$numberPerPage = 100)
    {
    	
    	$where = '';
		$query = '';
		$hourStart = "'00:00:00'";
    	$hourEnd = "'23:59:59'";
		$months = '';
		
		$response1 = array();
		$response2 = array();
		$response3 = array();
		$response4 = array();
		$response5 = array();	
		$response6 = array();
			
		$typeProgram = 'true';
		$timeIntervals = '-1';
		$users = '-1';
		$dbDepartments = new Application_Table_dbDepartments();			
		//echo $numberPerPage;die();
		if (!empty($options['users'])) {
			$where .= ' e.reUser IN ( ';
			$users = ' u.name IN ( ';
			$first = true;
			foreach($options['users'] as $user){
				if($first == false){
					$where .= ",'".$user."'";
					$users .= ",'".$user."'";
				}else{
					$where .= "'".$user."'";
					$users .= "'".$user."'";
					$first = false;
				} 
			}
			$where .= ") AND ";
			$users .= ") ";
		}
		if (isset($options['dateIs'])) {
			
			$where .="((convert(nvarchar(25),e.reStartDate,101)  >=  '{$options['dateIs']}'  AND
					  convert(nvarchar(25),e.reStartDate,101)  <=  '{$options['dateIs']}')  OR
					  (convert(nvarchar(25),e.reEndDate,101)  >=  '{$options['dateIs']}'  AND
					  convert(nvarchar(25),e.reEndDate,101)  <=  '{$options['dateIs']}')) 
					  ";
					  
			$dates = array(
				"result" => $this->_buildDateForQuerySingle($options['dateIs']),
				"number" => 1
			);		
		}else if (isset($options['dateStart']) && isset($options['dateEnd'])) {
			$dates = $this->_buildDateForQueryMultiple($options['dateStart'],$options['dateEnd']);
			$where .= "((convert(nvarchar(25),e.reStartDate,101)  >=  '{$options['dateStart']}'  AND
					  convert(nvarchar(25),e.reStartDate,101)  <=  '{$options['dateEnd']}')  OR
					  (convert(nvarchar(25),e.reEndDate,101)  >=  '{$options['dateStart']}'  AND
					  convert(nvarchar(25),e.reEndDate,101)  <=  '{$options['dateEnd']}')) 
					  ";
            switch ($options['interval']) {
            	
                case 'specweek':
					$days = array();
                    foreach ($options['days'] as $day) {
                        $days[] = $this->_weekDays[$day];
                    }					
					$where .= ' AND (DATEPART(weekday, e.reStartDate) IN (' . implode(',', $days) . ') OR 
					            DATEPART(weekday, e.reEndDate) IN (' . implode(',', $days) . '))';
                    break;
                case 'workweek':
                    $where .= ' AND (DATEPART(weekday, e.reStartDate) IN (2,3,4,5,6) OR 
                                DATEPART(weekday, e.reEndDate) IN (2,3,4,5,6) )';
                    break;
                case 'endweek':
                    $where .= ' AND (DATEPART(weekday, e.reStartDate) IN (1,7) OR
                    			DATEPART(weekday, e.reEndDate) IN (1,7) ) ';
                    break;
                case 'allweek':
                default:
                    // Do not filter by days
                    break;
            }
        }

   		switch($options['timeinterval']) {
            case 'workinghours':
				$where .= "  AND ((convert(nvarchar(25),e.reStartDate,8)  >=  convert(nvarchar(8), '{$options['hArray']['startTime']}', 8)  AND
					  convert(nvarchar(25),e.reStartDate,8)  <=  convert(nvarchar(8), '{$options['hArray']['endTime']}', 8))  OR
					  (convert(nvarchar(25),e.reEndDate,8)  >=  convert(nvarchar(8), '{$options['hArray']['startTime']}', 8)  AND
					  convert(nvarchar(25),e.reEndDate,8)  <=  convert(nvarchar(8), '{$options['hArray']['endTime']}', 8))) 
					  ";
				$hourStart = "'".$options['hArray']['startTime']."'";
				$hourEnd = "'".$options['hArray']['endTime']."'";					  
               break;
			case 'workhours':
				$department = $dbDepartments->getDepartmentbyId("1");
				$typeProgram = 'true';
				break;
			case 'workoutsidehours' :
				$department = $dbDepartments->getDepartmentbyId("1");
				$typeProgram = 'false';				
				break;
            case 'workallhours':
            default:
                // Do not filter by hours
                break;
        }
		
		if(!empty($department))
		{
			$timeIntervals = '';
			if(!empty($department['day1_start']))
				$timeIntervals .= $department['day1_start'].",".$department['day1_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";
			if(!empty($department['day2_start']))
				$timeIntervals .= $department['day2_start'].",".$department['day2_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day3_start']))
				$timeIntervals .= $department['day3_start'].",".$department['day3_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day4_start']))
				$timeIntervals .= $department['day4_start'].",".$department['day4_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day5_start']))
				$timeIntervals .= $department['day5_start'].",".$department['day5_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day6_start']))
				$timeIntervals .= $department['day6_start'].",".$department['day6_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day7_start']))
				$timeIntervals .= $department['day7_start'].",".$department['day7_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
		}
		//print_r($where);die();
		try {
		    $stmt = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetTopReport ?,?,?,?,?,?,?,?,?,?,?");  
		    $stmt2 = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetInternetSitesProductivity ?,?,?,?,?,?,?,?,?,?");
		    $stmt3 = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetInternetReportTotalAndCount ?,?,?,?,?,?,?,?,?");
			$start = ($page-1)*$numberPerPage + 1;
			$end = ($page-1)*$numberPerPage + $numberPerPage;
		
			$params1 = array(":start"=> $start,
							":end"=> $end,
							":where"=>$where,
							":hourStart"=>$hourStart,
							":hourEnd"=>$hourEnd,
							":dates" => $dates['result'],
							":number" => intval($dates['number']),
							":type" => 'productive',
							":timeIntervals" => $timeIntervals,
							":typeProgram" => $typeProgram,
							":users" => $users							
							);
			$params2 = array(":start"=> $start,
							":end"=> $end,
							":where"=>$where,
							":hourStart"=>$hourStart,
							":hourEnd"=>$hourEnd,
							":dates" => $dates['result'],
							":number" => intval($dates['number']),
							":type" => 'unproductive',
							":timeIntervals" => $timeIntervals,
							":typeProgram" => $typeProgram,
							":users" => $users											
							);
			$params3 = array(":start"=> $start,
							":end"=> $end,
							":where"=>$where,
							":hourStart"=>$hourStart,
							":hourEnd"=>$hourEnd,
							":dates" => $dates['result'],
							":number" => intval($dates['number']),
							":type" => 'total',
							":timeIntervals" => $timeIntervals,
							":typeProgram" => $typeProgram,
							":users" => $users											
							);
			$params4 = array(":start"=> $start,
							":end"=> $end,
							":where"=>$where,
							":hourStart"=>$hourStart,
							":hourEnd"=>$hourEnd,
							":dates" => $dates['result'],
							":number" => intval($dates['number']),
							":timeIntervals" => $timeIntervals,
							":type" => $typeProgram,
							":productivity" => 'productive'										
							);	
			$params5 = array(":start"=> $start,
							":end"=> $end,
							":where"=>$where,
							":hourStart"=>$hourStart,
							":hourEnd"=>$hourEnd,
							":dates" => $dates['result'],
							":number" => intval($dates['number']),
							":timeIntervals" => $timeIntervals,
							":type" => $typeProgram,
							":productivity" => 'unproductive'										
							);			
			$params6 = array(":start"=> $start,
							":end"=> $end,
							":where"=>$where,
							":hourStart"=>$hourStart,
							":hourEnd"=>$hourEnd,
							":dates" => $dates['result'],
							":number" => intval($dates['number']),
							":timeIntervals" => $timeIntervals,
							":typeProgram" => $typeProgram,						
							);																			
			//print_r($params1);print_r($params2);print_r($params3);die();									
		    $stmt->execute($params1);
		    $response1 = $stmt->fetchAll();
			
		    $stmt->execute($params2);
		    $response2 = $stmt->fetchAll();
			
		    $stmt->execute($params3);
		    $response3 = $stmt->fetchAll();
		    $i=0;
		   
			foreach($response1 as $item)
			{
				$params4[':where'] = $this->_buildWhereClause($options, $item['reUser']);
				$params5[':where'] = $this->_buildWhereClause($options, $item['reUser']);
				$params6[':where'] = $this->_buildWhereClause($options, $item['reUser']);
				
			    $stmt2->execute($params4);
			    $response4 = $stmt2->fetchAll();
	
			    $stmt2->execute($params5);
			    $response5 = $stmt2->fetchAll();	
	
			    $stmt3->execute($params6);
			    $response6 = $stmt3->fetchAll();

			    $response1[$i]['Timp']+= $response4[0]['Timp'];
			    $response2[$i]['Timp']+= $response5[0]['Timp'];
			    $response3[$i]['Timp']+= $response6[0]['TimpTotal'];
			    //print_r($response1);
			     $i++;

			}		    
			//die();	  
			//print_r($response1);die();	    
			//print_r($response1);print_r($response2);print_r($response3);print_r($response4);print_r($response5);print_r($response6);die();
		    //print_r($response1);die();
		  }
		  catch (Zend_Db_Adapter_Exception $e) {
		    print $e->__toString();
		  }
		  catch (PDOException $e) {
		    print $e->__toString();
		  }
		  catch (Zend_Exception $e) {
		    print $e->__toString();
		}
		
		return array(
					"productive" => $response1,
					"unproductive" => $response2,
					"total" => $response3
					);		

		
		
/*        $this->_name = "Event";
        $this->_primary = "reID";
        
        $query = $this->select();
        $query
            ->setIntegrityCheck(false)
            ->from(array('event' => "Event"))
            ->joinLeft(array('rIdle' => "IdleEvent"), "event.reID=rIdle.rieRawEventID");

       
        // User filters
        if (!empty($options['users'])) {
            if (is_string($options['users'])) $options['users'] = array($options['users']);
            $query->where('event.reUser IN ( ? )', $options['users']);
        }
        
        // Date filters
        if (isset($options['dateIs'])) {
            $query->where('convert(nvarchar(10), reStartDate, 101) = ?', $options['dateIs']);
        } else if (isset($options['dateStart']) && isset($options['dateEnd'])) {
            $query
                ->where('convert(nvarchar(10), reStartDate, 101) >= ?', $options['dateStart'])
                ->where('convert(nvarchar(10), reEndDate, 101) < ?', $options['dateEnd']);
            switch ($options['interval']) {
                case 'specweek':
                    $days = array();
                    foreach ($options['days'] as $day) {
                        $days[] = $this->_weekDays[$day];
                    }
                    $query->where('DATEPART(weekday, reStartDate) IN (' . implode(',', $days) . ')');
                    break;
                case 'workweek':
                    $query->where('DATEPART(weekday, reStartDate) IN (2,3,4,5,6)');
                    break;
                case 'endweek':
                    $query->where('DATEPART(weekday, reStartDate) IN (1,7)');
                    break;
                case 'allweek':
                default:
                    // Do not filter by days
                    break;
            }
        }
        
        // Time filters
        switch($options['timeinterval']) {
            case 'workinghours':
                $query
                    ->where('convert(nvarchar(8), reStartDate, 8) > convert(nvarchar(8), ?, 8)', $options['hArray']['startTime'])
                    ->where('convert(nvarchar(8), reEndDate, 8) <= convert(nvarchar(8), ?, 8)', $options['hArray']['endTime']);
                break;
            case 'workhours':
                $query
                    ->where('convert(nvarchar(8), reStartDate, 8) > convert(nvarchar(8), ?, 8)', "09:00:00")
                    ->where('convert(nvarchar(8), reEndDate, 8) <= convert(nvarchar(8), ?, 8)', "18:00:00");
                break;
            case 'workoutsidehours':
                $query->where('((convert(nvarchar(8), reStartDate, 8) <= ?) OR ' .
                        '(convert(nvarchar(8), reEndDate, 8) > ?))', '09:00:00', '18:00:00');
                break;
            case 'workallhours':
            default:
                // Do not filter by hours
                break;
        }
        
        // Order
        $query->order(array(
                        'reStartDate ASC'
                    ));
        
        // Limit
        if ($limit > 0) {
            if ($offset > 0) {
                $query->limit($limit, $offset);
            } else {
                $query->limit($limit);
            }
        }

        $result = $this->fetchAll($query);
        return empty($result) ? array() : $result->toArray();*/
    }
    
    public function getActivityReportDataGrid($options, $limit = 0, $offset = 0,$page = 1,$numberPerPage = 100,$type = 'all')
    {
    	
   		$where = '';
		$query = '';
		$hourStart = "'00:00:00'";
    	$hourEnd = "'23:59:59'";
		$months = '';
		$response = array();
		$response2 = array();
		$typeProgram = 'true';
		$timeIntervals = '-1';
		$dbDepartments = new Application_Table_dbDepartments();			
		//echo $numberPerPage;die();
		if (!empty($options['users'])) {
			$where .= ' e.reUser IN ( ';
			$first = true;
			foreach($options['users'] as $user){
				if($first == false){
					$where .= ",'".$user."'";
				}else{
					$where .= "'".$user."'";
					$first = false;
				} 
			}
			$where .= ") AND ";
		}
		
		if (isset($options['dateIs'])) {
			
			$where .="((convert(nvarchar(25),e.reStartDate,101)  >=  '{$options['dateIs']}'  AND
					  convert(nvarchar(25),e.reStartDate,101)  <=  '{$options['dateIs']}')  OR
					  (convert(nvarchar(25),e.reEndDate,101)  >=  '{$options['dateIs']}'  AND
					  convert(nvarchar(25),e.reEndDate,101)  <=  '{$options['dateIs']}')) 
					  ";
					  
			$dates = array(
				"result" => $this->_buildDateForQuerySingle($options['dateIs']),
				"number" => 1
			);		
		}else if (isset($options['dateStart']) && isset($options['dateEnd'])) {
			$dates = $this->_buildDateForQueryMultiple($options['dateStart'],$options['dateEnd']);
			$where .= "((convert(nvarchar(25),e.reStartDate,101)  >=  '{$options['dateStart']}'  AND
					  convert(nvarchar(25),e.reStartDate,101)  <=  '{$options['dateEnd']}')  OR
					  (convert(nvarchar(25),e.reEndDate,101)  >=  '{$options['dateStart']}'  AND
					  convert(nvarchar(25),e.reEndDate,101)  <=  '{$options['dateEnd']}')) 
					  ";
            switch ($options['interval']) {
            	
                case 'specweek':
					$days = array();
                    foreach ($options['days'] as $day) {
                        $days[] = $this->_weekDays[$day];
                    }					
					$where .= ' AND (DATEPART(weekday, e.reStartDate) IN (' . implode(',', $days) . ') OR 
					            DATEPART(weekday, e.reEndDate) IN (' . implode(',', $days) . '))';
                    break;
                case 'workweek':
                    $where .= ' AND (DATEPART(weekday, e.reStartDate) IN (2,3,4,5,6) OR 
                                DATEPART(weekday, e.reEndDate) IN (2,3,4,5,6) )';
                    break;
                case 'endweek':
                    $where .= ' AND (DATEPART(weekday, e.reStartDate) IN (1,7) OR
                    			DATEPART(weekday, e.reEndDate) IN (1,7) ) ';
                    break;
                case 'allweek':
                	                	$where .= ' AND (DATEPART(weekday, e.reStartDate) IN (1,2,3,4,5,6,7) OR 
                                DATEPART(weekday, e.reEndDate) IN (1,2,3,4,5,6,7) )';
                    break;
                default:
                    // Do not filter by days
                    break;
            }
        }

   		switch($options['timeinterval']) {
            case 'workinghours':
				$where .= "  AND ((convert(nvarchar(25),e.reStartDate,8)  >=  convert(nvarchar(25), '{$options['hArray']['startTime']}', 8)  AND
					  convert(nvarchar(25),e.reStartDate,8)  <=  convert(nvarchar(25), '{$options['hArray']['endTime']}', 8))  AND
					  (convert(nvarchar(25),e.reEndDate,8)  >=  convert(nvarchar(25), '{$options['hArray']['startTime']}', 8)  AND
					  convert(nvarchar(25),e.reEndDate,8)  <=  convert(nvarchar(25), '{$options['hArray']['endTime']}', 8))) 
					  ";
				$hourStart = "'".$options['hArray']['startTime']."'";
				$hourEnd = "'".$options['hArray']['endTime']."'";					  
               break;
			case 'workhours':
				$department = $dbDepartments->getDepartmentbyId("1");
				$typeProgram = 'true';
				break;
			case 'workoutsidehours' :
				$department = $dbDepartments->getDepartmentbyId("1");
				$typeProgram = 'false';				
				break;
            case 'workallhours':
            default:
                // Do not filter by hours
                break;
        }
		
		if(!empty($department))
		{
			$timeIntervals = '';
			if(!empty($department['day1_start']))
				$timeIntervals .= $department['day1_start'].",".$department['day1_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";
			if(!empty($department['day2_start']))
				$timeIntervals .= $department['day2_start'].",".$department['day2_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day3_start']))
				$timeIntervals .= $department['day3_start'].",".$department['day3_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day4_start']))
				$timeIntervals .= $department['day4_start'].",".$department['day4_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day5_start']))
				$timeIntervals .= $department['day5_start'].",".$department['day5_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day6_start']))
				$timeIntervals .= $department['day6_start'].",".$department['day6_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day7_start']))
				$timeIntervals .= $department['day7_start'].",".$department['day7_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
		}
		
		//print_r($where);die();
		try {
		    $stmt = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetActivityReport ?,?,?,?,?,?,?,?,?");  
		    $stmt2 = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetActivityReportGraph ?,?,?,?,?,?,?,?,?");
		    $stmt3 = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetInternetReportTotalAndCount ?,?,?,?,?,?,?,?,?"); 
		    
			$start = ($page-1)*$numberPerPage + 1;
			$end = ($page-1)*$numberPerPage + $numberPerPage;
			$params = array(":start"=> $start,
							":end"=> $end,
							":where"=>$where,
							":hourStart"=>$hourStart,
							":hourEnd"=>$hourEnd,
							":dates" => $dates['result'],
							":number" => intval($dates['number']),
							":timeIntervals" => $timeIntervals,
							":type" => $typeProgram							
							);			
			$temp_time = array();
							
		    $stmt->execute($params);
		    $response = $stmt->fetchAll();

		    $stmt2->execute($params);
		    $response2 = $stmt2->fetchAll();
			//print_r($params[':where']);die();
		    $i = 0;
		    foreach($response as $item)
		    {
		    	$temp_date = explode("/", $item['Data']);
		    	if(empty($options['dateIs']))
		    	{
		    		$options['dateStart'] = $temp_date[1]."/".$temp_date[0]."/".$temp_date[2];
		    		$options['dateEnd'] = $temp_date[1]."/".$temp_date[0]."/".$temp_date[2];
		    	}
				$params[':where'] = $this->_buildWhereClause($options, $item['Utilizator']);
				
			    $stmt3->execute($params);
			    $response3 = $stmt3->fetchAll();	
				
			    $response[$i]['TimpTotal']+= $response3[0]['TimpTotal'];
			    if(empty($temp_time[$item['Data']]))
			    	$temp_time[$item['Data']] = $response3[0]['TimpTotal'];
			    else
			   		$temp_time[$item['Data']]+= $response3[0]['TimpTotal'];
			    //$response2[$i]['TimpTotal']+= $response3[0]['TimpTotal'];
			    //print_r($response1);
			     $i++;		    	
		    }
	    	
		    $i=0;
		    foreach($response2 as $day)
		    {
		    	if(!empty($temp_time[$day['Data']]))
		    		$response2[$i]['TimpTotal']+= $temp_time[$day['Data']];
		    	$i++;
		    }
		  }
		  catch (Zend_Db_Adapter_Exception $e) {
		    print $e->__toString();
		  }
		  catch (PDOException $e) {
		    print $e->__toString();
		  }
		  catch (Zend_Exception $e) {
		    print $e->__toString();
		}
		
		return array(
					"response" => $response,
					"graph" => $response2
					);		
		
/*      $this->_name = "Event";
        $this->_primary = "reID";
        
        $query = $this->select();
        $query
            ->setIntegrityCheck(false)
            ->from(array('event' => "Event"))
            ->joinLeft(array('rIdle' => "IdleEvent"), "event.reID=rIdle.rieRawEventID");
        
        // User filters
        if (!empty($options['users'])) {
            if (is_string($options['users'])) $options['users'] = array($options['users']);
            $query->where('event.reUser IN ( ? )', $options['users']);
        }
        
        // Date filters
        if (isset($options['dateIs'])) {
            $query->where('convert(nvarchar(10), reStartDate, 101) = ?', $options['dateIs']);
        } else if (isset($options['dateStart']) && isset($options['dateEnd'])) {
            $query
                ->where('convert(nvarchar(10), reStartDate, 101) >= ?', $options['dateStart'])
                ->where('convert(nvarchar(10), reEndDate, 101) <= ?', $options['dateEnd']);
            switch ($options['interval']) {
                case 'specweek':
                    $days = array();
                    foreach ($options['days'] as $day) {
                        $days[] = $this->_weekDays[$day];
                    }
                    $query->where('DATEPART(weekday, reStartDate) IN (' . implode(',', $days) . ')');
                    break;
                case 'workweek':
                    $query->where('DATEPART(weekday, reStartDate) IN (2,3,4,5,6)');
                    break;
                case 'endweek':
                    $query->where('DATEPART(weekday, reStartDate) IN (1,7)');
                    break;
                case 'allweek':
                default:
                    // Do not filter by days
                    break;
            }
        }
        
        // Time filters
        switch($options['timeinterval']) {
            case 'workinghours':
                $query
                    ->where('convert(nvarchar(8), reStartDate, 8) > convert(nvarchar(8), ?, 8)', $options['hArray']['startTime'])
                    ->where('convert(nvarchar(8), reEndDate, 8) <= convert(nvarchar(8), ?, 8)', $options['hArray']['endTime']);
                break;
            case 'workhours':
                $query
                    ->where('convert(nvarchar(8), reStartDate, 8) > convert(nvarchar(8), ?, 8)', "09:00:00")
                    ->where('convert(nvarchar(8), reEndDate, 8) <= convert(nvarchar(8), ?, 8)', "18:00:00");
                break;
            case 'workoutsidehours':
                $query->where('((convert(nvarchar(8), reStartDate, 8) <= ?) OR ' .
                        '(convert(nvarchar(8), reEndDate, 8) > ?))', '09:00:00', '18:00:00');
                break;
            case 'workallhours':
            default:
                // Do not filter by hours
                break;
        }
        
        // Order
        $query->order(array(
                        'reStartDate ASC'
                    ));
        
        // Limit
        if ($limit > 0) {
            if ($offset > 0) {
                $query->limit($limit, $offset);
            } else {
                $query->limit($limit);
            }
        }

        $result = $this->fetchAll($query);
        return empty($result) ? array() : $result->toArray();*/
    }


	public function getActivityReportDataGridPause($user, $day)
	{
		
		$month = substr($day,3,2);
		$year = substr ($day,6,4);
		
		$table = "_".$year."_".$month;
		
		try {
		    $stmt = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetActivityReportPause ?,?,?");  
			
			$params = array(":user"=> $user,
							":day"=> $day,
							":table"=>$table,						
							);
									
		    $stmt->execute($params);
		    $response = $stmt->fetchAll();

			//print_r($response);die();
		  }
		  catch (Zend_Db_Adapter_Exception $e) {
		    print $e->__toString();
		  }
		  catch (PDOException $e) {
		    print $e->__toString();
		  }
		  catch (Zend_Exception $e) {
		    print $e->__toString();
		}		
		  
		 return $response;
		
	}
	
	public function getActivityReportDataGridUpTime($user, $day)
	{
		
		$month = substr($day,3,2);
		$year = substr ($day,6,4);
		
		$table = "_".$year."_".$month;
		
		try {
		    $stmt = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetComputerEvents ?,?,?");  
			
			$params = array(":user"=> $user,
							":day"=> $day,
							":table"=>$table,						
							);
									
		    $stmt->execute($params);
		    $response = $stmt->fetchAll();

			//print_r($response);die();
		  }
		  catch (Zend_Db_Adapter_Exception $e) {
		    print $e->__toString();
		  }
		  catch (PDOException $e) {
		    print $e->__toString();
		  }
		  catch (Zend_Exception $e) {
		    print $e->__toString();
		}		
		  
		 return $response;
		
	}	
    
    // @TODO: Implement this
    public function getRoiReportDataGrid($options, $limit = 0, $offset = 0,$page = 1,$type = 'top',$numberPerPage = 100)
    {
    	
    	$where = '';
		$query = '';
		$hourStart = "'00:00:00'";
    	$hourEnd = "'23:59:59'";
		$months = '';
		$response2 = array();
		//echo $numberPerPage;die();
		if (!empty($options['users'])) {
			$where .= ' e.reUser IN ( ';
			$first = true;
			foreach($options['users'] as $user){
				if($first == false){
					$where .= ",'".$user."'";
				}else{
					$where .= "'".$user."'";
					$first = false;
				} 
			}
			$where .= ") AND ";
		}
		
		if (isset($options['dateIs'])) {
			
			$where .="((convert(nvarchar(25),e.reStartDate,101)  >=  '{$options['dateIs']}'  AND
					  convert(nvarchar(25),e.reStartDate,101)  <=  '{$options['dateIs']}')  OR
					  (convert(nvarchar(25),e.reEndDate,101)  >=  '{$options['dateIs']}'  AND
					  convert(nvarchar(25),e.reEndDate,101)  <=  '{$options['dateIs']}')) 
					  ";
					  
			$dates = array(
				"result" => $this->_buildDateForQuerySingle($options['dateIs']),
				"number" => 1
			);		
		}else if (isset($options['dateStart']) && isset($options['dateEnd'])) {
			$dates = $this->_buildDateForQueryMultiple($options['dateStart'],$options['dateEnd']);
			$where .= "((convert(nvarchar(25),e.reStartDate,101)  >=  '{$options['dateStart']}'  AND
					  convert(nvarchar(25),e.reStartDate,101)  <=  '{$options['dateEnd']}')  OR
					  (convert(nvarchar(25),e.reEndDate,101)  >=  '{$options['dateStart']}'  AND
					  convert(nvarchar(25),e.reEndDate,101)  <=  '{$options['dateEnd']}')) 
					  ";
            switch ($options['interval']) {
            	
                case 'specweek':
					$days = array();
                    foreach ($options['days'] as $day) {
                        $days[] = $this->_weekDays[$day];
                    }					
					$where .= ' AND (DATEPART(weekday, e.reStartDate) IN (' . implode(',', $days) . ') OR 
					            DATEPART(weekday, e.reEndDate) IN (' . implode(',', $days) . '))';
                    break;
                case 'workweek':
                    $where .= ' AND (DATEPART(weekday, e.reStartDate) IN (2,3,4,5,6) OR 
                                DATEPART(weekday, e.reEndDate) IN (2,3,4,5,6) )';
                    break;
                case 'endweek':
                    $where .= ' AND (DATEPART(weekday, e.reStartDate) IN (1,7) OR
                    			DATEPART(weekday, e.reEndDate) IN (1,7) ) ';
                    break;
                case 'allweek':
                default:
                    // Do not filter by days
                    break;
            }
        }
		
		//print_r($where);die();
		try {
		    $stmt = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetRoiReport ?,?,?,?,?,?,?");
		    $stmt2 = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetInternetSitesProductivity ?,?,?,?,?,?,?,?,?,?");  
			$start = ($page-1)*$numberPerPage + 1;
			$end = ($page-1)*$numberPerPage + $numberPerPage;
			//echo $start." ".$end;die();
			$params = array(":start"=> $start,
							":end"=> $end,
							":where"=>$where,
							":hourStart"=>$hourStart,
							":hourEnd"=>$hourEnd,
							":dates" => $dates['result'],
							":number" => intval($dates['number'])							
							);
			$params2 = array(":start"=> $start,
							":end"=> $end,
							":where"=>$where,
							":hourStart"=>$hourStart,
							":hourEnd"=>$hourEnd,
							":dates" => $dates['result'],
							":number" => intval($dates['number']),
							":timeIntervals" => '-1',
							":type" => 'true',
							":productivity" => 'productive'									
							);				
		    $stmt->execute($params);
		    $response = $stmt->fetchAll();

				    $i=0;
		   //print_r($response);die();
			foreach($response as $item)
			{
				$params2[':where'] = $this->_buildWhereClause($options, $item['reUser']);
				
			    $stmt2->execute($params2);
			    $response2 = $stmt2->fetchAll();
	
			    $response[$i]['Timp']+= $response2[0]['Timp'];
			    //print_r($response1);
			     $i++;

			}		    
		   // print_r($response2);die();	
		
		  }
		  catch (Zend_Db_Adapter_Exception $e) {
		    print $e->__toString();
		  }
		  catch (PDOException $e) {
		    print $e->__toString();
		  }
		  catch (Zend_Exception $e) {
		    print $e->__toString();
		}
		
		return empty($response) ? array() : array(
											"response" => $response,
											"totalandcount" => $response2
											);		
		
       /* $this->_name = "Event";
        $this->_primary = "reID";
        
        $query = $this->select();
        $query
            ->setIntegrityCheck(false)
            ->from(array('event' => "Event"))
            ->joinLeft(array('rIdle' => "IdleEvent"), "event.reID=rIdle.rieRawEventID");
            // ->joinLeft(array('uData' => "user_to_departments"), "event.reUser=uData.name");
        
        // User filters
        if (!empty($options['users'])) {
            if (is_string($options['users'])) $options['users'] = array($options['users']);
            $query->where('event.reUser IN ( ? )', $options['users']);
        }

        // Date filters
        if (isset($options['dateIs'])) {
            $query->where('convert(nvarchar(10), reStartDate, 101) = ?', $options['dateIs']);
        } else if (isset($options['dateStart']) && isset($options['dateEnd'])) {
            $query
                ->where('convert(nvarchar(10), reStartDate, 101) >= ?', $options['dateStart'])
                ->where('convert(nvarchar(10), reEndDate, 101) <= ?', $options['dateEnd']);
            switch ($options['interval']) {
                case 'specweek':
                    $days = array();
                    foreach ($options['days'] as $day) {
                        $days[] = $this->_weekDays[$day];
                    }
                    $query->where('DATEPART(weekday, reStartDate) IN (' . implode(',', $days) . ')');
                    break;
                case 'workweek':
                    $query->where('DATEPART(weekday, reStartDate) IN (2,3,4,5,6)');
                    break;
                case 'endweek':
                    $query->where('DATEPART(weekday, reStartDate) IN (1,7)');
                    break;
                case 'allweek':
                default:
                    // Do not filter by days
                    break;
            }
        }
        
        // Order
        $query->order(array(
                        'event.reStartDate ASC'
                    ));
        
        // Limit
        if ($limit > 0) {
            if ($offset > 0) {
                $query->limit($limit, $offset);
            } else {
                $query->limit($limit);
            }
        }

        $result = $this->fetchAll($query);
		
		//print_r($result->toArray());die();
		
        return empty($result) ? array() : $result->toArray();*/
    }
    
    // @TODO: Implement this
    public function getPerformanceReportDataGrid($options, $limit = 0, $offset = 0)
    {

		$temp_months = array(
			"01" => "Ianuarie",
			"02" => "Februarie",
			"03" => "Martie",
			"04" => "Aprilie",
			"05" => "Mai",
			"06" => "Iunie",	
			"07" => "Iulie",
			"08" => "August",
			"09" => "Septembrie",
			"10" => "Octombrie",
			"11" => "Noiembrie",
			"12" => "Decembrie",
		);    	
    	
    	$result = array();
    	$months = '';
    	$first = true;
    	$tHours = -1;
    	$tWeActHours = -1;
    	$tProd = -1;
    	$tNProd = -1;
    	$tAHours = -1;
    	$tIHours = -1;
    	$productive = -1;
    	$userName = '';
    	$nameDepartment = '';
    	
    	$user = $options[6]['users'][0];
    	
    	$dbUser = new Application_Table_dbUserToDepartments();
    	$userDetails = $dbUser->getUserFromDepartmentsByName($user);
    	
    	//get departments ierarchy
        $dbDepartments = new Application_Table_dbDepartments();
		$allDepartmentsTemp = $dbDepartments->getAllDepartments();
		$allDepartments = array();
		foreach($allDepartmentsTemp as $localDepartment)
		{
			$allDepartments[$localDepartment['id']] = $localDepartment;
		} 

		$dept = $allDepartments[$userDetails['department']];

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
    	
		//get user real name	
    	if(!empty($userDetails))
    		$userName = $userDetails['last_name']." ".$userDetails['first_name'];
    	
		foreach($options[0]['months'] as $month)
		{
			if($first == true){
				$months.= $temp_months[$month];
				$first = false;
			}
			else
				$months.=", ".$temp_months[$month];
		} 
		

		$result[] = array(
			'name' => ' Lunile',
			'number' => $months,
			'deviation' => 1
		);	

		$result[] = array(
			'name' => ' Anul',
			'number' => $options[5]['year'],
			'deviation' => 1
		);		

		$result[] = array(
			'name' => ' Utilizator',
			'number' => $user." / ".$userName,
			'deviation' => 1
		);
					
		$result[] = array(
			'name' => ' Departamente',
			'number' => $nameDepartment,
			'deviation' => 1
		);			
		if(!empty($options[1]))
		{
			foreach($options[1]['deviation_productivity'] as $item)
			{
				switch ($item){
					case 'tHours':
						$temp_var = array();
						$temp_var = $this->getTotalComputerTime("'".$user."'",$options,0);
						$tHours = $temp_var['TimpTotal'];
						$tAHours = $temp_var['TimpActiv'];
						$tIHours = $temp_var['TimpInactiv'];
						$result[] = array(
							'name' => ' Total ore pe calculator',
							'number' => $tHours
						);
							
						break;
					case 'tAHours':
						if($tAHours == -1){
							$temp_var = array();
							$temp_var = $this->getTotalComputerTime("'".$user."'",$options,0);
							$tAHours = $temp_var['TimpActiv'];
							$tIHours = $temp_var['TimpInactiv'];							
						}		
						$result[] = array(
							'name' => ' Total timp activ ',
							'number' => $tAHours
						);											
						break;
					case 'tIHours':
						if($tIHours == -1){
							$temp_var = array();
							$temp_var = $this->getTotalComputerTime("'".$user."'",$options,0);
							$tIHours = $temp_var['TimpInactiv'];								
						}	
						$result[] = array(
							'name' => ' Total timp inactiv ',
							'number' => $tIHours
						);							
						break;
					case 'tPClose':
						break;
					case 'tSuplHours':
						$overtime = $this->getPerformanceOvertime("'".$user."'",$options);
						$result[] = array(
							'name' => ' Total ore suplimentare ',
							'number' => $overtime
						);							
						break;
					case 'tWeHours':
						$tWeHours = $this->getTotalComputerTime("'".$user."'",$options,1);
						$result[] = array(
							'name' => '  Total ore lucrate in week-end (S si D, active si inactive) ',
							'number' => $tWeHours
						);									
						break;
					case 'tWeActHours':
						$tWeActHours  = $this->getTotalProductiveInWeekend("'".$user."'",$options,1);
						$result[] = array(
							'name' => ' Total ore productive in week-end (S si D) ',
							'number' => $tWeActHours
						);							
						break;
					case 'roi':
						$productive = $this->getProductiveOrUnproductiveTotal("'".$user."'",$options,"'"."productive"."'");
						$roi = 0;
						if(!empty($userDetails))
							if(!empty($userDetails['cost_per_hour']))
							{
								$roi = $userDetails['cost_per_hour'] * $productive;
							}
							else{
								$temp_department = $dbDepartments->getDepartmentbyId("1");
								if(!empty($temp_department))
									if(!empty($temp_department['cost_per_hour']))
									{		
										$roi = $temp_department['cost_per_hour'] * $productive;						
									}
									else
									{
										$roi = "N/A";										
									}
							}
						$result[] = array(
							'name' => 'ROI ',
							'number' => number_format(floatval($roi/3600),4),
							'deviation' => 1
						);						
						break;																																									
					case 'tProd':
						if($productive == -1)
							$tProd = $this->getProductiveOrUnproductiveTotal("'".$user."'",$options,"'"."productive"."'");
						else 
							$tProd = $productive;
						$result[] = array(
							'name' => 'Total timp productiv ',
							'number' => $tProd
						);						
						break;	
					case 'tNProd':
						$tNProd = $this->getProductiveOrUnproductiveTotal("'".$user."'",$options,"'"."unproductive"."'");
						$result[] = array(
							'name' => ' Total timp neproductiv ',
							'number' => $tNProd
						);							
						break;												
				}
			}

		}
		
		if(!empty($options[2]['nhDeviation']))
		{
			$hourDeviation = $this->GetPerformanceDeviationInactive("'".$user."'", $options);
			$result[] = array(
							'name' => ' Numbar Abateri Orare ',
							'number' => $hourDeviation['hour_deviation'],
							'deviation' => 1
						);	
						
			$result[] = array(
							'name' => 'Conditie 1 pentru generarea abaterii orare' ,
							'number' => 'Se considera abatare depasirea cu mai mult de  '.$options[2]['nhDeviation']['timepassingdeviation'].' (ore:min) fata de inceperea a programului.',
							'deviation' => 1
						);
			$result[] = array(
							'name' => 'Conditie 2 pentru generarea abaterii orare' ,
							'number' => 'Se considera abatere plecarea cu peste '.$options[2]['nhDeviation']['leavingdeviation'].' (ore:min) inainte de ora de terminare a programului.',
							'deviation' => 1
						);						
		}
		
    	if(!empty($options[3]['nInacDeviation']))
		{
			$inactivityDeviation = $this->GetPerformanceDeviationInactive("'".$user."'", $options);
			$result[] = array(
							'name' => ' Numbar Abateri Inactivitate ',
							'number' => $inactivityDeviation['inactivity_deviation'],
							'deviation' => 1
						);	
			$result[] = array(
							'name' => 'Conditie pentru generarea abaterii de inactivitate' ,
							'number' => 'Depasirea cu  '.$options[3]['nInacDeviation']['inactivitydeviation'].' (ore:min) a timpului de inactivitate / zi - pauze legale.',
							'deviation' => 1
						);
						
		}	
			
    	if(!empty($options[4]['nNprodDeviation']))
		{
			$nNprodDeviation = $this->GetPerformanceDeviationUnproductive("'".$user."'", $options);
			$result[] = array(
							'name' => ' Numbar Abateri Neproductivitate ',
							'number' => $nNprodDeviation,
							'deviation' => 1
						);	
			$result[] = array(
							'name' => 'Conditie pentru generarea abaterii de neproductivitate' ,
							'number' => 'Depasirea cu  '.$options[4]['nNprodDeviation']['nonproddeviation'].'  (ore:min) a timpului de neproductivitate / zi.',
							'deviation' => 1
						);
						
		}		
		
		//echo $tWeHours." ".$tWeActHours." ".$tProd." ".$tNProd;die(); 
		
		return $result;
    	
    	
        /*$this->_name = "Event";
        $this->_primary = "reID";
        
        $query = $this->select();
        $query
            ->setIntegrityCheck(false)
            ->from(array('event' => "Event"))
            ->joinLeft(array('rIdle' => "IdleEvent"), "event.reID=rIdle.rieRawEventID");
        
        // User filters
        if (!empty($options['users'])) {
            if (is_string($options['users'])) $options['users'] = array($options['users']);
            $query->where('event.reUser IN ( ? )', $options['users']);
        }
        
        $dt = $options['month'] . '/' . $options['year'];
        $query->where(' substring(convert(nvarchar(10), event.reStartDate, 103), 3, 7) = ?', $dt);
        
        // Order
        $query->order(array(
                        'event.reStartDate ASC'
                    ));
        
        // Limit
        if ($limit > 0) {
            if ($offset > 0) {
                $query->limit($limit, $offset);
            } else {
                $query->limit($limit);
            }
        }

        $result = $this->fetchAll($query);
        return empty($result) ? array() : $result->toArray();*/
    }
    
    // @TODO: Implement this
    public function getPunchInReportDataGrid($options, $limit = 0, $offset = 0)
    {
    		
     	$where = '';
		$query = '';
		$hourStart = "'00:00:00'";
    	$hourEnd = "'23:59:59'";
		$months = '';
		$response2 = array();
		$typeProgram = 'true';
		$timeIntervals = '-1';
		$dbDepartments = new Application_Table_dbDepartments();			
		//echo $numberPerPage;die();
		if (!empty($options['users'])) {
			$where .= ' AND e.reUser IN ( ';
			$first = true;
			foreach($options['users'] as $user){
				if($first == false){
					$where .= ",'".$user."'";
				}else{
					$where .= "'".$user."'";
					$first = false;
				} 
			}
			$where .= ") ";
		}
		
		$dates = "_".$options['year']."_".$options['month'];
		//print_r($dates);die();
		try {
		    $stmt = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetPunchInReport ?,?");  

		    //test if the date selected exists in the database 
			$params2 = array(":table"=> $dates."_event",											
							);							
			$stmt2 = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC TestIfTableExists ?");
			$stmt2->execute($params2);  
			$response2 = $stmt2->fetchAll();		    
		    
		    if($response2[0]['ok'] == 0 )
		    	return array();
			
			$params = array (
							":where"=>$where,
						    ":dates" => $dates,						
							);
			
		    $stmt->execute($params);
		    $response = $stmt->fetchAll();
		    
		    //print_r($response);die();
					
		
		  }
		  catch (Zend_Db_Adapter_Exception $e) {
		    print $e->__toString();
		  }
		  catch (PDOException $e) {
		    print $e->__toString();
		  }
		  catch (Zend_Exception $e) {
		    print $e->__toString();
		}
		
		return  array(
						'usage' => $response
					);           	
        /*$this->_name = "Event";
        $this->_primary = "reID";
        
        $query = $this->select();
        $query
            ->setIntegrityCheck(false)
            ->from(array('event' => "Event"))
            ->joinLeft(array('rIdle' => "IdleEvent"), "event.reID=rIdle.rieRawEventID");
		//print_r($options['users']);	die();
        // User filters
        if (!empty($options['users'])) {
            if (is_string($options['users'])) $options['users'] = array($options['users']);
			
            $query->where('event.reUser IN ( ? )', $options['users']);
        }
		//print_r($query);die();
        if((empty($options['month'])) || (empty($options['year']))) {
            return array();
        } else {
            $query->where(' substring(convert(nvarchar(10), reStartDate, 103), 4, 8) = ?', str_pad($options['month'], 2, '0', STR_PAD_LEFT) . '/' . $options['year']);
        }
       // print_r($query);die();
        // Limit
        if ($limit > 0) {
            if ($offset > 0) {
                $query->limit($limit, $offset);
            } else {
                $query->limit($limit);
            }
        }
        
        $result = $this->fetchAll($query);
		
        return empty($result) ? array() : $result->toArray(); */       
    }

    public function getInternetSites()
    {
        $this->_name = "HttpAccessEvent";
        $this->_primary = "rhaeID";
		     
		
        $query = $this->select();
        $query
            ->setIntegrityCheck(false)
            ->from(array('http' => "HttpAccessEvent"))
            ->join(array('event' => "Event"), "http.rhaeRawEventID=event.reID");

        $result = $this->fetchAll($query);	
		
		//echo "<pre>";print_r($result);echo "</pre>";die();
		
        return empty($result) ? array() : $result->toArray();
    }
    
    public function getApplicationForCategories()
    {
        $this->_name = "Event";
        $this->_primary = "reID";
        
        $query = $this->select();
        $query
            ->setIntegrityCheck(false)
            ->from("Event");
        
        $result = $this->fetchAll($query);	
        return empty($result) ? array() : $result->toArray();
    }
	
    public function getChatForCategories()
    {
        $this->_name = "Event";
        $this->_primary = "reID";
        
        $query = $this->select();
        $query
            ->setIntegrityCheck(false)
            ->from("Event")
            ->where('reProcess=?','Yahoo! Messenger');
        
        $result = $this->fetchAll($query);	
        return empty($result) ? array() : $result->toArray();
    }	
	
    function getEmployeesFromServer()
    {
        $this->_name = "Event";
        $this->_primary = "reID";
        
        $query = $this->select();
        $query
            ->setIntegrityCheck(false)
			->distinct()
            ->from('Event', 'reUser');
			//->limit(10000,0);
        $result = $this->fetchAll($query);	
        return empty($result) ? array() : $result->toArray();		
    }
	
    // @TODO: Implement this
    public function insertInItm($dates)
    {
        $this->_name = "itm";
        $this->_primary = "id";

    	try {
		    $stmt = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC TruncateTable ?");  

			$params = array(":table"=> 'itm',						
							);						
									
		    $stmt->execute($params); 

		  }
		  catch (Zend_Db_Adapter_Exception $e) {
		    print $e->__toString();
		  }
		  catch (PDOException $e) {
		    print $e->__toString();
		  }
		  catch (Zend_Exception $e) {
		    print $e->__toString();
		}        
        
        foreach($dates as $data)
        	$this->insert($data);
	}	
    
    public function getIcon($reProcess)
    {
        $this->_name = "ProcessIconConverted";
        $this->_primary = "piID";
  
        $query = $this->select();
        $query
            ->setIntegrityCheck(false)
            ->from(array('icon' => "ProcessIconConverted"))
            ->joinLeft(array('event' => "Event"), "event.reIconID=icon.piID")
			->where("event.reProcess = ?",$reProcess);
   		
		
        $result = $this->fetchRow($query);
        //print_r($result);die();
        return empty($result)
            ? array()
            : $result->toArray();		
    }
	
    public function getIconByWindowName($reWindow)
    {
        $this->_name = "ProcessIconConverted";
        $this->_primary = "piID";
  
        $query = $this->select();
        $query
            ->setIntegrityCheck(false)
            ->from(array('icon' => "ProcessIconConverted"))
            ->joinLeft(array('event' => "Event"), "event.reIconID=icon.piID")
			->where("event.reWnd = ?",$reWindow);
   
        $result = $this->fetchRow($query);
        return empty($result)
            ? array()
            : $result->toArray();		
    }	
	
    public function getIconForUrl($site)
    {
        $this->_name = "ProcessIconConverted";
        $this->_primary = "piID";
  		
        $query = $this->select();
        $query
            ->setIntegrityCheck(false)
            ->from(array('icon' => "ProcessIconConverted"))
            ->joinLeft(array('event' => "Event"), "event.reIconID=icon.piID")
			->where("event.reProcess LIKE ?","'"."%".$site."%"."'");
   		
		
		//print_r($query);die();
        $result = $this->fetchRow($query);
        return empty($result)
            ? array()
            : $result->toArray();		
    }
	
	public function _buildDateForQuerySingle($date)
	{
		$month = '';
		$year = '';
		
		$month = substr($date,0,2);
		$year = substr ($date,6,4);
		
		return "_".$year."_".$month;
	}	
	
	public function _buildDateForQueryMultiple($dateStart,$dateEnd)
	{
		$result = ''; 
		$monthStart = '';
		$yearStart = '';
		$monthEnd = '';
		$yearEnd = '';
		$number = 0;
				
		$monthStart = substr($dateStart,0,2);
		$yearStart = substr ($dateStart,6,4);
		
		$monthEnd = substr($dateEnd,0,2);
		$yearEnd = substr ($dateEnd,6,4);	
		
			
		$j = intval($monthEnd,10);	
		$currentMonth = intval($monthStart,10);
		
		for($i = intval($yearStart,10);$i <= intval($yearEnd,10);$i++)	
		{
			while ( $currentMonth <= 12 ){
				//if we are in the last year and we've pased the endMonth break;
				if($i == intval($yearEnd,10) && $currentMonth >  $j)
				{
					break;
				}						
				if($currentMonth >= 10)
					$result .= "_".$i."_".$currentMonth.",";
				else
				{
					$result .= "_".$i."_0".$currentMonth.",";
				}
				$number++;
				$currentMonth++;	
			}	
			$currentMonth = 1;
			
		}
		$result = substr($result, 0, -1);	
		
		return array(
			"result" =>$result,
			"number" =>$number
		);
	
	}

	
	public function getApplicationsIcon($process,$options)
	{
		$stmt4 = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetApplicationsIcons ?,?,?,?");
		$icon = array();
		if (isset($options['dateIs'])) {
			$dates = array(
				"result" => $this->_buildDateForQuerySingle($options['dateIs']),
				"number" => 1
			);
		}else if (isset($options['dateStart']) && isset($options['dateEnd'])) {
			$dates = $this->_buildDateForQueryMultiple($options['dateStart'],$options['dateEnd']);
		}		
		if(!empty($process))
		{
			$paramsIcons = array(":start" => '1',
				":reProcess" => "'".$process."'",
				":dates" => $dates['result'],
				":number" => intval($dates['number'])
				);		
			 
				$stmt4->execute($paramsIcons);
				$response4 = $stmt4->fetchAll();		
				
				if(!empty($response4)){
					$icon[$process] = array(
							'iconID' => $response4[0]['reIcon'],
							'Icon' => $response4[0]['Icon']
					);
				}
				else 
					return array();
		}
		
		return $icon;
		
	}
	
	public function getInternetIcon($site,$options)
	{
		$stmt4 = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetInternetIcons ?,?,?");
		$icon = array();
		if (isset($options['dateIs'])) {
			$dates = array(
				"result" => $this->_buildDateForQuerySingle($options['dateIs']),
				"number" => 1
			);
		}else if (isset($options['dateStart']) && isset($options['dateEnd'])) {
			$dates = $this->_buildDateForQueryMultiple($options['dateStart'],$options['dateEnd']);
		}		
		if(!empty($site))
		{
			$paramsIcons = array(
				":site" => "'".$site."'",
				":dates" => $dates['result'],
				":number" => intval($dates['number'])
				);		
			 
				$stmt4->execute($paramsIcons);
				$response4 = $stmt4->fetchAll();		
	
				$icon[] = array(
						'iconID' => $response4[0]['iconID'],
						'Icon' => $response4[0]['Icon']
				);
		}
		
		return $icon;
		
	}

	public function getDocumentsIcon($window,$options)
	{
		$stmt4 = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetDocumentsIcons ?,?,?");
		$icon = array();
		if (isset($options['dateIs'])) {
			$dates = array(
				"result" => $this->_buildDateForQuerySingle($options['dateIs']),
				"number" => 1
			);
		}else if (isset($options['dateStart']) && isset($options['dateEnd'])) {
			$dates = $this->_buildDateForQueryMultiple($options['dateStart'],$options['dateEnd']);
		}		
		if(!empty($window))
		{
			
			$window = str_replace("'", "_", $window);
			
			$paramsIcons = array(
				":window" => "'".$window."'",
				":dates" => $dates['result'],
				":number" => intval($dates['number'])
				);		
			 
				$stmt4->execute($paramsIcons);
				$response4 = $stmt4->fetchAll();		
				if(!empty($response4))
					$icon[] = array(
							'iconID' => $response4[0]['reIcon'],
							'Icon' => $response4[0]['Icon']
					);
				else 
					$icon[] = array(
							'iconID' => "default",
							'Icon' => "default"
					);					
		}
		
		return $icon;
		
	}

	function getTotalComputerTime($user,$options,$weekend)
	{
		$count = 0;
		$TimpTotal = 0;
		$TimpInactiv = 0;
		$TimpActiv = 0;
		$timpWeekend = 0;
		$secundePauze = 0;
		$pauzaLegala = 0;
		$db = new Application_Table_dbUserToDepartments();
		$userDetails = $db->getUserFromDepartmentsByName(str_replace("'", "", $user));
		
		if(!empty($userDetails['break_lenght'])){
			$userDetailsArray = explode(":",$userDetails['break_lenght']);
			//print_r($userDetailsArray);die();
			$secundePauze = intval($userDetailsArray[0]) * 3600 + intval($userDetailsArray[1]) * 60 + intval($userDetailsArray[2]);
		}


		
		foreach($options[0]['months'] as $month)
		{
			$table = "_".$options[5]['year']."_".$month;			
			
			try {
			    $stmt = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetPerformanceTotalComputerTime ?,?,?");  
				
			    
			    //test if the date selected exists in the database 
				$params2 = array(":table"=> $table."_event",											
								);				
											
				$stmt2 = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC TestIfTableExists ?");
				$stmt2->execute($params2);  
				$response2 = $stmt2->fetchAll();		    
			    
			    if($response2[0]['ok'] == 0 )
			    	continue;		    
			    
				//echo $start." ".$end;die();
				$params = array(":table"=> $table,
								":user"=> $user,
								":weekend"=>$weekend
								);
				
			    $stmt->execute($params);
			    $response = $stmt->fetchAll();
			    
			    if($weekend == 0)
			    {
				    $count += $response[0]['Total'];
				    $TimpTotal += $response[0]['TimpTotal'];
				    $TimpInactiv += $response[0]['Inactiv'];
				    $TimpActiv += ($response[0]['TimpTotal'] - $response[0]['Inactiv']);
			    }
			    else
			    	$timpWeekend += $response[0]['TimpTotal'];
			}
			catch (Zend_Db_Adapter_Exception $e) {
				 print $e->__toString();
			  }
			  catch (PDOException $e) {
			    print $e->__toString();
			  }
			  catch (Zend_Exception $e) {
			    print $e->__toString();
			  }

		}
		
		//print_r($count." ".$timpTotal." ".$TimpInactiv." ".$TimpActiv);die();
		
		$pauzaLegala = $count * $secundePauze;
		
		if($weekend == 0 )
			return array(
				'TimpTotal'=> $TimpTotal - $pauzaLegala,
				'TimpInactiv'=> $TimpInactiv - $pauzaLegala,
				'TimpActiv'=> $TimpActiv
			);
		else
			return $timpWeekend;
		
	}
	
	function getTotalProductiveInWeekend($user,$options)
	{
		$count = 0;
		$TimpProductiv = 0;
		
		foreach($options[0]['months'] as $month)
		{
			$table = "_".$options[5]['year']."_".$month;
			
			try {
			    $stmt = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetProductiveHoursWeekend ?,?");  
				
				//echo $start." ".$end;die();
				$params = array(":table"=> $table,
								":user"=> $user
								);
				$params2 = array(":table"=> $table."_event",											
								);				
											
				$stmt2 = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC TestIfTableExists ?");
				$stmt2->execute($params2);  
				$response2 = $stmt2->fetchAll();		    
			    
			    if($response2[0]['ok'] == 0 )
			    	continue;								
								
				
			    $stmt->execute($params);
			    $response = $stmt->fetchAll();
			    
				$TimpProductiv += $response[0]['Activ'];

			}
			catch (Zend_Db_Adapter_Exception $e) {
				 print $e->__toString();
			  }
			  catch (PDOException $e) {
			    print $e->__toString();
			  }
			  catch (Zend_Exception $e) {
			    print $e->__toString();
			  }

		}
		//print_r($count." ".$timpTotal." ".$TimpInactiv." ".$TimpActiv);die();
		
		return $TimpProductiv;
		
	}

	function getProductiveOrUnproductiveTotal($user,$options,$type)
	{
		$count = 0;
		$Timp = 0;
		
		foreach($options[0]['months'] as $month)
		{
			$table = "_".$options[5]['year']."_".$month;
			
			try {
			    $stmt = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetProductiveOrUnproductiveTotal ?,?,?");  
				
				//echo $start." ".$end;die();
				$params = array(":table"=> $table,
								":user"=> $user,
								":type" => $type
								);
				$params2 = array(":table"=> $table."_event",											
								);				
											
				$stmt2 = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC TestIfTableExists ?");
				$stmt2->execute($params2);  
				$response2 = $stmt2->fetchAll();		    
			    
			    if($response2[0]['ok'] == 0 )
			    	continue;				
								
			    $stmt->execute($params);
			    $response = $stmt->fetchAll();

				$Timp += $response[0]['Timp'];

			}
			catch (Zend_Db_Adapter_Exception $e) {
				 print $e->__toString();
			  }
			  catch (PDOException $e) {
			    print $e->__toString();
			  }
			  catch (Zend_Exception $e) {
			    print $e->__toString();
			  }

		}
		//print_r($count." ".$timpTotal." ".$TimpInactiv." ".$TimpActiv);die();
		//print_r($Timp);die();
		return $Timp;
		
	}	
	
	function GetPerformanceDeviationInactive($user,$options)
	{
		$dbDepartments = new Application_Table_dbDepartments();
		$db = new Application_Table_dbUserToDepartments();
		
		$department = $dbDepartments->getDepartmentbyId("1");
		
		$days = array();
		$secundePauze = 0;
		
		$userDetails = $db->getUserFromDepartmentsByName(str_replace("'", "", $user));
		
		//print_r($userDetails);die();
		
		if(!empty($userDetails['break_lenght'])){
			$userDetailsArray = explode(":",$userDetails['break_lenght']);
			//print_r($userDetailsArray);die();
			$secundePauze = intval($userDetailsArray[0]) * 3600 + intval($userDetailsArray[1]) * 60 + intval($userDetailsArray[2]);
		}		
		
		if(!empty($department))
		{
			$timeIntervals = '';
			if(!empty($department['day1_start']))
				$days['1'] = array(
						'start' => $department['day1_start'],
						'end' => $department['day1_stop']
				);
			else
				$days['1'] = array(
						'start' => '00:00:00',
						'end' => '23:59:59'
				);
			if(!empty($department['day2_start']))
				$days['2'] = array(
						'start' => $department['day2_start'],
						'end' => $department['day2_stop']
				);
			else
				$days['2'] = array(
						'start' => '00:00:00',
						'end' => '23:59:59'
				);			
			if(!empty($department['day3_start']))
				$days['3'] = array(
						'start' => $department['day3_start'],
						'end' => $department['day3_stop']
				);
			else
				$days['3'] = array(
						'start' => '00:00:00',
						'end' => '23:59:59'
				);			
			if(!empty($department['day4_start']))
				$days['4'] = array(
						'start' => $department['day4_start'],
						'end' => $department['day4_stop']
				);
			else
				$days['4'] = array(
						'start' => '00:00:00',
						'end' => '23:59:59'
				);				
			if(!empty($department['day5_start']))
				$days['5'] = array(
						'start' => $department['day5_start'],
						'end' => $department['day5_stop']
				);
			else
				$days['5'] = array(
						'start' => '00:00:00',
						'end' => '23:59:59'
				);			
			if(!empty($department['day6_start']))
				$days['6'] = array(
						'start' => $department['day6_start'],
						'end' => $department['day6_stop']
				);
			else
				$days['6'] = array(
						'start' => '00:00:00',
						'end' => '23:59:59'
				);		
			if(!empty($department['day7_start']))
				$days['7'] = array(
						'start' => $department['day7_start'],
						'end' => $department['day7_stop']
				);
			else
				$days['7'] = array(
						'start' => '00:00:00',
						'end' => '23:59:59'
				);			
		}
	    $numarAbateriOrare = 0;
	    $numbarAbateriInactivitate = 0;
		//print_r($days);die();
		
		foreach($options[0]['months'] as $month)
		{
			$table = "_".$options[5]['year']."_".$month;
			
			try {
			    $stmt = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetPerformanceDeviationInactive ?,?");  
				
				//echo $start." ".$end;die();
				$params = array(":table"=> $table,
								":user"=> $user,
								);

				$params2 = array(":table"=> $table."_event",											
								);				
											
				$stmt2 = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC TestIfTableExists ?");
				$stmt2->execute($params2);  
				$response2 = $stmt2->fetchAll();		    
			    
			    if($response2[0]['ok'] == 0 )
			    	continue;								
								
			    $stmt->execute($params);
			    $response = $stmt->fetchAll();

			    foreach($response as $item)
			    {
					$tempDate = explode("/",$item['Data']);
				 	$currentDay = date('w',strtotime($tempDate[1]."/".$tempDate[0]."/".$tempDate[2]));
				 	
				 	if($currentDay == 0)
				 		$currentDay = 7;			   
					
				    $start = $days[$currentDay]['start'];
				    $end = $days[$currentDay]['end'];
				   
				    if(!empty($options[2]['nhDeviation'])){
				    	//print_r(strtotime($item['StartTime'])." ".strtotime($options[2]['nhDeviation']['timepassingdeviation'])." ".strtotime($start)."\n");
					    if( strtotime($item['StartTime']) - strtotime($options[2]['nhDeviation']['timepassingdeviation']) + strtotime('00:00:00') > strtotime($start)
							|| strtotime($item['EndTime']) + strtotime($options[2]['nhDeviation']['leavingdeviation']) < strtotime($end) + strtotime('00:00:00') 
					      ){
					    	$numarAbateriOrare++;
					    }
				    }
				    
				    
				    
				     if(!empty($options[3]['nInacDeviation'])){
				     	
				     	$item['TimpInactiv'] = $item['TimpInactiv'] - $secundePauze;
				     	$temp = explode(":",$options[3]['nInacDeviation']['inactivitydeviation']);
				     	$nrSeconds = intval($temp[0]) *3600 + intval($temp[1]) *60 + intval($temp[2]);
				     	
				     	if( $item['TimpInactiv'] - $nrSeconds > 0 )
				     	{
				     		$numbarAbateriInactivitate++;
				     	}
				    	
				    }
			    }
				//print_r($numarAbateriOrare." ".$numbarAbateriInactivitate);die();
				//print_r($response);die();

			}
			catch (Zend_Db_Adapter_Exception $e) {
				 print $e->__toString();
			  }
			  catch (PDOException $e) {
			    print $e->__toString();
			  }
			  catch (Zend_Exception $e) {
			    print $e->__toString();
			  }

		}

		return array(
			'hour_deviation' => $numarAbateriOrare,
			'inactivity_deviation' =>$numbarAbateriInactivitate
		);
	}

	function GetPerformanceDeviationUnproductive($user,$options)
	{
		$dbDepartments = new Application_Table_dbDepartments();
		$department = $dbDepartments->getDepartmentbyId("1");

		//print_r($days);die();
		$numarAbateriNeproductivitate = 0;
		foreach($options[0]['months'] as $month)
		{
			$table = "_".$options[5]['year']."_".$month;
			
			try {
			    $stmt = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetPerformanceDeviationUnproductive ?,?");  
				
				//echo $start." ".$end;die();
				$params = array(":table"=> $table,
								":user"=> $user,
								);
				$params2 = array(":table"=> $table."_event",											
								);				
											
				$stmt2 = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC TestIfTableExists ?");
				$stmt2->execute($params2);  
				$response2 = $stmt2->fetchAll();		    
			    
			    if($response2[0]['ok'] == 0 )
			    	continue;				
								
								
			    $stmt->execute($params);
			    $response = $stmt->fetchAll();
			   
				 foreach($response as $item)
			    {   
			       if(!empty($options[4]['nNprodDeviation'])){
				     	
				     	$temp = explode(":",$options[4]['nNprodDeviation']['nonproddeviation']);
				     	$nrSeconds = intval($temp[0]) *3600 + intval($temp[1]) *60 + intval($temp[2]);
				     	
				     	if( $item['TimpNeproductiv'] - $nrSeconds > 0 )
				     	{
				     		$numarAbateriNeproductivitate++;
				     	}
			       }
			    }
				//print_r($response);die();

			}
			catch (Zend_Db_Adapter_Exception $e) {
				 print $e->__toString();
			  }
			  catch (PDOException $e) {
			    print $e->__toString();
			  }
			  catch (Zend_Exception $e) {
			    print $e->__toString();
			  }

		}	

		return $numarAbateriNeproductivitate;
	}	
	
	function getPerformanceOvertime($user,$options)
	{
		$dbDepartments = new Application_Table_dbDepartments();
		$department = $dbDepartments->getDepartmentbyId("1");	

		$days = array();
		$timpLucru = 0;
		if(!empty($department))
		{
			$timeIntervals = '';
			if(!empty($department['day1_start']))
			{
				$temp1 = explode(":",$department['day1_start']);
				$nrSeconds1 = intval($temp1[0]) *3600 + intval($temp1[1]) *60 + intval($temp1[2]);

				$temp2 = explode(":",$department['day1_stop']);
				$nrSeconds2 = intval($temp2[0]) *3600 + intval($temp2[1]) *60 + intval($temp2[2]);
				
				$timpLucru = $timpLucru + ($nrSeconds2 - $nrSeconds1);
			}	
			if(!empty($department['day2_start']))
			{
				$temp1 = explode(":",$department['day2_start']);
				$nrSeconds1 = intval($temp1[0]) *3600 + intval($temp1[1]) *60 + intval($temp1[2]);

				$temp2 = explode(":",$department['day2_stop']);
				$nrSeconds2 = intval($temp2[0]) *3600 + intval($temp2[1]) *60 + intval($temp2[2]);
				
				$timpLucru = $timpLucru + ($nrSeconds2 - $nrSeconds1);
			}
		
			if(!empty($department['day3_start']))
			{
				$temp1 = explode(":",$department['day3_start']);
				$nrSeconds1 = intval($temp1[0]) *3600 + intval($temp1[1]) *60 + intval($temp1[2]);

				$temp2 = explode(":",$department['day3_stop']);
				$nrSeconds2 = intval($temp2[0]) *3600 + intval($temp2[1]) *60 + intval($temp2[2]);
				
				$timpLucru = $timpLucru + ($nrSeconds2 - $nrSeconds1);
			}
		
			if(!empty($department['day4_start']))
			{
				$temp1 = explode(":",$department['day4_start']);
				$nrSeconds1 = intval($temp1[0]) *3600 + intval($temp1[1]) *60 + intval($temp1[2]);

				$temp2 = explode(":",$department['day4_stop']);
				$nrSeconds2 = intval($temp2[0]) *3600 + intval($temp2[1]) *60 + intval($temp2[2]);
				
				$timpLucru = $timpLucru + ($nrSeconds2 - $nrSeconds1);
			}
							
			if(!empty($department['day5_start']))
			{
				$temp1 = explode(":",$department['day5_start']);
				$nrSeconds1 = intval($temp1[0]) *3600 + intval($temp1[1]) *60 + intval($temp1[2]);

				$temp2 = explode(":",$department['day5_stop']);
				$nrSeconds2 = intval($temp2[0]) *3600 + intval($temp2[1]) *60 + intval($temp2[2]);
				
				$timpLucru = $timpLucru + ($nrSeconds2 - $nrSeconds1);
			}
						
			if(!empty($department['day6_start']))
			{
				$temp1 = explode(":",$department['day6_start']);
				$nrSeconds1 = intval($temp1[0]) *3600 + intval($temp1[1]) *60 + intval($temp1[2]);

				$temp2 = explode(":",$department['day6_stop']);
				$nrSeconds2 = intval($temp2[0]) *3600 + intval($temp2[1]) *60 + intval($temp2[2]);
				
				$timpLucru = $timpLucru + ($nrSeconds2 - $nrSeconds1);
			}
					
			if(!empty($department['day7_start']))
			{
				$temp1 = explode(":",$department['day7_start']);
				$nrSeconds1 = intval($temp1[0]) *3600 + intval($temp1[1]) *60 + intval($temp1[2]);

				$temp2 = explode(":",$department['day7_stop']);
				$nrSeconds2 = intval($temp2[0]) *3600 + intval($temp2[1]) *60 + intval($temp2[2]);
				
				$timpLucru = $timpLucru + ($nrSeconds2 - $nrSeconds1);
			}
			
		}	
		
		//multiply the time for a week with the numar of weeks in a month
		$timpLucru = $timpLucru * 4;
		
		$timpTotal = $this->getTotalComputerTime($user,$options,0);
		
		//print_r($timpTotal['TimpTotal']." ".$timpLucru);die();
		$timpSuplimentar = $timpTotal['TimpTotal'];
		foreach($options[0]['months'] as $month)
		{
			$table = "_".$options[5]['year']."_".$month;
			
			try {
			      
				$params2 = array(":table"=> $table."_event",											
								);				
											
				$stmt2 = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC TestIfTableExists ?");
				$stmt2->execute($params2);  
				$response2 = $stmt2->fetchAll();		    
			    
			    if($response2[0]['ok'] == 0 )
			    	continue;				

				//print_r($response);die();

			}
			catch (Zend_Db_Adapter_Exception $e) {
				 print $e->__toString();
			  }
			  catch (PDOException $e) {
			    print $e->__toString();
			  }
			  catch (Zend_Exception $e) {
			    print $e->__toString();
			  }			
			
			
		  	
			$timpSuplimentar = $timpSuplimentar - $timpLucru;

		}
		
		if($timpSuplimentar < 0 )
			$timpSuplimentar = 0;
		
		return $timpSuplimentar;
		
	}
	
	function GetCronHourDeviation($user,$options)
	{
		$dbDepartments = new Application_Table_dbDepartments();
		$db = new Application_Table_dbUserToDepartments();
		
		$department = $dbDepartments->getDepartmentbyId("1");
		
		$days = array();
		$secundePauze = 0;
		
		$userDetails = $db->getUserFromDepartmentsByName(str_replace("'", "", $user));
		
		//print_r($userDetails);die();
		
		if(!empty($userDetails['break_lenght'])){
			$userDetailsArray = explode(":",$userDetails['break_lenght']);
			//print_r($userDetailsArray);die();
			$secundePauze = intval($userDetailsArray[0]) * 3600 + intval($userDetailsArray[1]) * 60 + intval($userDetailsArray[2]);
		}		
		
		if(!empty($department))
		{
			$timeIntervals = '';
			if(!empty($department['day1_start']))
				$days['1'] = array(
						'start' => $department['day1_start'],
						'end' => $department['day1_stop']
				);
			else
				$days['1'] = array(
						'start' => '00:00:00',
						'end' => '23:59:59'
				);
			if(!empty($department['day2_start']))
				$days['2'] = array(
						'start' => $department['day2_start'],
						'end' => $department['day2_stop']
				);
			else
				$days['2'] = array(
						'start' => '00:00:00',
						'end' => '23:59:59'
				);			
			if(!empty($department['day3_start']))
				$days['3'] = array(
						'start' => $department['day3_start'],
						'end' => $department['day3_stop']
				);
			else
				$days['3'] = array(
						'start' => '00:00:00',
						'end' => '23:59:59'
				);			
			if(!empty($department['day4_start']))
				$days['4'] = array(
						'start' => $department['day4_start'],
						'end' => $department['day4_stop']
				);
			else
				$days['4'] = array(
						'start' => '00:00:00',
						'end' => '23:59:59'
				);				
			if(!empty($department['day5_start']))
				$days['5'] = array(
						'start' => $department['day5_start'],
						'end' => $department['day5_stop']
				);
			else
				$days['5'] = array(
						'start' => '00:00:00',
						'end' => '23:59:59'
				);			
			if(!empty($department['day6_start']))
				$days['6'] = array(
						'start' => $department['day6_start'],
						'end' => $department['day6_stop']
				);
			else
				$days['6'] = array(
						'start' => '00:00:00',
						'end' => '23:59:59'
				);		
			if(!empty($department['day7_start']))
				$days['7'] = array(
						'start' => $department['day7_start'],
						'end' => $department['day7_stop']
				);
			else
				$days['7'] = array(
						'start' => '00:00:00',
						'end' => '23:59:59'
				);			
		}
	    $numarAbateriOrare = 0;
		//print_r($days);die();
		
		$table = "_".$options[5]['year']."_".$options[0]['month'];
		
		try {
		    $stmt = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetPerformanceDeviationInactive ?,?");  
		    $stmt3 = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetCronProgramDeviation ?,?");
			
			//echo $start." ".$end;die();
			$params = array(":table"=> $table,
							":user"=> $user,
							);
	
			$params2 = array(":table"=> $table."_event",											
							);				

			$params3 = array(
								":user" => $user,
								":table"=> $table,											
							);								
			$stmt2 = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC TestIfTableExists ?");
			$stmt2->execute($params2);  
			$response2 = $stmt2->fetchAll();		    
		    
		    if($response2[0]['ok'] == 0 )
		    	continue;								
							
		    $stmt->execute($params);
		    $response = $stmt->fetchAll();

			$stmt3->execute($params3);  
			$response3 = $stmt3->fetchAll();
				    
		    foreach($response as $item)
		    {
				$tempDate = explode("/",$item['Data']);
			 	$currentDay = date('w',strtotime($tempDate[1]."/".$tempDate[0]."/".$tempDate[2]));
			 	
			 	if($currentDay == 0)
			 		$currentDay = 7;			   
				
			    $start = $days[$currentDay]['start'];
			    $end = $days[$currentDay]['end'];
			   
			    if(!empty($options[2]['nhDeviation'])){
			    	//print_r(strtotime($item['StartTime'])." ".strtotime($options[2]['nhDeviation']['timepassingdeviation'])." ".strtotime($start)."\n");
				    if( strtotime($item['StartTime']) - strtotime($options[2]['nhDeviation']['timepassingdeviation']) + strtotime('00:00:00') > strtotime($start)
						|| strtotime($item['EndTime']) + strtotime($options[2]['nhDeviation']['leavingdeviation']) < strtotime($end) + strtotime('00:00:00') 
				      ){
				    	$numarAbateriOrare++;
				    }
			    }
		    }
		    
		    foreach($response3 as $item)
		    {
		    	$tempDate = explode("/",$item['Data']);
			 	$currentDay = date('w',strtotime($tempDate[1]."/".$tempDate[0]."/".$tempDate[2]));
			 	
			 	if($currentDay == 0)
			 		$currentDay = 7;			   
				
			    $end = $days[$currentDay]['end'];
				
			    if(!empty($options[2]['nhDeviation'])){
					if( strtotime($item['Last']) - strtotime($options[2]['nhDeviation']['leavingafterschedule']) + strtotime("00:00:00") > strtotime($end)  ){
						$numarAbateriOrare++;
					}
			    }
		    }
		    
			//print_r($numarAbateriOrare." ".$numbarAbateriInactivitate);die();
			//print_r($response);die();
	
		}
		catch (Zend_Db_Adapter_Exception $e) {
			 print $e->__toString();
		  }
		  catch (PDOException $e) {
		    print $e->__toString();
		  }
		  catch (Zend_Exception $e) {
		    print $e->__toString();
		  }

		return $numarAbateriOrare;
	}

	function GetCronInactivityDeviation($user,$options)
	{
		$dbDepartments = new Application_Table_dbDepartments();
		$db = new Application_Table_dbUserToDepartments();
		
		$department = $dbDepartments->getDepartmentbyId("1");
		
		$days = array();
		$secundePauze = 0;
		
		$userDetails = $db->getUserFromDepartmentsByName(str_replace("'", "", $user));
		
		//print_r($userDetails);die();
		
		if(!empty($userDetails['break_lenght'])){
			$userDetailsArray = explode(":",$userDetails['break_lenght']);
			//print_r($userDetailsArray);die();
			$secundePauze = intval($userDetailsArray[0]) * 3600 + intval($userDetailsArray[1]) * 60 + intval($userDetailsArray[2]);
		}		
		
		if(!empty($department))
		{
			$timeIntervals = '';
			if(!empty($department['day1_start']))
				$days['1'] = array(
						'start' => $department['day1_start'],
						'end' => $department['day1_stop']
				);
			else
				$days['1'] = array(
						'start' => '00:00:00',
						'end' => '23:59:59'
				);
			if(!empty($department['day2_start']))
				$days['2'] = array(
						'start' => $department['day2_start'],
						'end' => $department['day2_stop']
				);
			else
				$days['2'] = array(
						'start' => '00:00:00',
						'end' => '23:59:59'
				);			
			if(!empty($department['day3_start']))
				$days['3'] = array(
						'start' => $department['day3_start'],
						'end' => $department['day3_stop']
				);
			else
				$days['3'] = array(
						'start' => '00:00:00',
						'end' => '23:59:59'
				);			
			if(!empty($department['day4_start']))
				$days['4'] = array(
						'start' => $department['day4_start'],
						'end' => $department['day4_stop']
				);
			else
				$days['4'] = array(
						'start' => '00:00:00',
						'end' => '23:59:59'
				);				
			if(!empty($department['day5_start']))
				$days['5'] = array(
						'start' => $department['day5_start'],
						'end' => $department['day5_stop']
				);
			else
				$days['5'] = array(
						'start' => '00:00:00',
						'end' => '23:59:59'
				);			
			if(!empty($department['day6_start']))
				$days['6'] = array(
						'start' => $department['day6_start'],
						'end' => $department['day6_stop']
				);
			else
				$days['6'] = array(
						'start' => '00:00:00',
						'end' => '23:59:59'
				);		
			if(!empty($department['day7_start']))
				$days['7'] = array(
						'start' => $department['day7_start'],
						'end' => $department['day7_stop']
				);
			else
				$days['7'] = array(
						'start' => '00:00:00',
						'end' => '23:59:59'
				);			
		}
	    $numbarAbateriInactivitate = 0;
		//print_r($days);die();
		
		$table = "_".$options[5]['year']."_".$options[0]['month'];
		
		try {
		    $stmt = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetPerformanceDeviationInactive ?,?");  
		 
			
			//echo $start." ".$end;die();
			$params = array(":table"=> $table,
							":user"=> $user,
							);
	
			$params2 = array(":table"=> $table."_event",											
							);				
						
			$stmt2 = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC TestIfTableExists ?");
			$stmt2->execute($params2);  
			$response2 = $stmt2->fetchAll();		    
		    
		    if($response2[0]['ok'] == 0 )
		    	continue;								
							
		    $stmt->execute($params);
		    $response = $stmt->fetchAll();

		    //echo "<pre>"; print_r($response);echo "</pre>";die();
		    
		    foreach($response as $item)
		    {
				$tempDate = explode("/",$item['Data']);
			 	$currentDay = date('w',strtotime($tempDate[1]."/".$tempDate[0]."/".$tempDate[2]));
			 	
			 	if($currentDay == 0)
			 		$currentDay = 7;			   
				
			    $start = $days[$currentDay]['start'];
			    $end = $days[$currentDay]['end'];
			   
		    	if(!empty($options[3]['nInacDeviation'])){
		     	
			     	$item['TimpInactiv'] = $item['TimpInactiv'] - $secundePauze;
			     	$temp = explode(":",$options[3]['nInacDeviation']['inactivitydeviation']);
			     	$nrSeconds = intval($temp[0]) *3600 + intval($temp[1]) *60 + intval($temp[2]);
			     	
			     	if( $item['TimpInactiv'] - $nrSeconds > 0 )
			     	{
			     		$numbarAbateriInactivitate++;
			     	}
				    	
				}
		    }
		    
		    
			//print_r($numarAbateriOrare." ".$numbarAbateriInactivitate);die();
			//print_r($response);die();
	
		}
		catch (Zend_Db_Adapter_Exception $e) {
			 print $e->__toString();
		  }
		  catch (PDOException $e) {
		    print $e->__toString();
		  }
		  catch (Zend_Exception $e) {
		    print $e->__toString();
		  }

		return $numbarAbateriInactivitate;
	}

	function GetCronDeviationUnproductive($user,$options)
	{
		$dbDepartments = new Application_Table_dbDepartments();
		$department = $dbDepartments->getDepartmentbyId("1");

		//print_r($days);die();
		$numarAbateriNeproductivitate = 0;

			$table = "_".$options[5]['year']."_".$options[0]['month'];
			//echo $user;die();
			try {
			    $stmt = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetPerformanceDeviationUnproductive ?,?");  
				
				//echo $start." ".$end;die();
				$params = array(":table"=> $table,
								":user"=> $user,
								);
				$params2 = array(":table"=> $table."_event",											
								);				
											
				$stmt2 = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC TestIfTableExists ?");
				$stmt2->execute($params2);  
				$response2 = $stmt2->fetchAll();		    
			    
			    if($response2[0]['ok'] == 0 )
			    	continue;				
								
								
			    $stmt->execute($params);
			    $response = $stmt->fetchAll();
			   
			    //echo "<pre>";print_r($response);echo "<pre>";die();
			    
				foreach($response as $item)
			    { 
			    	 
			       if(!empty($options[4]['nNprodDeviation'])){
				     	//print_r($options[4]['nNprodDeviation']['nonproddeviation']);die();
				     	$temp = explode(":",$options[4]['nNprodDeviation']['nonproddeviation']);
				     	$nrSeconds = intval($temp[0]) *3600 + intval($temp[1]) *60 + intval($temp[2]);
				     	
				     	if( $item['TimpNeproductiv'] - $nrSeconds > 0 )
				     	{
				     		$numarAbateriNeproductivitate++;
				     	}
			       }
			    }
				

			}
			catch (Zend_Db_Adapter_Exception $e) {
				 print $e->__toString();
			  }
			  catch (PDOException $e) {
			    print $e->__toString();
			  }
			  catch (Zend_Exception $e) {
			    print $e->__toString();
			  }
	

		return $numarAbateriNeproductivitate;
	}	
	
	function _buildWhereClause($options,$user)
	{
	    $where = '';
		$query = '';
		$hourStart = "'00:00:00'";
    	$hourEnd = "'23:59:59'";
		$months = '';
		
		$response1 = array();
		$response2 = array();
		$response3 = array();
		$response4 = array();
		$response5 = array();	
		$response6 = array();
			
		$typeProgram = 'true';
		$timeIntervals = '-1';
		$users = '-1';
		$dbDepartments = new Application_Table_dbDepartments();			
		//echo $numberPerPage;die();
		if (!empty($user)) {
			$where .= ' e.reUser IN ( ';
			$users = ' u.name IN ( ';
			
			$where .= "'".$user."'";
			$users .= "'".$user."'";
			
			$where .= ") AND ";
			$users .= ") ";
		}
		if (isset($options['dateIs'])) {
			
			$where .="((convert(nvarchar(25),e.reStartDate,101)  >=  '{$options['dateIs']}'  AND
					  convert(nvarchar(25),e.reStartDate,101)  <=  '{$options['dateIs']}')  OR
					  (convert(nvarchar(25),e.reEndDate,101)  >=  '{$options['dateIs']}'  AND
					  convert(nvarchar(25),e.reEndDate,101)  <=  '{$options['dateIs']}')) 
					  ";
					  
			$dates = array(
				"result" => $this->_buildDateForQuerySingle($options['dateIs']),
				"number" => 1
			);		
		}else if (isset($options['dateStart']) && isset($options['dateEnd'])) {
			$dates = $this->_buildDateForQueryMultiple($options['dateStart'],$options['dateEnd']);
			$where .= "((convert(nvarchar(25),e.reStartDate,101)  >=  '{$options['dateStart']}'  AND
					  convert(nvarchar(25),e.reStartDate,101)  <=  '{$options['dateEnd']}')  OR
					  (convert(nvarchar(25),e.reEndDate,101)  >=  '{$options['dateStart']}'  AND
					  convert(nvarchar(25),e.reEndDate,101)  <=  '{$options['dateEnd']}')) 
					  ";
            switch ($options['interval']) {
            	
                case 'specweek':
					$days = array();
                    foreach ($options['days'] as $day) {
                        $days[] = $this->_weekDays[$day];
                    }					
					$where .= ' AND (DATEPART(weekday, e.reStartDate) IN (' . implode(',', $days) . ') OR 
					            DATEPART(weekday, e.reEndDate) IN (' . implode(',', $days) . '))';
                    break;
                case 'workweek':
                    $where .= ' AND (DATEPART(weekday, e.reStartDate) IN (2,3,4,5,6) OR 
                                DATEPART(weekday, e.reEndDate) IN (2,3,4,5,6) )';
                    break;
                case 'endweek':
                    $where .= ' AND (DATEPART(weekday, e.reStartDate) IN (1,7) OR
                    			DATEPART(weekday, e.reEndDate) IN (1,7) ) ';
                    break;
                case 'allweek':
                default:
                    // Do not filter by days
                    break;
            }
        }
		if(!empty($options['timeinterval']))
	   		switch($options['timeinterval']) {
	            case 'workinghours':
					$where .= "  AND ((convert(nvarchar(25),e.reStartDate,8)  >=  convert(nvarchar(8), '{$options['hArray']['startTime']}', 8)  AND
						  convert(nvarchar(25),e.reStartDate,8)  <=  convert(nvarchar(8), '{$options['hArray']['endTime']}', 8))  OR
						  (convert(nvarchar(25),e.reEndDate,8)  >=  convert(nvarchar(8), '{$options['hArray']['startTime']}', 8)  AND
						  convert(nvarchar(25),e.reEndDate,8)  <=  convert(nvarchar(8), '{$options['hArray']['endTime']}', 8))) 
						  ";
					$hourStart = "'".$options['hArray']['startTime']."'";
					$hourEnd = "'".$options['hArray']['endTime']."'";					  
	               break;
				case 'workhours':
					$department = $dbDepartments->getDepartmentbyId("1");
					$typeProgram = 'true';
					break;
				case 'workoutsidehours' :
					$department = $dbDepartments->getDepartmentbyId("1");
					$typeProgram = 'false';				
					break;
	            case 'workallhours':
	            default:
	                // Do not filter by hours
	                break;
	        }
		
		if(!empty($department))
		{
			$timeIntervals = '';
			if(!empty($department['day1_start']))
				$timeIntervals .= $department['day1_start'].",".$department['day1_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";
			if(!empty($department['day2_start']))
				$timeIntervals .= $department['day2_start'].",".$department['day2_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day3_start']))
				$timeIntervals .= $department['day3_start'].",".$department['day3_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day4_start']))
				$timeIntervals .= $department['day4_start'].",".$department['day4_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day5_start']))
				$timeIntervals .= $department['day5_start'].",".$department['day5_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day6_start']))
				$timeIntervals .= $department['day6_start'].",".$department['day6_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
			if(!empty($department['day7_start']))
				$timeIntervals .= $department['day7_start'].",".$department['day7_stop'].";";
			else
				$timeIntervals .= "00:00:00,23:59:59;";				
		}
		return $where;		
	}
    public function getIcons()
    {
    	try {
		    $stmt = Zend_Db_Table::getDefaultAdapter()->prepare("EXEC GetIcons ");  

			$params = array(":table"=> 'itm',						
							);						
									
		    $stmt->execute(); 
			$response = $stmt->fetchAll();
		  }
		  catch (Zend_Db_Adapter_Exception $e) {
		    print $e->__toString();
		  }
		  catch (PDOException $e) {
		    print $e->__toString();
		  }
		  catch (Zend_Exception $e) {
		    print $e->__toString();
		}        
        
		return $response;
    }	
}

/* EOF */
