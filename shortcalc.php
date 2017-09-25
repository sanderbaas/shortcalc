<?php
/**
 * @package ShortCalc
 */
/*
Plugin Name: ShortCalc
Plugin URI: https://www.implode.nl/shortcalc
Description: This plugin makes shortcodes available with which calculator forms can be shown on pages or widgets.
Author: Sander Baas
Version: 0.3.5
Author URI: https://www.implode.nl/
Text Domain: shortcalc
Domain Path: /languages
*/

namespace ShortCalc;

require 'vendor/autoload.php';
require 'vendor/webdevstudios/cmb2/init.php';

if ( ! defined( 'WPINC' ) ) {
	die;
}

register_activation_hook(__FILE__, array('ShortCalc\Plugin', 'install' ) );
register_deactivation_hook(__FILE__, array('ShortCalc\Plugin', 'uninstall' ) );

add_action( 'plugins_loaded', array( 'ShortCalc\IoC', 'getPluginInstance' ) );
?>
