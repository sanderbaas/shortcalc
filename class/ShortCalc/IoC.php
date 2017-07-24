<?php
namespace ShortCalc;

/**
 * This class implements inversion of control for this plugin. Other
 * classes can call functions of this class to create instances of
 * other classes, without having to worry about dependencies.
 **/
class IoC {
	/** @var \ShortCalc\Plugin $pluginInstance This is the single plugin instance. */
	private static $pluginInstance;

	/**
	 * Retrieve or create and the retrieve the plugin singleton.
	 *
	 * @return \ShortCalc\Plugin The plugin singleton
	 **/
	public static function getPluginInstance() {
		$slug = 'shortcalc';

		if ( null == self::$pluginInstance ) {
			self::$pluginInstance = new Plugin($slug);
		}
		return self::$pluginInstance;
	}

	/**
	 * Search a registered calculator by name.
	 *
	 * @param string $name Name of the calculator to find.
	 *
	 * @return \ShortCalc\JsonCalculator|\ShortCalc\WPPostCalculator|mixed|boolean
	 * Returns the found calculator or false when nothing was found.
	 **/
	public static function findCalculator($name) {
		$plugin = self::$pluginInstance;
		$implementations = $plugin->implementations;
		foreach ($implementations['calculators'] as $cls) {
			$foundCalculator = call_user_func($cls . '::find', $name);
			if ($foundCalculator) { return $foundCalculator; }
		}
		return false;
	}

	/**
	 * Instantiates a calculator with a specific name and of a specific class.
	 *
	 * @return mixed Returns a new calculator of the defined class.
	 **/
	public static function newCalculator($name, $className) {
		return new $className($name);
	}

	/**
	 * Instantiates a formula parser of a specific class.
	 *
	 * @return mixed Returns a new formula parser of the defined class.
	 **/
	public static function newFormulaParser($className) {
		return new $className;
	}
}