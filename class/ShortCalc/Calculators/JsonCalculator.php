<?php
namespace ShortCalc\Calculators;
use \ShortCalc\CalculatorInterface;
use \ShortCalc\IoC;

class JsonCalculator extends CalculatorCore implements CalculatorInterface {
	public static function find(String $name) {
		$file = __DIR__ . '/../../../definitions/json/' . sanitize_file_name($name) .'.json';
		$override = locate_template(array(
			'shortcalc/calculator-'.sanitize_file_name($name).'.json',
			'calculator-'.sanitize_file_name($name).'.json',
		));
		$file = $override ? $override : $file;
		if (!is_file($file)) { return false; }
		$contents = file_get_contents($file);
		$contents = utf8_encode($contents);
		$json = json_decode($contents);

		$calculator = IoC::newCalculator($name, __CLASS__);
		$calculator->formula = $json->formula;
		$calculator->resultPrefix = !empty($json->resultPrefix) ? $json->resultPrefix : '';
		$calculator->resultPostfix = !empty($json->resultPostfix) ? $json->resultPostfix : '';
		$calculator->formulaParser = IoC::newFormulaParser($json->formulaParser);
		$calculator->assignParameters($json->parameters);

		return $calculator;
	}
}