<?php
	class LinkChanger{
		
		private $user_id;
		private $user_email;
		private $user_name;
		
		public function LinkChanger( $user_id=NULL, $user_email=NULL ,$user_name=NULL)
		{
			$this->user_email = $user_email;
			$this->user_id = $user_id;
			$this->user_name = $user_name;
		}

		public function changeLinksInContent( $content ){
			$orig_content = $content;
			try{
				$href_links = Array();
				$links = Array();
				$cached_links = Array();
				
				$ptn = "/(href=[^ ^>;]*)/i";
				preg_match_all($ptn, $content, $href_links);
				$links_str='';
				if(count($href_links[0])==0){
					return $content;
				}
				foreach($href_links[0] as $link){
					$pt_link = NULL;
					$clean_link = self::cleanHref($link);
					if(!is_null($clean_link) && strlen($clean_link)>6 ){
						$pt_link = PalTipCache::checkAndGetLinkTip($clean_link,true);
						if(is_null($pt_link)){
							$clean_link = urlencode($clean_link);
							$links[$clean_link]=$link;
							$links_str .= $clean_link.';!;';
						}else{
							$change_from = $link;
							$change_to = " href='$pt_link' ";
							$pos = strpos($content,$change_from);
							$content = str_replace($change_from,$change_to,$content);
							$counter++;
						}
					}
				}
				if ( strlen($links_str)>0){			
					$params = Array();
					$params['email'] = $this->user_email;
					$params['user_id'] = $this->user_id;
					$params['hear_about_us'] = publicFunctions::$PLUGIN_REFERRER;
					$params['blog'] = site_url();
					$params['url_list'] = $links_str;
		
					$response = publicFunctions::PostRequest(publicFunctions::$API_HOME_URL.'/createTips',$params,publicFunctions::$HOME_PORT,site_url());
			
					if(is_null($response[1]) || strlen($response[1])==0){
						return $content;
					}
			
					$dom_object = new DOMDocument();
					//	load xml file
				 	$dom_object->loadxml($response[1]);
		
				 	$status = $dom_object->getElementsByTagName("status");
				 	if(!is_null($status) && !is_null($status->item(0)) && $status->item(0)->getAttribute('code')=='ok' ){
				 		$user_id = $dom_object->getElementsByTagName("user")->item(0)->getElementsByTagName("id")->item(0)->nodeValue;
				 		$user_name = $dom_object->getElementsByTagName("user")->item(0)->getElementsByTagName("user_name")->item(0)->nodeValue;
				 		$link_list = $dom_object->getElementsByTagName("url");
				 		foreach($link_list as $link ){
				 			$original_link = $link->getElementsByTagName('original')->item(0)->nodeValue; 
				 			$pt_link = $link->getElementsByTagName('pt-link')->item(0)->nodeValue;
							$change_from = $links[urlencode($original_link)];
							$change_to = " href='$pt_link'";
							$pos = strpos($content,$change_from);
							$content = str_replace($change_from,$change_to,$content);
							PalTipCache::insertNewTip($original_link,$pt_link);
				 		}
	
				 		// in case the user changed his user name or id, i change all old posts as well
				 		if( $this->user_id!=$user_id || $this->user_name!=$user_name ){
				 			$this->user_id=$user_id;
				 			$this->user_name=$user_name;
				 			publicFunctions::setPTID($user_id);
				 			publicFunctions::setPTUserName($user_name);
				 			self::changeAllPostsLinks();
				 		}
				 	}
				}
				return $content;
			}catch(Exception $ex){
				return $orig_content;
			}
		}
		
		public function changeAllPostsLinks(){
			global $wpdb;
			try{
				foreach( $wpdb->get_results("SELECT * FROM wp_posts where post_content like '%href=%' and post_status='publish';") as $key => $row) {
					if(!current_user_can( 'edit_post', $row->ID )){
						continue;
					}
					if(strlen($row->post_content)>0){
						$content = $row->post_content;
						$content = $this->changeLinksInContent($content);
						self::updateLinksDB($content);
						$wpdb->update('wp_posts',array('post_content'=>$content),array('ID'=>$row->ID));
					}
				}
				return true;
			}catch(Exception $ex){
				// do nothing
			}
		}
		
		public function updateLinksToNewUser(){
			PalTipCache::emptyCache();
			global $wpdb;
			try{
				set_time_limit(600);
				foreach( $wpdb->get_results("SELECT * FROM wp_posts where post_content like '%href=%' and post_status='publish';") as $key => $row) {
					if(!current_user_can( 'edit_post', $row->ID )){
						continue;
					}
					if(strlen($row->post_content)>0){
						$content = $row->post_content;
						$content = $this->uninstallLinksInContent($content);
						$content = $this->changeLinksInContent($content);
						self::updateLinksDB($content);
						$wpdb->update('wp_posts',array('post_content'=>$content),array('ID'=>$row->ID));
					}
				}
			}catch(Exception $ex){
				// do nothing
			}
		}
		
		/* for old versions that no db was used*/
		public function updateLinksDB($content){
			$ptn = "/(pt_original_link=[^ ^>;]*)/i";
			// finding all href and delete them	
			preg_match_all($ptn, $content, $href_links);
			foreach($href_links[0] as $link){
				$clean_link = self::cleanHref($link);
				$pt_link = PalTipCache::checkAndGetOriginalTip($clean_link);
				if(is_null($pt_link)){
					$params = Array();
					$params['email'] = $this->user_email;
					$params['user_id'] = $this->user_id;
					$params['hear_about_us'] = publicFunctions::$PLUGIN_REFERRER;
					$params['blog'] = site_url();
					$params['url_list'] = $clean_link;
		
					$response = publicFunctions::PostRequest(publicFunctions::$API_HOME_URL.'/createTips',$params,publicFunctions::$HOME_PORT,site_url());
			
					if(is_null($response[1]) || strlen($response[1])==0){
						return $content;
					}
			
					$dom_object = new DOMDocument();
					//	load xml file
				 	$dom_object->loadxml($response[1]);
		
				 	$status = $dom_object->getElementsByTagName("status");
				 	if(!is_null($status) && !is_null($status->item(0)) && $status->item(0)->getAttribute('code')=='ok' ){
 						$link_list = $dom_object->getElementsByTagName("url");
				 		foreach($link_list as $link ){
			 				$original_link = $link->getElementsByTagName('original')->item(0)->nodeValue; 
			 				$pt_link = $link->getElementsByTagName('pt-link')->item(0)->nodeValue;
							PalTipCache::insertNewTip($original_link,$pt_link);
				 		}
				 	}
				}
			}
		}
		
		public function uninstallLinksInContent( $content ){
			$ptn = "/(href=[^ ^>;]*)/i";
			// finding all href and delete them	
			preg_match_all($ptn, $content, $href_links);
			foreach($href_links[0] as $link){
				if(strpos($link,'paltip')>0){
					$clean_link = self::cleanHref($link);
					$pt_link = PalTipCache::checkAndGetOriginalTip($clean_link);
					if(!is_null($pt_link)){
						$change_from = $link;
						$change_to = " href='$pt_link' ";
						$content = str_replace($change_from,$change_to,$content);
					}
				}
			}
	
			return $content;
		}
		
		public function uninstallLinksInAllPosts(){
			global $wpdb;
			try{
				foreach( $wpdb->get_results("SELECT * FROM wp_posts where post_status='publish' and post_content like '%paltip%';") as $key => $row) {
					if(!current_user_can( 'edit_post', $row->ID )){
						continue;
					}
					if(strlen($row->post_content)>0){
						$content = $row->post_content;
						$content = $this->uninstallLinksInContent($content);
						$wpdb->update('wp_posts',array('post_content'=>$content),array('ID'=>$row->ID));
					}
				}
			}catch(Exception $ex){
				// do nothing
			}
		}
		
		public function getNumberOfTipsAndPosts(){
			global $wpdb;
			try{
				set_time_limit(600);
				$res = Array();
				$res = $wpdb->get_row("select ceil(sum((length(post_content) - length(replace(post_content, 'paltip', '')))/length('paltip'))) as number_of_paltip_links,count(*) as number_of_posts from wp_posts where post_status='publish';",'ARRAY_A');
				return $res;
			}catch(Exception $ex){
				// do nothing
			}
			return NULL;
		}
		
		private function cleanHref ($text){
			$text = trim($text);
			$text = substr($text,strpos($text,'http'));
			$len = strlen($text);
			if($text{$len-1}=='"' || $text{$len-1}=='\''){
				$text = substr($text,0,$len-1);
			}
			if($text{$len-2}=='\\' ){
				$text = substr($text,0,$len-2);
			}
			if(strpos($text,'.jpg')>5 || strpos($text,'.gif')>5 || strpos($text,'.png')>5 || strpos($text,'.bmp')>5 || strpos($text,'.jpeg')>5){
				return NULL;
			}
			return $text;
		}
	}

?>