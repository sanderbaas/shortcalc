<?php
namespace ShortCalc\Calculators;
use \ShortCalc\CalculatorInterface;
use \ShortCalc\IoC;

class JsonCalculator extends CalculatorCore implements CalculatorInterface {
	public static function find(String $name) {
		$contents = file_get_contents("/var/www/shortcalc/wp-content/plugins/shortcalc/definitions/json/pythagoras.json");
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
			if (empty($param->element)) { $param->element = 'input';}
			if ($param->element == 'input' && empty($param->attributes->type)) {
				$param->attributes->type = 'text';
			}
			$param->allAttributes = "";
			foreach ($param->attributes as $name => $value) {
				$param->allAttributes .= "$name=\"$value\" ";
			}
			if (empty($param->label) && $param->attributes->type !== 'submit'
				&& $param->element !== 'button') {
				$param->label = $key;
			}
		}

		return $calculator;
	}
}