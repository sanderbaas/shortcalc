<?php
namespace ShortCalc;

class IoC {
	private static $pluginInstance;

	public static function getPluginInstance() {
		$slug = 'shortcalc';

		if ( null == self::$pluginInstance ) {
			self::$pluginInstance = new Plugin($slug);
		}
		return self::$pluginInstance;
	}
}