<?php
namespace ShortCalc;

class IoC {
	private static $pluginInstance;

	public static function getPluginInstance($slug) {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$pluginInstance ) {
			self::$pluginInstance = new Plugin($slug);
		}
		return self::$pluginInstance;
	}
}