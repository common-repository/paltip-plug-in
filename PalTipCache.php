<?php
	class PalTipCache{
	
		public function createCache(){
			global $wpdb;
			$res = $wpdb->get_results('DESC paltip_links;');		
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            if (!$res || mysql_errno()==1146){
			$sql = "CREATE TABLE paltip_links (               
					id int(11) NOT NULL AUTO_INCREMENT,                
					original_url varchar(1024) NOT NULL,    
                	pt_url varchar(1024) NOT NULL,          
                	use_count int(11) default 0,
                	creation_date timestamp,    
                	PRIMARY  KEY  (id),                       
                	KEY original_url (original_url(100))  
             		) ENGINE=InnoDB DEFAULT CHARSET=latin1";
			dbDelta($sql);
		}
		}
		
		public function destroyCache(){
			global $wpdb;
			$wpdb->get_results('drop table paltip_links;');		
		}
		
		public function emptyCache(){
			global $wpdb;
			$wpdb->get_results('truncate table paltip_links;');		
		}
		
		public function checkAndGetLinkTip( $url , $update_counter ){
			global $wpdb;
			$res = $wpdb->get_row("select * from paltip_links where original_url='$url';",ARRAY_A);
			if ( is_null($res)){
				return NULL;
			}
			if( $update_counter && !is_null($res['id'])){
				$wpdb->update('paltip_links',Array('use_count'=>($res['use_count']+1)),Array('id'=>$res['id']));
			}
			return $res['pt_url'];
		}
		
		public function checkAndGetOriginalTip( $url ){
			global $wpdb;
			$res = $wpdb->get_row("select * from paltip_links where pt_url='$url';",ARRAY_A);
			if ( is_null($res)){
				return NULL;
			}
			
			return $res['original_url'];
		}
		
		public function insertNewTip( $original_url, $paltip_url ){
			global $wpdb;
			$arr = Array('original_url'=>$original_url,'pt_url'=>$paltip_url,'use_count'=>1);
			return  $wpdb->insert('paltip_links',$arr);
		}
	}

?>