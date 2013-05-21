<?php

class Application_Model_offers{

    protected $_db;

    public function __construct() {
        $this->_db = Zend_Registry::get('db');
    }


        
    public function getAllOffers() {
            $sql = 'SELECT * FROM offers ORDER BY `order` ';
            $result = $this->_db->query($sql);
            return $result->fetchAll();
            
        }
	public function _createThumbnail($source_image_path) {

		    define( 'THUMBNAIL_IMAGE_MAX_WIDTH', 133 );
  			define( 'THUMBNAIL_IMAGE_MAX_HEIGHT', 100 );

			list( $source_image_width, $source_image_height, $source_image_type ) = getimagesize( $source_image_path );
		
		    switch ( $source_image_type )
		    {
		      case IMAGETYPE_GIF:
		        $source_gd_image = imagecreatefromgif( $source_image_path );
		        break;
		
		      case IMAGETYPE_JPEG:
		        $source_gd_image = imagecreatefromjpeg( $source_image_path );
		        break;
		
		      case IMAGETYPE_PNG:
		        $source_gd_image = imagecreatefrompng( $source_image_path );
		        break;
		    }
		
		    if ( $source_gd_image === false )
		    {
		      return false;
		    }
		
		    $thumbnail_image_width = THUMBNAIL_IMAGE_MAX_WIDTH;
		    $thumbnail_image_height = THUMBNAIL_IMAGE_MAX_HEIGHT;
		
		    $source_aspect_ratio = $source_image_width / $source_image_height;
		    $thumbnail_aspect_ratio = $thumbnail_image_width / $thumbnail_image_height;
		
		    if ( $source_image_width <= $thumbnail_image_width && $source_image_height <= $thumbnail_image_height )
		    {
		      $thumbnail_image_width = $source_image_width;
		      $thumbnail_image_height = $source_image_height;
		    }
		    elseif ( $thumbnail_aspect_ratio > $source_aspect_ratio )
		    {
		      $thumbnail_image_width = ( int ) ( $thumbnail_image_height * $source_aspect_ratio );
		    }
		    else
		    {
		      $thumbnail_image_height = ( int ) ( $thumbnail_image_width / $source_aspect_ratio );
		    }
		
		    $thumbnail_gd_image = imagecreatetruecolor( $thumbnail_image_width, $thumbnail_image_height );
		
		    imagecopyresampled( $thumbnail_gd_image, $source_gd_image, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $source_image_width, $source_image_height );
		
		    imagejpeg( $thumbnail_gd_image, $source_image_path, 90 );
		
		    imagedestroy( $source_gd_image );
		
		    imagedestroy( $thumbnail_gd_image );
		
		    return true;


	}
	public function getInterestsByOfferId($id){
	
			$sql =$this->_db->select()
							->from(array('i' => 'interests'),array('id'=>'i.id','name'=>'i.name', 'order'=>'i.order'))
							->joinLeft(array('oi' => 'offer_interests'),
							'i.id = oi.id_interest' )
							->joinLeft(array('o' => 'offers'),
							'oi.id_offer = o.id')
							->group('i.order');




			
				$sql->where('o.id = ?', $id);
			
			
							
			//die(var_dump($this->_db->fetchAll($sql)));
	
		 	return $this->_db->fetchAll($sql);
	
	}
    
	public function getInterestsNotOfOfferId($id, $array_of_interest){
	
			$sql =$this->_db->select()
							->from(array('i' => 'interests'),array('id'=>'i.id','name'=>'i.name', 'order'=>'i.order'));


		$interests = Array();

			foreach($array_of_interest as $key =>$data){

				
				$interests[] = $data["id_interest"];
				
			
			}	
			$sql->where('i.id NOT IN(?)',$interests);
							
			//die(var_dump($this->_db->fetchAll($sql)));
	
		 	return $this->_db->fetchAll($sql);
	
	}	
	
	public function getInterests(){
	
		$sql = $this->_db->select()
					->from(array('i' => 'interests'),array('id'=>'i.id','name'=>'i.name', 'order'=>'i.order'));
		
				 	return $this->_db->fetchAll($sql);
	
	}
	
	public function saveInterestsOffer($id,$array_of_interests){
	//echo "1_".$id.'_';
		 $where = array();
		 $where[]= $this->_db->quoteInto('id_offer = ?' , $id);
		 $this->_db->delete('offer_interests',$where );
		
		 
	//echo "2";
		foreach($array_of_interests as $interest){
		
			$aData=array('id_offer'=>$id,'id_interest'=>$interest);
			$this->_db->insert('offer_interests', $aData);
	//echo "a_".$interest.'_';
		}
	//echo "3";
	}
	
    public function setOffersOrder($id, $order){
    
    	$aData=array(
			'order'=>($order+1),
		);
		$where = $this->_db->quoteInto('id = ?', $id);
		$this->_db->update('offers', $aData, $where);
    
    }     
        
   
        
	public function getOffersData($nFaqId) {			
		$sql =$this->_db->select()
						->from(array('o' => 'offers'))
						->where('o.id = ?', $nFaqId);
	
	 	$aResult= $this->_db->fetchAll($sql);
		//print_r($aResult);
		$aPageData=array();
		foreach ($aResult as $row){
			$aPageData['id']=$row['id'];
			$aPageData['title']=$row['title'];
			$aPageData['short_description']=$row['short_description'];
			$aPageData['offers_contact_company']=$row['offers_contact_company'];
			$aPageData['offers_contact_street']=$row['offers_contact_street'];
			$aPageData['offers_contact_city']=$row['offers_contact_city'];
			$aPageData['offers_contact_country']=$row['offers_contact_country'];
			$aPageData['offers_contact_phone']=$row['offers_contact_phone'];
			$aPageData['content']=$row['content'];
			
			$aPageData['img']=$row['img'];
                        $aPageData['start_date']=$row['start_date'];
                        $aPageData['end_date']=$row['end_date'];
                        $aPageData['active']=$row['active'];

                        }
  
                 return $aPageData;
                                    }
          

    public function deleteOffers($nFaqId) {
		 $where = array();
		 $where[]= $this->_db->quoteInto('id = ?' , $nFaqId);
		 $this->_db->delete('offers',$where );

		 


		 
        }
  
	
	
	public function getNextOrder(){
	
		$sql =$this->_db->select()
						->from('offers',new Zend_Db_Expr("MAX(`order`)+1 as max"));
							
	 	$data = $this->_db->fetchOne($sql);
	 	if($data==NULL) $data = 1;
		return $data;
	
	}
	
	

	public function saveoffer($aValues){	
	


		if ( $aValues['id']==0){
			$aData=array('title'=>$aValues['title'],
                                        'content'=>$aValues['content'],
                                        'offers_contact_company'=>$aValues['offers_contact_company'],
                                        'offers_contact_street'=>$aValues['offers_contact_street'],
                                        'offers_contact_city'=>$aValues['offers_contact_city'],
                                        'offers_contact_country'=>$aValues['offers_contact_country'],
                                        'offers_contact_phone'=>$aValues['offers_contact_phone'],
                                        
                                        'short_description'=>$aValues['short_description'],
                                        'img'=>$aValues['img'],
                                        'pdf'=>$aValues['pdf'],
                                        'start_date'=>$aValues['start_date'],
                                        'end_date'=>$aValues['end_date'],
                                        'active'=>$aValues['active'],
                                        'order'=>$this->getNextOrder(),
										'creation_date'=>date('Y-m-d H:i:s')
						);

			$this->_db->insert('offers', $aData);
			$nPageIdx = $this->_db->lastInsertId();
			$aValues['id']=$nPageId;
			return $aValues;
		}
		else{

			$nPageId = $aValues['id'];
			
				$aData=array(
						'title'=>$aValues['title'],
                                        'offers_contact_company'=>$aValues['offers_contact_company'],
                                        'offers_contact_street'=>$aValues['offers_contact_street'],
                                        'offers_contact_city'=>$aValues['offers_contact_city'],
                                        'offers_contact_country'=>$aValues['offers_contact_country'],
                                        'offers_contact_phone'=>$aValues['offers_contact_phone'],
										'short_description'=>$aValues['short_description'],
										'content'=>$aValues['content'],
                                                'start_date'=>$aValues['start_date'],
                                                'end_date'=>$aValues['end_date'],
                                                'active'=>$aValues['active']
						);
			
			if(!empty($aValues['img'])){
				$aData['img'] = $aValues['img'];
			}
			
			if(!empty($aValues['pdf'])){
				$aData['pdf'] = $aValues['pdf'];
			}
			
				
			$where = $this->_db->quoteInto('id = ?', $aValues['id']);
			$this->_db->update('offers', $aData, $where);
			
/*		 echo "<pre>";
        var_dump($aData);
        echo "</pre>";
        die();	*/	
			
			return $aValues;
		}
		
	}

}