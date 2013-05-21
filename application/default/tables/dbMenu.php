<?php

class Application_Table_dbMenu extends Zend_Db_Table_Abstract {

    protected $_name = 'access_level';
    //protected $_primary = array('page_ID');
   // protected $_intLanguageID = 1;

    public function __construct() {
        parent::__construct();
    }
    
   public function getMenu($role){
   		
 		  /*$sql = "SELECT  * FROM access_level WHERE access = '".$role."'";
		
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
		
		$query = $this->select();
		$query->from('access_level')->where("access = ?", $role);
		$result = $this->fetchRow($query);
		
        if(!empty($result)) {
            $result = $result->toArray();

			return $result;
            //echo '<prE>'.print_r($result,true);die();
            
        }	
       
      	      
   }	
   
   public function getRole($id){
   	
 		 /* $sql = "SELECT  * FROM access_level WHERE id = '".$id."'";
		
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
		$query = $this->select();
		$query->from('access_level')->where("id = ?",$id);

		$result = $this->fetchRow($query);
		
        if(!empty($result)) {
            $result = $result->toArray();

			return $result;
            //echo '<prE>'.print_r($result,true);die();
            
        }		
   }
    public function getRoles()
    {
    	$query = $this->select();
		$query->from('access_level');
		
        $result = $this->fetchAll($query);
		$result = $result->toArray();
		
		$roles = array();
		
		foreach($result as $role)
		{
			$roles[$role['id']] = $role['access'];
			
		}
		//echo '<pre>'.print_r($roles,true).'</pre>';die();
        return $roles;
       
       /*
  		  $sql = "SELECT  * FROM access_level WHERE access = '".$role."'";
		
		  $link = mssql_connect('89.38.209.13', 'productivo', 'productivo');
		
		  mssql_select_db('productivo', $link);
		
		  $query = mssql_query($sql);
		
		  $aDataSql = array();
		
		  while ($row = mssql_fetch_assoc($query)) {
		
		  		 $aDataSql[$row['id']] = $row['access'];
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

	
    public function getAccessLevel()
    {
   		$query = $this->select();
		$query->from('access_level');
		
		$result = $this->fetchAll($query);
		
        if(!empty($result)) {
            $result = $result->toArray();

			return $result;
            //echo '<prE>'.print_r($result,true);die();
            
        }
		/*	
  		  $sql = "SELECT  * FROM access_level";
		
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
		  }         
        */		
    }
 
    public function getAccessLevelById($id)
    {
   		$query = $this->select();
		$query->from('access_level')->where('id = ?',$id);
		
		$result = $this->fetchRow($query);
		
        if(!empty($result)) {
            $result = $result->toArray();

			return $result;
            //echo '<prE>'.print_r($result,true);die();
            
        }
       
   		/*  $sql = "SELECT  * FROM access_level WHERE id = '".$id."'";
		
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
	public function deleteSelectedRoles($roles)
	{
	    //$link = mssql_connect('89.38.209.13', 'productivo', 'productivo');
   	   // mssql_select_db('productivo', $link);	
			
		for($i=0;$i<count($roles);$i++)
		{
			 $where = $this->getAdapter()->quoteInto("id = ?", $roles[$i]);
			 $this->delete($where);
	    	 $sql = " DELETE access_level FROM access_level WHERE id = ".$roles[$i];
	         $query = mssql_query($sql);				 
			 
		}		
	}
	
	public function deleteSelectedRole($role)
	{
		$where = $this->getAdapter()->quoteInto("id = ?", $role);
		$this->delete($where);
	   // $link = mssql_connect('89.38.209.13', 'productivo', 'productivo');
   	    //mssql_select_db('productivo', $link);
   	     
		//$sql = " DELETE access_level FROM access_level WHERE id = ".$role;
		//$query = mssql_query($sql);			
		
	}
	
	/*
    * Returns the rights that a role has on a page
	*/
	public function getRights($role,$page)
	{
		
  		 /* $sql = "SELECT  * FROM access_level WHERE access = '".$role."'";
		
		  $link = mssql_connect('89.38.209.13', 'productivo', 'productivo');
		
		  mssql_select_db('productivo', $link);
		
		  $query = mssql_query($sql);
		
		  $aDataSql = array();
		
		  while ($row = mssql_fetch_assoc($query)) {
		
		  		 return $row[$page];
		  }
		
		  mssql_free_result($query);
		  mssql_close($link); 
		   	*/	
		$query = $this->select();
		$query->from('access_level')->where('access = ?',$role)	;
		
		$result = $this->fetchRow($query);
        if(!empty($result)) {
            $result = $result->toArray();
			return $result[$page];
            //echo '<prE>'.print_r($result,true);die();
        }
          	
	}
	
	public function addRole($data)
	{
		$this->insert($data);
	  /*$sql = "INSERT access_level (access,report_computer,report_applications,report_documents,report_internet,report_chat,
	  							   report_files,report_activity,report_timekeeping,report_alerts,report_top,report_performance
	  							   ,report_roi,access_accounts,departments,role_management,emails,categories) VALUES
	  							    ('". $data['access']."','".
									$data['report_computer']."','"	.
									$data['report_applications']."'".
									$data['report_documents']."'".
									$data['report_internet']."','"	.
									$data['report_chat']."'".
									$data['report_files']."'".
									$data['report_activity']."','"	.
									$data['report_timekeeping']."'".
									$data['report_alerts']."'".
									$data['report_top']."','"	.
									$data['report_performance']."'".
									$data['report_roi']."'".
									$data['access_accounts']."','"	.
									$data['departments']."'".
									$data['role_management']."'".	
									$data['emails']."'".
									$data['categories']."','"																																															
									 .") ";
	
	  $link = mssql_connect('89.38.209.13', 'productivo', 'productivo');
	
	  mssql_select_db('productivo', $link);
	
	  $query = mssql_query($sql);
	
	  mssql_free_result($query);
	  mssql_close($link);   */ 		
	}
	
    public function updateRole($id,$data){
   	
        $where = $this->getAdapter()->quoteInto('id = ?', $id);
       
        $this->update($data, $where);		
 
   }
}

?>