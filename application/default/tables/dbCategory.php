<?php

class Application_Table_dbCategory extends Zend_Db_Table_Abstract {

    protected $_db;
    protected $_name = 'categories';
    protected $_id = 'id';

    public function __construct() {
        $this->_db = Zend_Registry::get('db');
    }

   public function getAllCategories(){
 
         $query = $this->select();
		 $query->from('categories');
        $result = $this->fetchAll($query);
        //echo '<prE>'.print_r($result,true);die();
        if(!empty($result)) {
            $result = $result->toArray();

            //echo '<prE>'.print_r($result,true);die();
            return $result;
        }
        
        return array();  	
   }
   
   public function getNCategories($page,$numberRowsPerPage){
 
         $query = $this->select();
		 $query->from('categories')	
		 	   ->limit($numberRowsPerPage,($page-1)*$numberRowsPerPage +$numberRowsPerPage);
        $result = $this->fetchAll($query);
        //echo '<prE>'.print_r($result,true);die();
        if(!empty($result)) {
            $result = $result->toArray();

            //echo '<prE>'.print_r($result,true);die();
            return $result;
        }
        
        return array();  	
   }   

   public function getCategoriesNumber(){
 
         $query = $this->select();
		 $query->from('categories',array('count(*) as amount'));
        $result = $this->fetchAll($query);
        //echo '<prE>'.print_r($result,true);die();
        if(!empty($result)) {
            $result = $result->toArray();

            //echo '<prE>'.print_r($result,true);die();
            return $result[0]['amount'];
        }
        
        return 0;  	
   }  
   
   function getCategoryDetails($id,$category)
   {

        $query = $this->select();
		$query->from('categories')->where("id = ?", $id)
		 		->where("category = ?",$category);
        $result = $this->fetchRow($query);
        //echo '<prE>'.print_r($result,true);die();
        if(!empty($result)) {
            $result = $result->toArray();

            //echo '<prE>'.print_r($result,true);die();
            return $result;
        }
        
        return array();  
					    
   }

   function getAllCategoriesByName($name)
   {
        $query = $this->select();
        $query->from('categories')
            ->where("name LIKE ?", $name);
        $result = $this->fetchRow($query);
        return empty($result) ? array() : $result->toArray();
   }   
   
   function getCategoryByName($name, $category)
   {
        $query = $this->select();
        $query->from('categories')
            ->where("name LIKE ?", $name)
            ->where("category LIKE ?", $category);
        $result = $this->fetchRow($query);
        return empty($result) ? array() : $result->toArray();
   }   
   
   function getCategoryBytypes($category)
   {

        $query = $this->select();
		$query->from('categories')
		 		->where("category = ?",$category);
        $result = $this->fetchAll($query);
        //echo '<prE>'.print_r($result,true);die();
        if(!empty($result)) {
            $result = $result->toArray();

            //echo '<prE>'.print_r($result,true);die();
            return $result;
        }
        
        return array();  
					    
   } 

   function setCategoriesType($categories,$type)
   {
   		$data = array(
			'type' => $type
		);

   		foreach($categories as $category)
		{
			
	   		$where = $this->getAdapter()->quoteInto('id = ?', $category);
	        
	        $this->update($data, $where);			
		}
   } 

   function setCategoryType($category,$type)
   {
   		
   		$data = array(
			'type' => $type
		);

   		$where = $this->getAdapter()->quoteInto('name = ?', $category);
        
        $this->update($data, $where);			

   } 
   
   function updateCategory($id,$data)	
   {
   		$where = $this->getAdapter()->quoteInto('id = ?', $id);
        
        $this->update($data, $where);
		
   }
   
   function addCategory($data)
   {
		$this->insert($data);
		

   }
   
   function deleteSelectedCategory($id)
   {
	   $where = $this->getAdapter()->quoteInto("id = ?", $id);

	    $this->delete($where);

	    /*$link = mssql_connect('89.38.209.13', 'Productivito', 'Productivito');
   	    mssql_select_db('Productivito', $link);
   	     
		$sql = " DELETE categories FROM categories WHERE id = ".$id;
		$query = mssql_query($sql);	*/
	  
	
	 
				 		
   }
 
 
   function deleteSelectedCategories($categories)
   {
   	   //$link = mssql_connect('89.38.209.13', 'Productivito', 'Productivito');
   	   //mssql_select_db('Productivito', $link);
   	   
		for($i=0;$i<count($categories);$i++)
		{
			 $where = $this->getAdapter()->quoteInto("id = ?", $categories[$i]);
			 $this->delete($where);
			// $sql = " DELETE categories FROM categories WHERE id = ".$categories[$i];
			// $query = mssql_query($sql);
			 
		}		
   }  

	function getCategoriesByRawName($name)
	{
        $query = $this->select();
		$query->from('categories')->where("name = ?", $name);
        $result = $this->fetchRow($query);
        //echo '<prE>'.print_r($result,true);die();
        if(!empty($result)) {
            $result = $result->toArray();

            //echo '<prE>'.print_r($result,true);die();
            return $result;
        }
        
        return array(); 		
	}

	function insertSitesInCategory($data)
	{
		
				/*echo "<pre>";
				print_r($data);
				echo "</pre>";die();*/
						
		$i = 0;
		$db = new Application_Table_dbCategory();
		foreach($data as $site)
		{
			//if($i < 222){
			if(!empty($site['name']))
				$ok = $db->getCategoriesByRawName($site['name']);
			
			//print_r($ok);die();
			if(empty($ok))
				//print_r($site);die();
				$this->insert($site);
			$i++;
			//}
		}
		
	}
	
	function insertApplicationsInCategory($data)
	{
		$i = 0;
		$db = new Application_Table_dbCategory();
		foreach($data as $application)
		{
			if(!empty($application['name']))
				$ok = $db->getCategoriesByRawName($application['name']);
			//print_r($ok);die();
			if(empty($ok))
				//print_r($site);die();
				$this->insert($application);

			
		}
	}	
	
	function insertChatsInCategory($data)
	{
		$i = 0;
		$db = new Application_Table_dbCategory();
		foreach($data as $application)
		{
			if(!empty($application['name']))
				$ok = $db->getCategoriesByRawName($application['name']);
			//print_r($ok);die();
			if(empty($ok))
				//print_r($site);die();
				$this->insert($application);

			
		}
	}
	/*
	 * Gets all the categories whith the selected filter
	 */   
   function getFilterdCategories($selectedCateg, $filtru1, $filtru2, $filtru3,$page,$numberRowsPerPage,$flag = 1)
   {
   		//$link = mssql_connect('89.38.209.13', 'Productivito', 'Productivito');
	
	    //mssql_select_db('Productivito', $link);
	    
	   // print_r(($page-1)*$numberRowsPerPage +$numberRowsPerPage);die();
	   if($page == 1)
	   		$end = 0;
	   	else 
	   		$end = ($page-2)*$numberRowsPerPage +$numberRowsPerPage;
   		$query = $this->select();
		if($flag == 1)
				$query->limit($numberRowsPerPage,$end);
		$filters = array();
		
		if (!empty($filtru1))
			array_push($filters, $filtru1);
		
		if (!empty($filtru2))
			array_push($filters, $filtru2);
		
		if (!empty($filtru3))
			array_push($filters, $filtru3);
		
		$query->from('categories');
		
		if (!empty($filters))
		{
			$query->where('type IN (?)', $filters);
		}
		
		if (!empty($selectedCateg))
		{
			$query->where('category = ?', $selectedCateg);
		}
		
		$result = $this->fetchAll($query);

        //echo '<prE>'.print_r($result,true);die();
        if(!empty($result))
        {
            $result = $result->toArray();

            return $result;
        }
        
        return array();				
   }
   
   function getCategoriesNumberFilters($selectedCateg, $filtru1, $filtru2, $filtru3)
   {
   		//$link = mssql_connect('89.38.209.13', 'Productivito', 'Productivito');
	
	    //mssql_select_db('Productivito', $link);
   		$query = $this->select();
		$filters = array();
		
		if (!empty($filtru1))
			array_push($filters, $filtru1);
		
		if (!empty($filtru2))
			array_push($filters, $filtru2);
		
		if (!empty($filtru3))
			array_push($filters, $filtru3);
		
		$query->from('categories',array('COUNT(*) as amount'));
		
		if (!empty($filters))
		{
			$query->where('type IN (?)', $filters);
		}
		
		if (!empty($selectedCateg))
		{
			$query->where('category = ?', $selectedCateg);
		}
		
		$result = $this->fetchAll($query);

        //echo '<prE>'.print_r($result,true);die();
        if(!empty($result))
        {
            $result = $result->toArray();

            return $result[0]['amount'];;
        }
        
        return 0;				
   }   
   
}