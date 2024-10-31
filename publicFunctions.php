<?php

class publicFunctions{
	
	public static $PALTIP_UID = 'paltip_uid';
	public static $PALTIP_USER_NAME = 'paltip_user_name';
	public static $PALTIP_EMAIL = 'paltip_email';
	public static $PALTIP_ACTIVE = 'paltip_active';
	public static $PALTIP_LINK_COUNTER = 'paltip_link_counter';
	public static $PALTIP_PLUGIN_VERSION = 'paltip_plugin_version';
	public static $PLUGIN_ACTIVETED = 'paltip_plugin_activated';
		
	public static $PLUGIN_VERSION = '1.1.3';
	public static $PLUGIN_NAME = 'PalTip';
	public static $PLUGIN_REFERRER = 'wp-plugin';

	public static $HOME_URL = 'http://paltip.com';
	public static $API_HOME_URL = 'http://api.paltip.com';
	public static $HOME_PORT = '80';
	
	public static function PostRequest($url, $_data , $port=80 ,$referer=NULL) {
		// convert variables array to string:
		$_data['version'] = self::$PLUGIN_VERSION;
		$_data['plugin_name'] = self::$PLUGIN_REFERRER;
		
		$data = array();
		while(list($n,$v) = each($_data)){
			$data[] = "$n=$v";
		}
		$data = implode('&', $data);
	
		// parse the given URL
		$url = parse_url($url);
		if ($url['scheme'] != 'http') {
			die('Only HTTP request are supported !');
		}
	
		// extract host and path:
		$host = $url['host'];
		$path = $url['path'];
		$query = $url['query'];
	
		// 	open a socket connection on port 80
		$fp = fsockopen($host, $port);
		// send the request headers:
		fputs($fp, "POST $path?$query HTTP/1.0\r\n");
		fputs($fp, "Host: $host\r\n");
		//fputs($fp, "Referer: $referer\r\n");
		fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
		fputs($fp, "Content-length: ". strlen($data) ."\r\n");
		fputs($fp, "Connection: close\r\n\r\n");
		fputs($fp, $data);
	
		$result = '';
		while(!feof($fp)) {
		// 	receive the results of the request
			$result .= fgets($fp, 128);
		}
	
		// 	close the socket connection:
		fclose($fp);
	
		// split the result header from the content
		$result = explode("\r\n\r\n", $result, 2);
	
		$header = isset($result[0]) ? $result[0] : '';
		$content = isset($result[1]) ? $result[1] : '';
	
		// 	return as array:
		return array($header, $content);
	}
	
	public static function createOptions(){
		update_option(self::$PALTIP_UID, '', '', 'yes');
		update_option(self::$PALTIP_USER_NAME, '', '', 'yes');
		update_option(self::$PALTIP_EMAIL, get_option( 'admin_email' ), '', 'yes');
		update_option(self::$PALTIP_ACTIVE, '0', '', 'yes');
		update_option(self::$PALTIP_PLUGIN_VERSION, self::$PLUGIN_VERSION, '', 'yes');
		update_option(self::$PLUGIN_ACTIVETED, '0', '', 'yes');
	}
	
	public static function updateOptions(){
		if(self::getPTID()==NULL){update_option(self::$PALTIP_UID, '', '', 'yes');}
		if(self::getPTUserName()==NULL){update_option(self::$PALTIP_USER_NAME, '', '', 'yes');}
		if(self::getPTEmail()==NULL){update_option(self::$PALTIP_EMAIL, '', '', 'yes');}
		if(self::getPTActive()==NULL){update_option(self::$PALTIP_ACTIVE, '', '', 'yes');}
		if(self::getPluginActivated()==NULL){update_option(self::$PLUGIN_ACTIVETED, '', '', 'yes');}
	}
	
	public static function deleteOptions(){
		self::deletePTActive();
		self::deletePTEmail();
		self::deletePTID();
		self::deletePTUserName();
		self::deletePTPluginVersion();
		self::deletePluginActivated();
	}
	
	public static function setPTID($user_id){
		return update_option(self::$PALTIP_UID, $user_id);
	}
	
	public static function getPTID(){
		return get_option(self::$PALTIP_UID);
	}
	
	public static function deletePTID(){
		return delete_option(self::$PALTIP_UID);
	}
	
	public static function setPTUserName($user_name){
		return update_option(self::$PALTIP_USER_NAME, $user_name);
	}
	
	public static function getPTUserName(){
		return get_option(self::$PALTIP_USER_NAME);
	}
	
	public static function deletePTUserName(){
		return delete_option(self::$PALTIP_USER_NAME);
	}
	
	public static function setPTEmail($email){
		return update_option(self::$PALTIP_EMAIL, $email);
	}
	
	public static function getPTEmail(){
		return get_option(self::$PALTIP_EMAIL);
	}
	
	public static function deletePTEmail(){
		return delete_option(self::$PALTIP_EMAIL);
	}
	
	public static function setPTActive($val){
		return update_option(self::$PALTIP_ACTIVE, $val);
	}
	
	public static function getPTActive(){
		return get_option(self::$PALTIP_ACTIVE);
	}
	public static function deletePTActive(){
		return delete_option(self::$PALTIP_ACTIVE);
	}
	
	public static function setPluginActivated($val){
		return update_option(self::$PLUGIN_ACTIVETED, $val);
	}
	
	public static function getPluginActivated(){
		return get_option(self::$PLUGIN_ACTIVETED);
	}
	
	public static function deletePluginActivated(){
		return delete_option(self::$PLUGIN_ACTIVETED);
	}
	
	public static function setPTPluginVersion($val){
		return update_option(self::$PALTIP_PLUGIN_VERSION, $val);
	}
	
	public static function getPTPluginVersion(){
		return get_option(self::$PALTIP_PLUGIN_VERSION);
	}
	
	public static function deletePTPluginVersion(){
		return delete_option(self::$PALTIP_PLUGIN_VERSION);
	}
	
	public static function getUserInfo($id){
		$params = Array();
		$params['id'] = $id;
		$response = publicFunctions::PostRequest(self::$API_HOME_URL.'/getUserInfo',$params,self::$HOME_PORT,site_url());
		if(is_null($response[1]) || strlen($response[1])==0){
			return NULL;
		}
		$array = Array();
		try{		
			$dom_object = new DOMDocument();
			$dom_object->loadxml($response[1]);
			$status = $dom_object->getElementsByTagName("status");
			if(!is_null($status) && !is_null($status->item(0)) && $status->item(0)->getAttribute('code')=='ok' ){
				$user_node = $dom_object->getElementsByTagName('user')->item(0);
				$array['first_name'] = $user_node->getElementsByTagName('first_name')->item(0)->nodeValue;
				$array['last_name'] = $user_node->getElementsByTagName('last_name')->item(0)->nodeValue;
				$array['user_name'] = $user_node->getElementsByTagName('user_name')->item(0)->nodeValue;
				$array['total_balance'] = $user_node->getElementsByTagName('total_balance')->item(0)->nodeValue;
				$array['profile_picture'] = $user_node->getElementsByTagName('profile_picture')->item(0)->nodeValue;
				$array['tips_count'] = $user_node->getElementsByTagName('tips_count')->item(0)->nodeValue;
				$array['api_token'] = $user_node->getElementsByTagName('api_token')->item(0)->nodeValue;
			}
		}catch(Exception $exp){ return NULL; }
		return $array;
	}
	
	function deactivateUser(){
		$params['user_id'] = self::getPTID();
		publicFunctions::PostRequest(self::$HOME_URL.'/ajax/deactivateWPPlugin',$params,self::$HOME_PORT,site_url());
	}
	
	function pluginActivated(){
		$params['email'] = get_option( 'admin_email' );
		
		$params['activated'] = 1;
		$params['installed'] = 1;
		publicFunctions::PostRequest( self::$HOME_URL.'/ajax/wordpress/pluginInstalledOrActivated',$params,self::$HOME_PORT,site_url());
		self::setPluginActivated(1);
	}
	
	function getUserLinks($id){
		$params = Array();
		$params['id'] = $id;
		$response = publicFunctions::PostRequest(self::$API_HOME_URL.'/getUserLinks',$params,self::$HOME_PORT,site_url());
		if(is_null($response[1]) || strlen($response[1])==0){
			return NULL;
		}
		$array = Array();
		try{		
			$dom_object = new DOMDocument();
			$dom_object->loadxml( $response[1] );
			if(!is_null($dom_object)){
				$status = $dom_object->getElementsByTagName("status");
				if(!is_null($status) && !is_null($status->item(0)) && $status->item(0)->getAttribute('code')=='ok' ){
					$url_elements = $dom_object->getElementsByTagName('shortenUrl');
					foreach($url_elements as $element){
						$tmp_array = Array();
						$tmp_array['view_count'] = $element->getElementsByTagName('view_count')->item(0)->nodeValue;		
						$tmp_array['product_commision'] = $element->getElementsByTagName('product_commision')->item(0)->nodeValue;
						$tmp_array['purches_count'] = $element->getElementsByTagName('purches_count')->item(0)->nodeValue;
						$tmp_array['url_title'] = $element->getElementsByTagName('url_title')->item(0)->nodeValue;
						$tmp_array['product_pic'] = $element->getElementsByTagName('product_pic')->item(0)->nodeValue;
						$tmp_array['link'] = $element->getElementsByTagName('link')->item(0)->nodeValue;
						$array[] = $tmp_array;
					}			
				}
			}
		}catch(Exception $exp){ return NULL; }
		return $array;
	}
	
	public static function login($email,$password,&$error){
		$params = Array();
		if(strlen($email)<4){
			$error = 'The email address you entered is too short'; 
			return NULL;
		}
		
		if(strlen($password)<4){
			$error = 'The password you entered is too short'; 
			return NULL;
		}
		
		$params['email'] = $email;
		$params['password'] = $password;
		
		$response = publicFunctions::PostRequest(self::$API_HOME_URL.'/login',$params,self::$HOME_PORT,site_url());
		if(is_null($response[1]) || strlen($response[1])==0){
			$error = 'Unknown error, please try again later'; 
			return NULL;
		}
		$array = Array();
		try{		
			$dom_object = new DOMDocument();
			$dom_object->loadxml($response[1]);
			$status = $dom_object->getElementsByTagName("status");
			if(!is_null($status) && !is_null($status->item(0)) && $status->item(0)->getAttribute('code')=='ok' ){
				$user_node = $dom_object->getElementsByTagName('user')->item(0);
				$array['id'] = $user_node->getElementsByTagName('id')->item(0)->nodeValue;
				$array['first_name'] = $user_node->getElementsByTagName('first_name')->item(0)->nodeValue;
				$array['last_name'] = $user_node->getElementsByTagName('last_name')->item(0)->nodeValue;
				$array['email'] = $user_node->getElementsByTagName('email')->item(0)->nodeValue;
				$array['user_name'] = $user_node->getElementsByTagName('user_name')->item(0)->nodeValue;
				$array['total_balance'] = $user_node->getElementsByTagName('total_balance')->item(0)->nodeValue;
				$array['profile_picture'] = $user_node->getElementsByTagName('profile_picture')->item(0)->nodeValue;
				$array['tips_count'] = $user_node->getElementsByTagName('tips_count')->item(0)->nodeValue;
				
			}else{
				$error = $dom_object->getElementsByTagName('description')->item(0)->nodeValue;
			}
		}catch(Exception $exp){
			$error = 'Unknown error, please try again later'; 
			return NULL; 
		}
		return $array;
	}
}

?>