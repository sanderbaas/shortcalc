<?php
namespace ShortCalc\Calculators;
use \ShortCalc\CalculatorInterface;
use \FormulaInterpreter;

class JsonCalculator implements CalculatorInterface {
	public $name = null;
	public $settings = null;
	public $formula = null;
	public $formatter = null;

	public function __construct(String $name) {
		
	}

	public static function find(String $name) {
		error_log('json find: ' . $name);
		$compiler = new FormulaInterpreter\Compiler();
		$executable = $compiler->compile('sqrt(9)+sqrt(16)');
		$result = $executable->run(array('a' => 2,'b' => 4));
		error_log($result);
	}

	public function renderForm(String $view = null) {
		return $this->name;
	}

	public function renderResult(String $view = null) {
		return $this->name;
	}
}