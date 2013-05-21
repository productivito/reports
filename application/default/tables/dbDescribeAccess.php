<?php

class Application_Table_dbDescribeAccess extends Zend_Db_Table_Abstract {

    protected $_name = 'access_describe';
    //protected $_primary = array('page_ID');
   // protected $_intLanguageID = 1;

    public function __construct() {
        parent::__construct();
    }
	
	
	/*
	 * returns the description of a Menu Button for witch the user has access
	 */
	public function getMenuElement($column,$type){
		
		$query = $this->select();
		$query->from('access_describe')->where("name_access_level = ?", $column)
			  ->where("type = ?", $type);
			  
		$result = $this->fetchRow($query);
		
		if(!empty($result))
		{
			$result = $result->toArray();
			
			return $result;
		}
		
		return array();
		
		
		 /* $sql = "SELECT  * FROM access_describe WHERE name_access_level = '".$column."' AND ".
		  											   "type = '".$type."'";
		
		  $link = mssql_connect('89.38.209.13', 'productivo', 'productivo');
		
		  mssql_select_db('productivo', $link);
		
		  $query = mssql_query($sql);
		
		  $aDataSql = array();
		
		  while ($row = mssql_fetch_assoc($query)) {
		
		   $aDataSql[] = $row;
		  }
		
		  mssql_free_result($query);
		  mssql_close($link);
		   if (!empty($aDataSql)){
		         return $aDataSql;
		  }
		   else{
		         return false;
		  }		*/

	}
	
	/*
	 * returns all the rows of type sent as parameter
	 */
	public function getAllOfType($type)
	{
		$query = $this->select();
		$query->from('access_describe')->where("type = ?", $type);
			  
		$result = $this->fetchAll($query);
		
		if(!empty($result))
		{
			$result = $result->toArray();
			
			return $result;
		}
		
		return array();	
		
		
		
		/*  $sql = "SELECT  * FROM access_describe WHERE type = '".$type."'";
		
		  $link = mssql_connect('89.38.209.13', 'productivo', 'productivo');
		
		  mssql_select_db('productivo', $link);
		
		  $query = mssql_query($sql);
		
		  $aDataSql = array();
		
		  while ($row = mssql_fetch_assoc($query)) {
		
		   $aDataSql[] = $row;
		  }
		
		  mssql_free_result($query);
		  mssql_close($link);
		   if (!empty($aDataSql)){
		         return $aDataSql;
		  }
		   else{
		         return false;
		  }*/			
	}
	
	
	public function getAllReports()
	{
		$query = $this->select();
		$query->from('access_describe')->where("type = 'm' OR type = 'a'");
			  
		$result = $this->fetchAll($query);
		
		if(!empty($result))
		{
			$result = $result->toArray();
			
			return $result;
		}
		
		return array();	
	}
	
	public function getReportByID($id)
	{
		$query = $this->select();
		
		$query->from('access_describe')->where("id = ?", $id);
			  
		$result = $this->fetchRow($query);
		
		if(!empty($result))
		{
			$result = $result->toArray();
			
			return $result;
		}
		
		return array();	
	}	
	
	
}
?>