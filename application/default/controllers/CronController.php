<?php

class CronController extends Zend_Controller_Action
{
	
    const XSMALL = 0.3;
    const SMALL = 2;
    const MEDIUM = 3;
    const LARGE = 5;
    const XLARGE = 8;
    const HUGE = 11;
    	
    public function init() 
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNeverRender();   
    }

    public function indexAction()
    {
    	
        $selector = new Application_Table_dbEmail();
        $response = $selector->getReportAlertsByType('ra');
        
        $message = '';
        $hasDeviations = 0;
        if (empty($response)) {
            echo "Nothing to do.";
            return;
        }
        
        foreach($response as $cron)
        {
        	$message = '';
        	$users = explode("/",$cron['users']);
        	$destination = explode(",", $cron['destination']);
        	$alertName = $cron['name'];
        	$hasDeviations = 0;
        	
        	switch ($cron){
        		case  $cron['alert_type'] == 'hourly_deviation':
        			
        			$time_deviation = explode("/", $cron['time_deviation']);
        			$message.="<h1> Abatere Orara </h1>";
        			$message.="<h3> Luna : ".date("F ")."</h3>";
        			$message.="<h3>An : ".date("Y ")."</h3>";
        			$message.="<h3>Nume Alerta : ".$alertName."</h3>";
        			
        			$result = $this->getUsersWithHourDeviation($users,$time_deviation);
        			
        			foreach($result as $key=>$value)
        			{
        				$hasDeviations = 1;
        				
        				$message.= "<p>Userul ".$key." are ".$value." abateri orare</p>";
        				
        			}
        			if($hasDeviations == 1)
        				$this->sendMail($message, $destination);
        			
        			break;
        		case  $cron['alert_type'] == 'inactivity':
        			
        			$message.="<h1> Abatere de Inactivitate </h1>";
        			$message.="<h3> Luna : ".date("F ")."</h3>";
        			$message.="<h3>An : ".date("Y ")."</h3>";
        			$message.="<h3>Nume Alerta : ".$alertName."</h3>";
        			        			
        			$result = $this->getUsersWithInactivityDeviation($users,$cron['time_deviation']);
        			
        			foreach($result as $key=>$value)
        			{
        				$hasDeviations = 1;
        				
        				$message.= "<p>Userul ".$key." are ".$value." abateri de inactivitate</p>";
        				
        			}
        			if($hasDeviations == 1)
        				$this->sendMail($message, $destination);        			
        			
        			break;
        		case  $cron['alert_type'] == 'unproductive':

        			$message.="<h1> Abatere de Neproductivitate </h1>";
        			$message.="<h3> Luna : ".date("F ")."</h3>";
        			$message.="<h3>An : ".date("Y ")."</h3>";
        			$message.="<h3>Nume Alerta : ".$alertName."</h3>";

        			
        			
        			$result = $this->getUsersWithUnproductiveDeviation($users,$cron['time_deviation']);
        			
        			foreach($result as $key=>$value)
        			{
        				$hasDeviations = 1;
        				
        				$message.= "<p>Userul ".$key." are ".$value." abateri de neproductivitate</p>";
        				
        			}
        			if($hasDeviations == 1)
        				$this->sendMail($message, $destination);          			
        			
        			break;        			        			
        	}
        }
        
        
        /*$cron = new Custom_CronParser();
        foreach ($response as $cronjob) {
            $schedule = "";
            $cron->calcLastRan($schedule);
            $lastExecTimestamp = $cron->getLastRanUnix();
            $actualExecTimestamp = "";
            if ($actualExecTimestamp < $schedule) {
                // Must execute
            }
        }*/
    }
    
    public function reportemailAction()
    {
    	
        $selector = new Application_Table_dbEmail();
        $response = $selector->getReportAlertsByType('re');
        
        $reportsResult = array();
        
        
        $hasDeviations = 0;
        if (empty($response)) {
            echo "Nothing to do.";
            return;
        }
        
		       
       //echo "<pre>"; print_r($response);echo "</pre>";die();
        foreach($response as $cron)
        {
        	$db = new Application_Table_dbUserToDepartments();  
        	 
        	$reportsResult = array();
        	$users = explode("/",$cron['users']);
        	$destination = explode(",", $cron['destination']);
        	$reports = explode("/", $cron['reports']);
        	$alertName = $cron['name'];
        	
         	$body = '<h1> Rapoarte pe Mail </h1>';
        	$body.="A fost generata alerta <b>".$alertName."</b>";    

        	
        	foreach($users as $user){
        		
		    	$userDetails = $db->getUserFromDepartmentsByID($user);    
				
				$reportsResult = $this->getUserReports($userDetails['name'],$reportsResult,$reports);
				
        	}
        	//echo "<pre>";print_r($reportsResult);echo "</pre>";die();
        	$this->sendMail($body, $destination,$reportsResult);
        }
        
      //  echo "<pre>"; print_r($reportsResult);echo "</pre>";die();
       
    }    
    
    public function testAction()
    {	    	
		
    }
    
    public function jobAction()
    {
    	
        $id = $this->_request->getParam('id');
        $selector = new Application_Table_dbEmail();
        $response = $selector->getReportAlertById($id);
        if (empty($response)) {
            echo "Nothing to do.";
            return;
        }
        
        $cron = new Custom_CronParser();
        $schedule = "";
        $cron->calcLastRan($schedule);
        $lastExecTimestamp = $cron->getLastRanUnix();
        $actualExecTimestamp = "";
        if ($actualExecTimestamp < $schedule) {
            // Must execute
        }
    }
    
    
    public function __call($method, $data)
    {
        // Not defined
        echo "Called action unavailable.";
    }
    
    public function sendMail($body,$to,$attachments = array())
    {
    	
    	$db = new Application_Table_dbEmailAddress();
    	
    	$emailaddress = $db->getAddress();
    	
    	if(empty($emailaddress))
			$emailaddress = array(
				'email' => 'test@domain.com',
				'password' => 'test'
			);
    	
   		require_once(realpath(dirname(dirname(APPLICATION_PATH)) . '/public/phpmailer/class.phpmailer.php'));    	
	    $mail             = new PHPMailer();
	
		$mail->IsSMTP(); // telling the class to use SMTP
		$mail->SMTPDebug  = 2;                         // enables SMTP debug information (for testing)
		                                               // 1 = errors and messages
		                                               // 2 = messages only
		$mail->SMTPAuth   = true;                      // enable SMTP authentication
		$mail->SMTPSecure = "ssl";                     // sets the prefix to the servier
		$mail->Host       = $emailaddress['server'];          // sets GMAIL as the SMTP server
		$mail->Port       = $emailaddress['port'];                       // set the SMTP port for the GMAIL server
		$mail->Username   = $emailaddress['email'];    // GMAIL username
		$mail->Password   = $emailaddress['password']; // GMAIL password
		
		$mail->SetFrom($emailaddress['email'], 'Productivito Webapp');
		
		$mail->AddReplyTo($emailaddress['email'],"Productivito Webapp");
		//$mail->AddReplyTo("spam@productivito.org","Productivito Webapp");
		
		if(!empty($attachments))		
			$mail->Subject    = "Productivito Webapp - Rapoarte pe Mail";
		else
			$mail->Subject    = "Productivito Webapp - Alerte Mail";
		$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
		
		$mail->MsgHTML($body);
		
		foreach($to as $item)
			$mail->AddAddress($item,$item);
		
		if(!empty($attachments))	
		{
			foreach($attachments as $attachment)
			{
				$mail->AddAttachment($attachment);
			}
		}
			
		//$mail->AddAttachment("images/phpmailer.gif");      // attachment
		//$mail->AddAttachment("images/phpmailer_mini.gif"); // attachment
		
		if(!$mail->Send()) {
		  echo "Mailer Error: " . $mail->ErrorInfo;
		} else {
		  echo "Message sent!";
		}   	
    }
    
    public function getUsersWithHourDeviation($users,$time_deviation)
    {
    	$db = new Application_Table_dbUserToDepartments();   
    	$dbReports = new Application_Table_dbReports(); 
    	$users_return = array();	
    	foreach($users as $user)
    	{
    		$userDetails = $db->getUserFromDepartmentsByID($user);
    		
    		
    		$year = date('Y',time());
    		$month = date('m',time());

    		$options = array();
    		$options[0]['month'] = '07';
    		$options[5]['year'] = $year;
    		$options[2]['nhDeviation']['timepassingdeviation'] = $time_deviation[0];
    		$options[2]['nhDeviation']['leavingdeviation'] = $time_deviation[1];
    		$options[2]['nhDeviation']['leavingafterschedule'] = $time_deviation[2];
    		$result = $dbReports->GetCronHourDeviation("'".$userDetails['name']."'",$options);
    		
    		if($result > 0 )
    			$users_return[$userDetails['name']] = $result;
    		//echo "<pre>";echo $userDetails['name']." ";print_r($result);echo "</pre>";
    	}
    	//echo "<pre>";print_r($users_return);echo "</pre>";
    	//die();
    	
    	return $users_return;
    }
    
    public function getUsersWithInactivityDeviation($users,$time_deviation)
    {
    	$db = new Application_Table_dbUserToDepartments();   
    	$dbReports = new Application_Table_dbReports(); 
    	$users_return = array();	
    	foreach($users as $user)
    	{
    		$userDetails = $db->getUserFromDepartmentsByID($user);
    		
    		
    		$year = date('Y',time());
    		$month = date('m',time());

    		$options = array();
    		$options[0]['month'] = '07';
    		$options[5]['year'] = $year;
    		$options[3]['nInacDeviation']['inactivitydeviation'] = $time_deviation;
    		$result = $dbReports->GetCronInactivityDeviation("'".$userDetails['name']."'",$options);
    		
    		if($result > 0 )
    			$users_return[$userDetails['name']] = $result;
    		//echo "<pre>";echo $userDetails['name']." ";print_r($result);echo "</pre>";
    	}
    	//echo "<pre>";print_r($users_return);echo "</pre>";
    	//die();
    	
    	return $users_return;
    }   

    public function getUsersWithUnproductiveDeviation($users,$time_deviation)
    {
    	$db = new Application_Table_dbUserToDepartments();   
    	$dbReports = new Application_Table_dbReports(); 
    	$users_return = array();	
    	foreach($users as $user)
    	{
    		$userDetails = $db->getUserFromDepartmentsByID($user);
    		
    		
    		$year = date('Y',time());
    		$month = date('m',time());

    		$options = array();
    		$options[0]['month'] = '06';
    		$options[5]['year'] = $year;
    		$options[4]['nNprodDeviation']['nonproddeviation'] = $time_deviation;
    		$result = $dbReports->GetCronDeviationUnproductive("'".$userDetails['name']."'",$options);
    		
    		if($result > 0 )
    			$users_return[$userDetails['name']] = $result;
    		//echo "<pre>";echo $userDetails['name']." ";print_r($result);echo "</pre>";
    	}
    	//echo "<pre>";print_r($users_return);echo "</pre>";
    	
    	
    	return $users_return;
    }    
    
    public function getUserReports($user,$reportsResult,$reports)
    {
    	
    	$db = new Application_Table_dbDescribeAccess();
    	$temp_report = array();

    	$max = count($reports);
    	$j = 0;	

    	while($j < $max)
    	{
    		$temp_report = array();
    		
			$temp_report = $db->getReportByID($reports[$j]);
			
			$reportsResult[] = $this->generateReport($temp_report['name_access_level'],$user);	

			//echo "<pre>";print_r($reportsResult);echo "</pre>";die();
			$j++;
			
    	}
  		//echo "<pre>";print_r($reportsResult);echo "</pre>";
		return $reportsResult;
    }
    
    public function generateReport($report,$user)
    {
		
    	switch ($report){
    		case "report_computer":
    			return $this->pdfcron($user,"computer");
    		
    			break;
    		case "report_applications":
    			return $this->pdfcron($user,"applications");
    			break;
    		case "report_documents":
    			return $this->pdfcron($user,"documents");
    			break;
    		case "report_internet":
    			return $this->pdfcron($user,"internet");
    			break;
    		case "report_chat":
    			return $this->pdfcron($user,"chat");
    			break;
    		case "report_files":
    			return $this->pdfcron($user,"files");
    			break;
    		case "report_activity":
    			return $this->pdfcron($user,"activity");
    			break;
     		case "report_timekeeping":
     			return $this->pdfcron($user,"timekeeping");
    			break;
     		case "report_top":
     			return $this->pdfcron($user,"top");
    			break;
    		case "report_roi":
    			return $this->pdfcron($user,"roi");
    			break;
    		case "report_performance":
    			return $this->pdfcron($user,"performance");
    			break;    			   			   			   			   			    			    			    			    			    			
    	}
    }
    
    public function pdfcron($user,$action)
    {
		$path = '';
        $component = "usage";
        
    	$year = date('Y',time());
    	$month = date('m',time()); 
    	
    	//$month = '07';       
        if($action == 'timekeeping')
        {
        	
        	$filters['users'][0] = $user;
        	$filters['year'] = $year;
        	$filters['month'] = $month;
        }
        else 
        	if($action == 'performance'){
				$filters[0]['months'][0] = $month;
				$filters[1]['deviation_productivity'] = array(
                    0 => 'tHours',
                    1 => 'tAHours',
                    2 => 'tIHours',
                    3 => 'tSuplHours',
                    4 => 'tWeHours',
                    5 => 'tWeActHours',
                    6 => 'roi',
                    7 => 'tProd',
                    8 => 'tNProd'			
				);
				$filters[2]['nhDeviation'] = array(
                    'timepassingdeviation' => '01:00:00',
                    'leavingdeviation' => '01:00:00'
				);				
				$filters[3]['nInacDeviation'] = array(
                    'inactivitydeviation' => '01:00:00'
				);	
				$filters[4]['nNprodDeviation'] = array(
                    'nonproddeviation' => '01:00:00'
				);
				$filters[5]['year'] = $year;
				$filters[6]['users'][0] = $user;		
        	}
        	else
        	{
        		$filters['dateStart'] = $month.'/01/'.$year;
				$filters['dateEnd'] = $month.'/31/'.$year;
				
				$filters['interval'] = 'allweek';
				$filters['timeinterval'] = 'workallhours';
				$filters['users'][0] = $user;
        	}
        
        //echo "<pre>";print_r($filters);echo "</pre>";die();	
        	
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
        
		//echo "<pre>";print_r($data);echo "</pre>";die();
		/*print_r($action);echo "</br>";
		print_r($component);echo "</br>";
		print_r($filters);echo "</br>";die();
		*/
        $path = $this->_exportPdfDocument($action, $filters, $headers, $data, $component);
        
        return $path;
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
                        array('title' => 'Durata',            'type' => 'text',  'widthpercent' => self::MEDIUM),
                        array('title' => 'Utilizare %',       'type' => 'text',  'widthpercent' => self::MEDIUM),
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
                        array('title' => 'Cale citire',  'type' => 'text', 'widthpercent' => self::MEDIUM),
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
		//echo "<pre>";print_r($reportFilters);echo "</pre>";die();
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

    protected function _exportPdfDocument($action, &$filters, &$headers, &$data, $component)
    {
        
        $reportName = $this->getReportName($action);
        
        $currentTime = time();
        $timeAfterOneHour = $currentTime+60*60;
        
        $reportNameWithoutSpaces = str_replace(" ", "_", $reportName);
        // Create file name
        $filename = ucfirst($reportNameWithoutSpaces) . '_export-' . date('Y_m_d-').date('H_i_s',$timeAfterOneHour) . '.pdf';
        
		$exportPath = BASE_PATH . "\\public\\temporary_exports\\".$filename;
        // Build output
        $output = '';
        $export = array();
        if (!empty($filters)) {
            $this->_attachCsvFilters($action, $output, $filters);
        }
        if (!empty($headers)) {
            $this->_attachPdfHeader($export, $headers[$component]);
        }
		$i = 2;
		$k =1;
		//echo "<pre>";print_r($data);echo "<pre>";die();
		while(!empty($data[$component]) )
		{
			$j = 0;
			/*if($action == 'files')
				if($i % 5 == 0 )
				{
			        $currentTime = time();
			        $timeAfterOneHour = $currentTime+60*60;	
			        				
			        $filename = ucfirst($reportNameWithoutSpaces) . '_export-' . date('Y_m_d-').date('H_i_s',$timeAfterOneHour)."_partea".$k. '.pdf';
        					
					$this->generatePDF($filters, $action, $export, $reportName, $exportPath.$filename,"F");
					
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
				}*/			
	        if (!empty($data[$component])) foreach ($data[$component] as $item) {
	            $line = $this->_prepareLine($action, $component, $item);
	            //echo "<pre>";print_r($line);echo "<pre>";die();
	            $this->_attachPdfLine($export, $headers[$component], array_values($line),$j);
	            $j++;	
	        }
			//echo "<pre>";print_r($action);echo "<pre>";die();
			$data = $this->_getExportData($action, $filters,$i,200,$component);
			//echo "<pre>";print_r($data);echo "<pre>";die();
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

		$exportFilters = $this->_buildFilters($filters,$action);
        $output .= '<h3>Data generare raport : '.date('Y/m/d - H:i:s').'</h3>'.$exportFilters.'<table style="width:100%;border:1px solid #000000;border-spacing:0px;border-collapse:colapse">' . implode('', $export) . '</table>';
		//echo "<pre>";print_r($output);echo "<pre>";die();
        // Generate export file
        require_once(realpath(dirname(dirname(APPLICATION_PATH)) . '/thirdparty/mpdf/mpdf.php'));
        
		$this->generatePDF($output, $exportPath);
        
        return $exportPath;
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
    
    protected function generatePDF($output,$exportPath)
    {
        $stylesheet = file_get_contents(BASE_PATH.'/public/css/general.css');
        $mpdf = new mPDF('en-GB-x','A4','','',10,10,10,10,6,3); 
        $mpdf->debug=false;
		$mpdf->allow_charset_conversion=true;
		$mpdf->charset_in='windows-1252';
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->list_indent_first_level = 0;
        $mpdf->WriteHTML($stylesheet,1);
        $mpdf->WriteHTML($output);
        $mpdf->Output($exportPath, 'F');    	
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

    protected function _attachPdfHeader(&$export, &$headers)
    {
        $unit = $this->_calculateUnitSize($headers);
        $attachment = array();
        
        
        
        foreach ($headers as $column) {
            $attachment[] = '<th style="background-color:#CCCCCC;width:' . number_format($unit * floatval($column['widthpercent']), 2) . '%;">' . $column['title'] . '</th>';
        }
        array_push($export, '<tr>' . implode('', $attachment) . '</tr>');
    }

    protected function _attachPdfLine(&$export, &$headers, $line,$j)
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
                	
                	if(strlen($item['title']) >50 )
                	{
                		$item['title'] = substr($item['title'],0,50)."...";
                	}
                    if(strlen($item['link']) >70 )
                	{
                		$item['link'] = substr($item['link'],0,70)."...";
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
    
}
