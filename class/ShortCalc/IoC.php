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

	public function getCalculator($name) {
		// find Schema, Formula and Formatter by name
		// search in different types of schema or do we
		// have a container for that as well?
		// i.e. YamlSchema or WPPostSchema
		// ExcelFormula JavascriptFormula MathFormula
		// who knows all these different objects?
		//
		/**
		 * Calculators contain all information about the calculator.
		 * So the settings, the formula and the formatter are defined
		 * in the calculator. A calculator can be of different types:
		 * a YamlCalculator, a WPPostCalculator, etc. These can be extended
		 * without need for modification of most functionality. Only a class
		 * file should be made and this can be placed within a theme of a WordPress
		 * site. It should extend CalculatorInterface.
		 **/
		// find out which implementations of CalculatorInterface are defined
		// and then do a find on each static classes for $name
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