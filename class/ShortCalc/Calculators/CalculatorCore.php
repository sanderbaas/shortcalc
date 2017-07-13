<?php
namespace ShortCalc\Calculators;
use \ShortCalc\CalculatorInterface;
use \ShortCalc\IoC;

class CalculatorCore implements CalculatorInterface {
	public $name;
	public $parameters;
	public $formula;
	public $formulaParser;

	public function __construct(String $name) {
		$this->name = $name;
	}

	public static function wpInit() {}

	public static function find(String $name) {
		return false;
	}

	public function renderForm(String $view = null) {
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

	public function renderResult() {
		$this->formulaParser->setFormula($this->formula);
		foreach ($this->parameters as $key => $param) {
			$value = $_POST['parameters'][$param->attributes->name];
			$this->formulaParser->setParameter($param->attributes->name,$value);
		}
		echo $this->formulaParser->getResult();
		exit;
	}
}