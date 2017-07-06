<?php
namespace ShortCalc\Calculators;
use \ShortCalc\CalculatorInterface;
use \ShortCalc\IoC;

class JsonCalculator implements CalculatorInterface {
	public $name;
	public $parameters;
	public $formula;
	public $formulaParser;

	public function __construct(String $name) {
		$this->name = $name;
	}

	public static function find(String $name) {
		$contents = file_get_contents("/var/www/shortcalc/wp-content/plugins/shortcalc/definitions/json/pythagoras.json");
		$contents = utf8_encode($contents);
		$json = json_decode($contents);

		$calculator = IoC::newCalculator($name, __CLASS__);
		$calculator->formula = $json->formula;
		$calculator->formulaParser = IoC::newFormulaParser($json->formulaParser);
		$calculator->parameters = $json->parameters;
		foreach ($calculator->parameters as $key => $param) {
			if (empty($param->attributes)) { $param->attributes = new stdClass(); }
			if (empty($param->attributes->id)) { $param->attributes->id = $key;}
			if (empty($param->attributes->name)) { $param->attributes->name = $key;}
			if (empty($param->attributes->element)) { $param->attributes->element = 'input';}
			if ($param->attributes->element == 'input' && empty($param->attributes->type)) {
				$param->attributes->type = 'text';
			}
			$param->allAttributes = "";
			foreach ($param->attributes as $name => $value) {
				$param->allAttributes .= "$name=\"$value\" ";
			}
			if (empty($param->label) && $param->attributes->type !== 'submit'
				&& $param->attributes->element !== 'button') {
				$param->label = $key;
			}
		}
		return $calculator;
	}

	public function renderForm(String $view = null) {
		// determine template, todo: make trait of it
		$template = __DIR__ . '/../../../views/content-calculator-form.php';
		$override = locate_template(array(
			'shortcalc/content-calculator-form.php',
			'shortcalc/content-calculator-form-'.$this->name.'.php',
			'content-calculator-form.php',
			'content-calculator-form-'.$this->name.'.php',
		));
		$template = $override ? $override : $template;
		set_query_var('name', $this->name);
		set_query_var('parameters', $this->parameters);
		ob_start();
		load_template($template, false);
		return ob_get_clean();
	}

	public function renderResult(String $view = null) {
		$this->formulaParser->setFormula($this->formula);
		foreach ($this->parameters as $key => $param) {
			$value = $_POST[$param->attributes->name];
			$this->formulaParser->setParameter($key,$value);
		}
		return $this->formulaParser->getResult();
	}
}