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
		$calculator->parameters = $json->parameters;

		foreach ($calculator->parameters as $key => $param) {
			if (empty($param->attributes)) { $param->attributes = new \stdClass(); }
			if (empty($param->attributes->id)) { $param->attributes->id = $key;}
			if (empty($param->attributes->name)) { $param->attributes->name = $key;}
			if (empty($param->attributes->value)) { $param->attributes->value = '';}
			if (empty($param->element)) { $param->element = 'input';}
			if (empty($param->label)) { $param->label = '';}
			if (empty($param->prefix)) { $param->prefix = '';}
			if (empty($param->postfix)) { $param->postfix = '';}
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