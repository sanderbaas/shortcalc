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

spl_autoload_register(function ($class_name) {
	include __DIR__ . '/class/' . str_replace('\\', '/', $class_name) . '.php';
});

if ( ! defined( 'WPINC' ) ) {
	die;
}

$plugin = IoC::getPluginInstance('shortcalc');
?>