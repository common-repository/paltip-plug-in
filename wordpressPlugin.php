<?php
/*
 Plugin Name: PalTip plugin
 Plugin URI: http://wordpress.org/extend/plugins/paltip-plug-in/
 Description: Our plug-in locates the links that you have included in your post.  It then automatically replaces the original link with a new PalTip link. The PalTip is an affiliate link, which is the easiest and fastest way to convert your links to affiliate links, earning you money. For more options please go to <a href="admin.php?page=PalTip_options">PalTip under Settings </a> after you activate the plugin
 Version: 1.1.3
 Author: Amir Lotan
 */

/*
 This plug-in sends a link to third party server, and can get a different link instead of the original one
 Copyright (C) 2012 amir lotan

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

/* loads and checks if all set for current version */
function paltipPluginInstalled() {
	require_once plugin_dir_path(__FILE__).'LinkChanger.php';
	require_once plugin_dir_path(__FILE__).'publicFunctions.php';
	require_once plugin_dir_path(__FILE__).'PalTipCache.php';
	$current_plugin_version = publicFunctions::getPTPluginVersion();
	if ( $current_plugin_version != publicFunctions::$PLUGIN_VERSION ) {
		publicFunctions::updateOptions();
		PalTipCache::createCache();
		publicFunctions::setPTPluginVersion( publicFunctions::$PLUGIN_VERSION);
	}
}
add_action('plugins_loaded', 'paltipPluginInstalled');
add_action('init', 'paltipPluginInstalled');

/* Runs when plugin is activated */
function paltip_plugin_activate() {
	if ( function_exists( 'money_maker_installed' ) ){
		trigger_error("You already have Money Maker plugin installed. this plugin is doing the same, so there is no need for installing both of them.", E_USER_ERROR);
		wp_die("You already have Money Maker plugin installed. this plugin is doing the same, so there is no need for installing both of them.",'already installed');
	}else if( function_exists( 'easy_affiliate_installed' ) ) {
		trigger_error("You already have Easy Affiliate plugin installed. this plugin is doing the same, so there is no need for installing both of them.", E_USER_ERROR);
		wp_die("You already have Easy Affiliate plugin installed. this plugin is doing the same, so there is no need for installing both of them.",'already installed');
	}
	require_once plugin_dir_path(__FILE__).'LinkChanger.php';
	require_once plugin_dir_path(__FILE__).'publicFunctions.php';
	require_once plugin_dir_path(__FILE__).'PalTipCache.php';
	
	/* Creates new database field */
	publicFunctions::createOptions();
	PalTipCache::createCache();
	if(publicFunctions::getPluginActivated()!=1){
		publicFunctions::pluginActivated();
	}
}
register_activation_hook(__FILE__,'paltip_plugin_activate');

/* Runs on plugin deactivation*/
register_deactivation_hook( __FILE__, 'paltip_plugin_remove' );
function paltip_plugin_remove() {
	/* chaning back to original links */
	$link_changer = new LinkChanger(publicFunctions::getPTID(),publicFunctions::getPTEmail(),publicFunctions::getPTUserName());
	$link_changer->uninstallLinksInAllPosts();
	publicFunctions::deactivateUser();
	/* Deletes the database field */
	publicFunctions::deleteOptions();
	/* delete the cache DB */
	PalTipCache::destroyCache();
}

function paltip_plugin_admin_menu() {
	add_options_page(publicFunctions::$PLUGIN_NAME.' - settings', publicFunctions::$PLUGIN_NAME, 'manage_options','PalTip_options', 'paltip_plugin_settings_html_page');
}

/**
 * Add Settings link to plugins - code from GD Star Ratings
 */
function add_paltip_plugin_settings_link($links, $file) {
	static $this_plugin;
	if (!$this_plugin) $this_plugin = plugin_basename(__FILE__);

	if ($file == $this_plugin){
		$settings_link = '<a href="admin.php?page=PalTip_options">'.__("Settings", publicFunctions::$PLUGIN_NAME ).'</a>';
		array_unshift($links, $settings_link);
	}
	return $links;
}

if ( is_admin() ){
	/* Call the html code */
	add_action('admin_menu', 'paltip_plugin_admin_menu');
	add_filter('plugin_action_links', 'add_paltip_plugin_settings_link', 10, 2 );
}

function paltip_plugin_settings_html_page(){
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	require_once plugin_dir_path(__FILE__).'admin.php';
}

function platipPluginUpdateLinks($content){
	$link_changer = new LinkChanger( publicFunctions::getPTID(), publicFunctions::getPTEmail() , publicFunctions::getPTUserName());
	return $link_changer->changeLinksInContent($content);
}
add_filter('content_save_pre','platipPluginUpdateLinks');

function platip_plugin_custom_dashboard_help() {
	$tips_count = LinkChanger::getNumberOfTipsAndPosts();
	echo '<tr><td class="first b"><a href="admin.php?page=PalTip_options">'.$tips_count['number_of_paltip_links'].'</a></td><td class="first t"><a href="admin.php?page=PalTip_options">Tips</a></td></tr>';
}
add_action('right_now_content_table_end', 'platip_plugin_custom_dashboard_help');
?>