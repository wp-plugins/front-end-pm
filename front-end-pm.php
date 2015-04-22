<?php
/*
Plugin Name: Front End PM
Plugin URI: https://shamimbiplob.wordpress.com/contact-us/
Description: Front End PM is a Private Messaging system and a secure contact form to your WordPress site.This is full functioning messaging system fromfront end. The messaging is done entirely through the front-end of your site rather than the Dashboard. This is very helpful if you want to keep your users out of the Dashboard area.
Version: 3.1
dbVersion: 3.1
metaVersion: 3.1
Author: Shamim
Author URI: https://shamimbiplob.wordpress.com/contact-us/
Text Domain: fep
License: GPLv2 or later
*/
//DEFINE
global $wpdb;
define('FEP_PLUGIN_DIR',plugin_dir_path( __FILE__ ).'/');
define('FEP_PLUGIN_URL',plugins_url().'/front-end-pm/');
define('FEP_MESSAGES_TABLE',$wpdb->prefix.'fep_messages');
define('FEP_META_TABLE',$wpdb->prefix.'fep_meta');
require_once('essential-functions.php');

	//ACTIVATE PLUGIN
	register_activation_hook(__FILE__ , 'fep_plugin_activate');


	//ADD ACTIONS
	add_action('after_setup_theme', 'fep_include_require_files');
	add_action('plugins_loaded', 'fep_translation');
	add_action('wp_enqueue_scripts', 'fep_enqueue_scripts');


?>