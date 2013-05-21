<?php

class Application_Model_pages{

    protected $_db;

    public function __construct() {
        $this->_db = Zend_Registry::get('db');
    }

    public function getAllContent() {
            $sql = 'SELECT * FROM pages inner join page_resources on pages.page_ID = page_resources.page_ID ORDER BY pages.`page_ID`';
            $result = $this->_db->query($sql);
            $aDealers = $result->fetchAll();

            return $aDealers;
        }
        
    public function getContentData($contentId) {
    	
        $sql = "SELECT * FROM pages inner join page_resources on pages.page_ID = page_resources.page_ID where pages.`page_ID` = ".$contentId." ORDER BY pages.`page_ID`";
        $result = $this->_db->query($sql);
        $aData = $result->fetchAll();
		return $aData[0];
        }
   
	public function saveContent($aValues){	
		$aData=array('page_resources_left'=>$aValues['page_resources_left'],
					 'page_resources_right'=>$aValues['page_resources_right']
					);
		
			$contentId = $aValues['page_ID'];
			$where = $this->_db->quoteInto('page_ID = ?', $contentId);
			$this->_db->update('page_resources', $aData, $where);
			
		return $aValues;
	}


}

?>