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
		$contents = file_get_contents($file);
		$contents = utf8_encode($contents);
		$json = json_decode($contents);

		$calculator = IoC::newCalculator($name, __CLASS__);
		$calculator->formula = $json->formula;
		$calculator->formulaParser = IoC::newFormulaParser($json->formulaParser);
		$calculator->parameters = $json->parameters;

		foreach ($calculator->parameters as $key => $param) {
			if (empty($param->attributes)) { $param->attributes = new \stdClass(); }
			if (empty($param->attributes->id)) { $param->attributes->id = $key;}
			if (empty($param->attributes->name)) { $param->attributes->name = $key;}
			if (empty($param->attributes->value)) { $param->attributes->value = '';}
			if (empty($param->element)) { $param->element = 'input';}
			if ($param->element == 'input' && empty($param->attributes->type)) {
				$param->attributes->type = 'text';
			}
			if (empty($param->label) && $param->attributes->type !== 'submit'
				&& $param->element !== 'button') {
				$param->label = $key;
			}
		}

		return $calculator;
	}
}