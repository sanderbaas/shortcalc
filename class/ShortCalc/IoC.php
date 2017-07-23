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

	public static function findCalculator($name) {
		$plugin = self::$pluginInstance;
		$implementations = $plugin->implementations;
		foreach ($implementations['calculators'] as $cls) {
			$foundCalculator = call_user_func($cls . '::find', $name);
			if ($foundCalculator) { return $foundCalculator; }
		}
		return false;
	}

	public static function newCalculator($name, $className) {
		return new $className($name);
	}

	public static function newFormulaParser($className) {
		return new $className;
	}
}