<?php
/**
 * @package ShortCalc
 */
/*
Plugin Name: ShortCalc
Plugin URI: https://www.implode.nl/shortcalc
Description: This plugin makes shortcodes available with which calculator forms can be shown on pages or widgets.
Author: Sander Baas
Version: 0.3.1
Author URI: https://www.implode.nl/
Text Domain: shortcalc
Domain Path: /languages
*/

namespace ShortCalc;

require 'vendor/autoload.php';

spl_autoload_register(function ($class_name) {
	if (preg_match('/^CMB2_Type_/', $class_name) == 1) {
		$filename = __DIR__ . '/vendor/webdevstudios/cmb2/includes/types/' . $class_name . '.php';
		include($filename);
		return;
	}
	if (preg_match('/^CMB2/', $class_name) == 1) {
		$filename = __DIR__ . '/vendor/webdevstudios/cmb2/includes/' . $class_name . '.php';
		include($filename);
		return;
	}
	$filename = __DIR__ . '/class/' . str_replace('\\', '/', $class_name) . '.php';
	$filename = apply_filters('shortcalc_autoload_register', $filename, $class_name);
	include($filename);
});

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( file_exists( dirname( __FILE__ ) . '/vendor/webdevstudios/cmb2/init.php' ) ) {
	require_once dirname( __FILE__ ) . '/vendor/webdevstudios/cmb2/init.php';
}

register_activation_hook(__FILE__, array('ShortCalc\Plugin', 'install' ) );
register_deactivation_hook(__FILE__, array('ShortCalc\Plugin', 'uninstall' ) );

add_action( 'plugins_loaded', array( 'ShortCalc\IoC', 'getPluginInstance' ) );
?>
