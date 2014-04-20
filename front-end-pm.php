<?php
/*
Plugin Name: Front End PM
Plugin URI: http://www.banglardokan.com/blog/recent/project/front-end-pm-2215/
Description: Front End PM is a Private Messaging system to your WordPress site.This is full functioning messaging system. The messaging is done entirely through the front-end of your site rather than the Dashboard. This is very helpful if you want to keep your users out of the Dashboard area.
Version: 1.1
Author: Shamim
Author URI: http://www.banglardokan.com/blog/recent/project/front-end-pm-2215/
Text Domain: fep
License: GPLv2
*/

//INCLUDE THE CLASS FILE
include_once("fep-class.php");

//DECLARE AN INSTANCE OF THE CLASS
if(class_exists("clFEPm"))
	$FrontEndPM = new clFEPm();

//HOOKS
if (isset($FrontEndPM))
{
	//ACTIVATE PLUGIN
	register_activation_hook(__FILE__ , array(&$FrontEndPM, "fepActivate"));

	//ADD SHORTCODES
	add_shortcode('front-end-pm', array(&$FrontEndPM, "displayAll"));

	//ADD ACTIONS
	add_action('init', array(&$FrontEndPM, "session"));
	add_action('plugins_loaded', array(&$FrontEndPM, "translation"));
	add_action('init', array(&$FrontEndPM, "jsInit"));
	add_action('wp_enqueue_scripts', array(&$FrontEndPM, "fep_enqueue_scripts"));
	add_action('admin_menu', array(&$FrontEndPM, "addAdminPage"));
	

	//ADD WIDGET
	wp_register_sidebar_widget("fep-button-widget",__("FEP button widget", "fep"), array(&$FrontEndPM, "widget"));
	wp_register_sidebar_widget("fep-text-widget",__("FEP text widget", "fep"), array(&$FrontEndPM, "widget_text"));
}

?>