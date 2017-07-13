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

	public function findCalculator($name) {
		$plugin = self::$pluginInstance;
		$implementations = $plugin->implementations;
		foreach ($implementations['calculators'] as $cls) {
			$foundCalculator = call_user_func($cls . '::find', $name);
			if ($foundCalculator) { return $foundCalculator; }
		}
		return false;
	}

	public function newCalculator($name, $className) {
		return new $className($name);
	}

	public function newFormulaParser($className) {
		return new $className;
	}
}