<?php
namespace ShortCalc\Calculators;
use \ShortCalc\CalculatorInterface;

class WPPostCalculator implements CalculatorInterface {
	public $name = null;
	public $settings = null;
	public $formula = null;
	public $formatter = null;

	public function __construct(String $name) {
		
	}

	public static function find(String $name) {
		// wordpress select query
		error_log('wppost find: ' . $name);
	}

	public function renderForm(String $view = null) {
		return $this->name;
	}

	public function renderResult(String $view = null) {
		return $this->name;
	}
}