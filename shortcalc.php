<?php
/**
 * @package ShortCalc
 * @version 1.0
 */
/*
Plugin Name: ShortCalc
Plugin URI: https://www.implode.nl/shortcalc
Description: This plugin makes shortcodes available with which calculator forms can be shown on pages or widgets.
Author: Sander Baas
Version: 1.0
Author URI: https://www.implode.nl/
*/

namespace ShortCalc;

require 'vendor/autoload.php';

spl_autoload_register(function ($class_name) {
	$filename = __DIR__ . '/class/' . str_replace('\\', '/', $class_name) . '.php';
	$filename = apply_filters('shortcalc_autoload_register', $filename, $class_name);
	include($filename);
});

if ( ! defined( 'WPINC' ) ) {
	die;
}

register_activation_hook(__FILE__, array('ShortCalc\Plugin', 'install' ) );
register_deactivation_hook(__FILE__, array('ShortCalc\Plugin', 'uninstall' ) );

add_action( 'plugins_loaded', array( 'ShortCalc\IoC', 'getPluginInstance' ) );
?>